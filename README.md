# Anonymous Accounts
This module adds support for Anonymous Accounts to HumHub.

## Installation

- Clone the anon_accounts module into your modules directory
```
cd protected/modules
git clone https://github.com/ConnectedCommunities/humhub-modules-anon_accounts.git anon_accounts
```

- Go to Admin > Modules. You should now see the `Anonymous Accounts` module in your list of installed modules

-  Click "Enable". This will install the module for you


## Usage
To enable registration of Anonymous Accounts, you will need to redirect to the new registration page by overwriting HumHub's default registration page.

Within your theme, create `themes/your-theme/views/user/auth/createAccount.php` with the contents:
```
<?php
use yii\helpers\Url;

if(Yii::$app->hasModule('anon_accounts')) {
    Yii::$app->getResponse()->redirect(Url::toRoute(['//anon_accounts/main/index', 'token' => $_GET['token']]));
} else {
    $controller = Yii::$app->controller;
    echo $controller->render('user/auth/createAccount', array(
            'hForm' => $hForm,
            'needAproval' => false)
    );
}
?>
```

Then create `themes/your-theme/views/user/auth/createAccount_original.php` and copy and paste the contents of `protected/humhub/modules/user/views/auth/createAccount.php`