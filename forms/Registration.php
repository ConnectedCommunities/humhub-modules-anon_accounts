<?php

namespace humhub\modules\anon_accounts\forms;

use Yii;
use yii\helpers\ArrayHelper;
use humhub\compat\HForm;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\GroupUser;

class Registration extends \humhub\modules\user\models\forms\Registration
{

    /**
     * Override
     * Builds HForm Definition to automatically build form output
     */
    protected function setFormDefinition()
    {
        parent::setFormDefinition();

        $this->definition['elements']['IdenticonForm'] = array(
            'type' => 'form',
            'elements' => array(
                'image' => array(
                    'type' => 'hidden',
                    'class' => 'form-control',
                    'id' => 'identiconform-image'
                ),
            ),
        );
    }

}