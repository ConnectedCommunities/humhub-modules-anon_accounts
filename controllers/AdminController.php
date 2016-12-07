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
use yii\helpers\Url;
use humhub\models\Setting;
use humhub\libs\ProfileImage;
use humhub\modules\anon_accounts\forms\AnonAccountsForm;

class AdminController extends \humhub\modules\admin\components\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'adminOnly' => true
            ]
        ];
    }

    /**
     * Configuration Action for Super Admins
     */
    public function actionIndex() {

        $form = new AnonAccountsForm;

        if (isset($_POST['AnonAccountsForm'])) {

            $form->attributes = $_POST['AnonAccountsForm'];

            if ($form->validate()) {

                $form->anonAccountsFirstNameOptions = Setting::SetText('anonAccountsFirstNameOptions', $form->anonAccountsFirstNameOptions);
                $form->anonAccountsLastNameOptions = Setting::SetText('anonAccountsLastNameOptions', $form->anonAccountsLastNameOptions);

                // set flash message
                Yii::$app->getSession()->setFlash('data-saved', 'Saved');

                return $this->redirect(Url::toRoute('index'));
            }

        } else {
            $form->anonAccountsFirstNameOptions = Setting::GetText('anonAccountsFirstNameOptions');
            $form->anonAccountsLastNameOptions = Setting::GetText('anonAccountsLastNameOptions');
        }

        return $this->render('index', array(
            'model' => $form
        ));

    }

}