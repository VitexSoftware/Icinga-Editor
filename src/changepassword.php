<?php

namespace Icinga\Editor;

/**
 * User passwords change
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright Vitex Software © 2011-2018
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged(); //Pouze pro přihlášené
$formOK = true;

if (!isset($_POST['password']) || !strlen($_POST['password'])) {
    $oUser->addStatusMessage('Please enter new password');
    $formOK = false;
} else {
    if ($_POST['password'] == $oUser->GetUserLogin()) {
        $oUser->addStatusMessage('Password cant match with login', 'waring');
        $formOK = false;
    }
    /* TODO:
      if (!$OUser->passwordCrackCheck($_POST['password'])) {
      $OUser->addStatusMessage('Heslo není dostatečně bezpečné');
      $FormOK = false;
      }
     */
}
if (!isset($_POST['passwordConfirm']) || !strlen($_POST['passwordConfirm'])) {
    $oUser->addStatusMessage('Please enter password confirmation');
    $formOK = false;
}
if ((isset($_POST['passwordConfirm']) && isset($_POST['password'])) && ($_POST['passwordConfirm'] != $_POST['password'])) {
    $oUser->addStatusMessage('Password control do not match', 'waring');
    $formOK = false;
}

if (!isset($_POST['CurrentPassword'])) {
    $oUser->addStatusMessage('Please enter current password');
    $formOK = false;
} else {
    if (!$oUser->PasswordValidation($_POST['CurrentPassword'],
                    $oUser->GetDataValue($oUser->passwordColumn))) {
        $oUser->AddStatusMessage('Current password invalid', 'warning');
        $formOK = false;
    }
}

$oPage->addItem(new UI\PageTop(_('User password change')));
$oPage->addPageColumns();

if ($formOK && $oPage->isPosted()) {
    $plainPass = $oPage->getRequestValue('password');

    if ($oUser->passwordChange($plainPass)) {

        $oUser->addStatusMessage(_('Password was changed'), 'success');

        $email = $oPage->addItem(new \Ease\Mailer($oUser->getDataValue($oUser->mailColumn),
                        _('Changed monitoring password')));
        $email->addItem(sprintf(_('Dear user %s, your monitoring password was changed to'),
                        $oUser->getUserLogin()) . ":\n");
        $email->addItem(_('Password') . ': ' . $plainPass . "\n");

        $email->send();
    }
} else {
    $loginForm = new \Ease\Html\Form(NULL);

    $loginForm->addItem(new \Ease\TWB\FormGroup(_('Current password'),
                    new \Ease\Html\InputPasswordTag('CurrentPassword'), NULL
    ));

    $loginForm->addItem(new \Ease\TWB\FormGroup(_('New Password'),
                    new \Ease\Html\InputPasswordTag('password'), NULL
    ));

    $loginForm->addItem(new \Ease\TWB\FormGroup(_('Password confirm'),
                    new \Ease\Html\InputPasswordTag('passwordConfirm'), NULL
    ));

    $loginForm->addItem(new \Ease\TWB\SubmitButton(_('Change password')));

    $loginForm->fillUp($_POST);

    $oPage->columnII->addItem(new \Ease\TWB\Panel(_('Password change'),
                    'default', $loginForm));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
