<?php

namespace Icinga\Editor;

/**
 * Změna hesla uživatele
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright Vitex Software © 2011
 * @package LinkQuick
 * @subpackage WEBUI
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged(); //Pouze pro přihlášené
$formOK = true;

if (!isset($_POST['password']) || !strlen($_POST['password'])) {
    $oUser->addStatusMessage('Prosím zadejte nové heslo');
    $formOK = false;
} else {
    if ($_POST['password'] == $oUser->GetUserLogin()) {
        $oUser->addStatusMessage('Heslo se nesmí shodovat s přihlašovacím jménem',
            'waring');
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
    $oUser->addStatusMessage('Prosím zadejte potvrzení hesla');
    $formOK = false;
}
if ((isset($_POST['passwordConfirm']) && isset($_POST['password'])) && ($_POST['passwordConfirm']
    != $_POST['password'])) {
    $oUser->addStatusMessage('Zadaná hesla se neshodují', 'waring');
    $formOK = false;
}

if (!isset($_POST['CurrentPassword'])) {
    $oUser->addStatusMessage('Prosím zadejte stávající heslo');
    $formOK = false;
} else {
    if (!$oUser->PasswordValidation($_POST['CurrentPassword'],
            $oUser->GetDataValue($oUser->passwordColumn))) {
        $oUser->AddStatusMessage('Stávající heslo je neplatné', 'warning');
        $formOK = false;
    }
}

$oPage->addItem(new UI\PageTop(_('Změna hesla uživatele')));
$oPage->addPageColumns();

if ($formOK && $oPage->isPosted()) {
    $plainPass = $oPage->getRequestValue('password');

    if ($oUser->passwordChange($plainPass)) {

        $oUser->addStatusMessage(_('Heslo bylo změněno'), 'success');

        $email = $oPage->addItem(new EaseMail($oUser->getDataValue($oUser->mailColumn),
            _('Změněné heslo pro Monitoring')));
        $email->addItem(_('Vážený zákazníku vaše přihlašovací údaje byly změněny').":\n");

        $email->addItem(' Login: '.$oUser->getUserLogin()."\n");
        $email->addItem(' Heslo: '.$plainPass."\n");

        $email->send();
    }
} else {
    $loginForm = new \Ease\Html\Form(NULL);

    $loginForm->addItem(new \Ease\TWB\FormGroup(_('Stávající heslo'),
        new \Ease\Html\InputPasswordTag('CurrentPassword'), NULL
    ));

    $loginForm->addItem(new \Ease\TWB\FormGroup(_('Nové heslo'),
        new \Ease\Html\InputPasswordTag('password'), NULL
    ));

    $loginForm->addItem(new \Ease\TWB\FormGroup(_('potvrzení hesla'),
        new \Ease\Html\InputPasswordTag('passwordConfirm'), NULL
    ));

    $loginForm->addItem(new \Ease\TWB\SubmitButton(_('Změnit heslo')));

    $loginForm->fillUp($_POST);

    $oPage->columnII->addItem(new \Ease\TWB\Panel(_('změna hesla'), 'default',
        $loginForm));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
