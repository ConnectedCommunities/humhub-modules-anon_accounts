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
use \humhub\models\Setting;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use humhub\modules\anon_accounts\forms\IdenticonForm;
use humhub\libs\ProfileImage;


class MainController extends \humhub\modules\user\controllers\AuthController {

    public function actionIndex(){

        $authClient = null;
        $registration = new \humhub\modules\anon_accounts\forms\Registration();

        // Apply Identicon profile picture
        $identiconForm = new IdenticonForm();
        $registration->models['IdenticonForm'] = $identiconForm;

        $userInvite = Invite::findOne(['token' => Yii::$app->request->get('token')]);
        if (!$userInvite)
            throw new HttpException(404, 'Token not found!');

        if ($userInvite->language)
            Yii::$app->language = $userInvite->language;


        if ($registration->submitted('save') && $registration->validate() && $registration->register($authClient)) {
            Yii::$app->session->remove('authClient');

            // Prepend Data URI scheme (stripped out for safety)
            $identiconForm->image = str_replace("[removed]", "data:image/png;base64,", $identiconForm->image);

            // Upload new Profile Picture for user
            $this->uploadProfilePicture($registration->models['User']->guid, $identiconForm->image);

            // Autologin when user is enabled (no approval required)
            if ($registration->getUser()->status === User::STATUS_ENABLED) {
                Yii::$app->user->switchIdentity($registration->models['User']);
                return $this->redirect(['/dashboard/dashboard']);
            }

            return $this->render('success', [
                'form' => $registration,
                'needApproval' => ($registration->getUser()->status === User::STATUS_NEED_APPROVAL)
            ]);
        }

        ///////////////////////////////////////////////////////
        // Set the users email in the Registration model
        $registration->getUser()->email = $userInvite->email;

        // Generate a random first name
        $firstNameOptions = explode("\n", Setting::GetText('anonAccountsFirstNameOptions'));
        $randomFirstName = trim(ucfirst($firstNameOptions[array_rand($firstNameOptions)]));

        // Generate a random last name
        $lastNameOptions = explode("\n", Setting::GetText('anonAccountsLastNameOptions'));
        $randomLastName = trim(ucfirst($lastNameOptions[array_rand($lastNameOptions)]));

        // Pre-set the random first and last name
        $registration->getProfile()->lastname = $randomLastName;
        $registration->getProfile()->firstname = $randomFirstName;

        // Make the username from the first and lastnames (only first 25 chars)
        $registration->getUser()->username = substr(str_replace(" ", "_", strtolower($registration->getProfile()->firstname . "_" . $registration->getProfile()->lastname)), 0, 25);
        ///////////////////////////////////////////////////////

        return $this->render('createAccount', ['hForm' => $registration]);

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