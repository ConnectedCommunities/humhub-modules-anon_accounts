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

class AdminController extends Controller
{
     public $subLayout = "application.modules_core.admin.views._layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'expression' => 'Yii::app()->user->isAdmin()',
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Configuration Action for Super Admins
     */
    public function actionIndex() {

        $form = new AnonAccountsForm;

        if (isset($_POST['AnonAccountsForm'])) {

            $form->attributes = $_POST['AnonAccountsForm'];

            if ($form->validate()) {

                $form->anonAccountsFirstNameOptions = HSetting::SetText('anonAccountsFirstNameOptions', $form->anonAccountsFirstNameOptions);
                $form->anonAccountsLastNameOptions = HSetting::SetText('anonAccountsLastNameOptions', $form->anonAccountsLastNameOptions);

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));

                $this->redirect(Yii::app()->createUrl('//anon_accounts/admin/index'));
            }

        } else {
            $form->anonAccountsFirstNameOptions = HSetting::GetText('anonAccountsFirstNameOptions');
            $form->anonAccountsLastNameOptions = HSetting::GetText('anonAccountsLastNameOptions');
        }

        $this->render('index', array(
            'model' => $form
        ));

    }
     

    /** 
     * Prototyping the random name generator
     */
    public function actionRand() {
        
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__) . '/../resources', true, 0, defined('YII_DEBUG'));
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/md5.min.js');
        Yii::app()->clientScript->registerScriptFile($assetPrefix . '/jdenticon-1.3.0.min.js');

        $firstNameOptions = explode("\n", HSetting::GetText('anonAccountsFirstNameOptions'));
        $randomFirstName = ucfirst($firstNameOptions[array_rand($firstNameOptions)]);
        
        $lastNameOptions = explode("\n", HSetting::GetText('anonAccountsLastNameOptions'));
        $randomLastName = ucfirst($lastNameOptions[array_rand($lastNameOptions)]);

        ////// Save DataURL as Image ///////
        // @TODO: Pull this from $_POST
        // $data = "data:image/png;base64,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";
        // $filePath = dirname(__FILE__) . '/../resources/test.png';
        // $fp = fopen($filePath,"w");
        // fwrite($fp, file_get_contents($data));
        // fclose($fp);
        ////////////////////////////////////

        $model = new AnonAccountRegisterForm();

        if(isset($_POST['AnonAccountRegisterForm'])) {

            // Pre-set the random first and last name
            $model->firstName = trim($randomFirstName);
            $model->lastName = trim($randomLastName);

            // Load attributes into the model
            $model->attributes = $_POST['AnonAccountRegisterForm'];

            // Make the username from the first and lastnames
            $model->username = strtolower($model->firstName . "_" . $model->lastName);

            // Validate
            if($model->validate()) {

                // Create temporary file
                $temp_file_name = tempnam(sys_get_temp_dir(), 'img') . '.png';
                $fp = fopen($temp_file_name,"w");
                fwrite($fp, file_get_contents($model->image));
                fclose($fp);

                // Store profile image for user
                $profileImage = new ProfileImage($model->guid);
                $profileImage->setNew($temp_file_name);

                // Remove temporary file 
                unlink($temp_file_name);

                // Finished. Redirect away!
                $this->redirect($this->createUrl('//anon_accounts/admin/rand', array()));

            } else {
                echo "Error processing account register form";
            }
        }

    
        $this->render('test', array(
            'firstName' => $randomFirstName,
            'lastName' => $randomLastName,
            'model' => $model
        ));

    }
}