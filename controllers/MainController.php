<?php

/**
 * Connected Communities Initiative
 * Copyright (C) 2016  Queensland University of Technology
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class MainController extends Controller{

    public $layout = "application.modules_core.user.views.layouts.main_auth";
    public $subLayout = "_layout";

    public function actionIndex(){
		
		$_POST = Yii::app()->input->stripClean($_POST);
        
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/md5.min.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jdenticon-1.3.0.min.js');

        $needApproval = HSetting::Get('needApproval', 'authentication_internal');

        if (!Yii::app()->user->isGuest)
            throw new CHttpException(401, 'Your are already logged in! - Logout first!');

        // Check for valid user invite
        $userInvite = UserInvite::model()->findByAttributes(array('token' => Yii::app()->request->getQuery('token')));
        if (!$userInvite)
            throw new CHttpException(404, 'Token not found!');

        if ($userInvite->language)
            Yii::app()->setLanguage($userInvite->language);


        $userModel = new User('register');
        $userModel->email = $userInvite->email;
        $userPasswordModel = new UserPassword('newPassword');
        $profileModel = $userModel->profile;
        $profileModel->scenario = 'register';


        ///////////////////////////////////////////////////////
		
        // Generate a random first name
        $firstNameOptions = explode("\n", HSetting::GetText('anonAccountsFirstNameOptions'));
        $randomFirstName = trim(ucfirst($firstNameOptions[array_rand($firstNameOptions)]));
		
		// Generate a random last name
        $lastNameOptions = explode("\n", HSetting::GetText('anonAccountsLastNameOptions'));
        $randomLastName = trim(ucfirst($lastNameOptions[array_rand($lastNameOptions)]));
        
        // Pre-set the random first and last name
		$profileModel->lastname = $randomLastName;
		$profileModel->firstname = $randomFirstName;
        
        // Make the username from the first and lastnames (only first 25 chars)
        $userModel->username = substr(str_replace(" ", "_", strtolower($profileModel->firstname . "_" . $profileModel->lastname)), 0, 25);

		///////////////////////////////////////////////////////

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();

        $groupModels = Group::model()->findAll(array('order' => 'name'));

        $defaultUserGroup = HSetting::Get('defaultUserGroup', 'authentication_internal');
        $groupFieldType = "dropdownlist";
        if ($defaultUserGroup != "") {
            $groupFieldType = "hidden";
        } else if (count($groupModels) == 1) {
            $groupFieldType = "hidden";
            $defaultUserGroup = $groupModels[0]->id;
        }

        // Add Identicon Form
        $identiconForm = new IdenticonForm();
        $definition['elements']['IdenticonForm'] = array(
            'type' => 'form', 
            'elements' => array(
                'image' => array(
                    'type' => 'hidden',
                    'class' => 'form-control',
                    'id' => 'image'
                ),
            ),
        );

        // Add Profile Form
        $definition['elements']['Profile'] = array_merge(array('type' => 'form'), $profileModel->getFormDefinition());

        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
            'title' => 'Password',
            'elements' => array(
                'username' => array(
                    'type' => 'hidden',
                    'class' => 'form-control',
                    'maxlength' => 25,
                ),
                'email' => array(
                    'type' => 'hidden',
                    'class' => 'form-control',
                ),
                'group_id' => array(
                    'type' => $groupFieldType,
                    'class' => 'form-control',
                    'items' => CHtml::listData($groupModels, 'id', 'name'),
                    'value' => $defaultUserGroup,
                ),
            ),
        );

        // Add User Password Form
        $definition['elements']['UserPassword'] = array(
            'type' => 'form',
            #'title' => 'Password',
            'elements' => array(
                'newPassword' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
                'newPasswordConfirm' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
            ),
        );

        // Get Form Definition
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'class' => 'btn btn-primary',
                'label' => Yii::t('UserModule.controllers_AuthController', 'Create account'),
            ),
        );

        $form = new HForm($definition);
        $form['User']->model = $userModel;
        $form['UserPassword']->model = $userPasswordModel;
        $form['Profile']->model = $profileModel;
        $form['IdenticonForm']->model = $identiconForm;


        /// ----- WE DONT WANT TO SAVE YET -------
        if ($form->submitted('save') && $form->validate() && $identiconForm->validate()) {

            $this->forcePostRequest();

            // Registe User
            $form['User']->model->email = $userInvite->email;
            $form['User']->model->language = Yii::app()->getLanguage();
            if ($form['User']->model->save()) {

                // Save User Profile
                $form['Profile']->model->user_id = $form['User']->model->id;
                $form['Profile']->model->save();

                // Save User Password
                $form['UserPassword']->model->user_id = $form['User']->model->id;
                $form['UserPassword']->model->setPassword($form['UserPassword']->model->newPassword);
                $form['UserPassword']->model->save();

                
                // Autologin user
                if (!$needApproval) {
                    $user = $form['User']->model;
                    $newIdentity = new UserIdentity($user->username, '');
                    $newIdentity->fakeAuthenticate();
                    Yii::app()->user->login($newIdentity);
                    
                    // Prepend Data URI scheme (stripped out for safety) 
                    $identiconForm->image = str_replace("[removed]", "data:image/png;base64,", $identiconForm->image);

                    // Upload new Profile Picture for user
                    $this->uploadProfilePicture(Yii::app()->user->guid, $identiconForm->image);

                    // Redirect to dashboard
                    $this->redirect(array('//dashboard/dashboard'));
                    return;
                }

                $this->render('createAccount_success', array(
                    'form' => $form,
                    'needApproval' => $needApproval,
                ));

                return;
            }
        }

        $this->render('createAccount', array(
            'form' => $form,
            'identiconForm' => $identiconForm,
            'needAproval' => $needApproval
        ));
    
    }


    /** 
     * Uploads the identicon profile picture
     * @param int guid
     * @param Base64 Image (identicon)
     */
    private function uploadProfilePicture($guid, $data) 
    {

        // Create temporary file
        $temp_file_name = tempnam(sys_get_temp_dir(), 'img') . '.png';
        $fp = fopen($temp_file_name,"w");
        fwrite($fp, file_get_contents($data));
        fclose($fp);

        // Store profile image for user
        $profileImage = new ProfileImage($guid);
        $profileImage->setNew($temp_file_name);

        // Remove temporary file 
        unlink($temp_file_name);

    }

}