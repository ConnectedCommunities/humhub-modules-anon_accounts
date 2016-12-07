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

namespace humhub\modules\anon_accounts\controllers;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use \humhub\models\Setting;
use humhub\components\Controller;
use humhub\modules\user\models\Invite;
use humhub\compat\HForm;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\anon_accounts\forms\IdenticonForm;
use humhub\libs\ProfileImage;


class MainController extends \humhub\modules\user\controllers\AuthController {

    public function actionIndex(){


        $needApproval = Setting::Get('needApproval', 'authentication_internal');

        if (!Yii::$app->user->isGuest)
            throw new HttpException(401, 'Your are already logged in! - Logout first!');


        $userInvite = Invite::findOne(['token' => Yii::$app->request->get('token')]);
        if (!$userInvite)
            throw new HttpException(404, 'Token not found!');

        if ($userInvite->language)
            Yii::$app->language = $userInvite->language;

        $userModel = new User();
        $userModel->scenario = 'registration';
        $userModel->email = $userInvite->email;

        $userPasswordModel = new Password();
        $userPasswordModel->scenario = 'registration';

        $profileModel = $userModel->profile;
        $profileModel->scenario = 'registration';

        ///////////////////////////////////////////////////////
        // Generate a random first name
        $firstNameOptions = explode("\n", Setting::GetText('anonAccountsFirstNameOptions'));
        $randomFirstName = trim(ucfirst($firstNameOptions[array_rand($firstNameOptions)]));

        // Generate a random last name
        $lastNameOptions = explode("\n", Setting::GetText('anonAccountsLastNameOptions'));
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


        $groupModels = \humhub\modules\user\models\Group::find()->orderBy('name ASC')->all();
        $defaultUserGroup = \humhub\models\Setting::Get('defaultUserGroup', 'authentication_internal');
        $groupFieldType = "dropdownlist";
        if ($defaultUserGroup != "") {
            $groupFieldType = "hidden";
        } else if (count($groupModels) == 1) {
            $groupFieldType = "hidden";
            $defaultUserGroup = $groupModels[0]->id;
        }
        if ($groupFieldType == 'hidden') {
            $userModel->group_id = $defaultUserGroup;
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
            'title' => Yii::t('UserModule.controllers_AuthController', 'Account'),
            'elements' => array(
                'username' => array(
                    'type' => 'hidden',
                    'class' => 'form-control',
                    'maxlength' => 25,
                ),
                'group_id' => array(
                    'type' => $groupFieldType,
                    'class' => 'form-control',
                    'items' => \yii\helpers\ArrayHelper::map($groupModels, 'id', 'name'),
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
        $form->models['User'] = $userModel;
        $form->models['UserPassword'] = $userPasswordModel;
        $form->models['Profile'] = $profileModel;
        $form->models['IdenticonForm'] = $identiconForm;

        if ($form->submitted('save') && $form->validate() && $identiconForm->validate()) {

            $this->forcePostRequest();

            // Registe User
            $form->models['User']->email = $userInvite->email;
            $form->models['User']->language = Yii::$app->language;
            if ($form->models['User']->save()) {

                // Save User Profile
                $form->models['Profile']->user_id = $form->models['User']->id;
                $form->models['Profile']->save();

                // Save User Password
                $form->models['UserPassword']->user_id = $form->models['User']->id;
                $form->models['UserPassword']->setPassword($form->models['UserPassword']->newPassword);
                $form->models['UserPassword']->save();

                // Autologin user
                if (!$needApproval) {

                    $user = $form->models['User'];
                    Yii::$app->user->login($user);
                    
                    // Prepend Data URI scheme (stripped out for safety)
                    $identiconForm->image = str_replace("[removed]", "data:image/png;base64,", $identiconForm->image);
                    // Upload new Profile Picture for user
                    $this->uploadProfilePicture(Yii::$app->user->guid, $identiconForm->image);

                    // Redirect to dashboard
                    return $this->redirect(Url::to(['/dashboard/dashboard']));
                }

                return $this->render('createAccount_success', array(
                    'form' => $form,
                    'needApproval' => $needApproval,
                ));
            }
        }

        return $this->render('createAccount', array(
                'hForm' => $form,
                'needAproval' => $needApproval)
        );
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