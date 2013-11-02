<?php

/**
 * Změna hesla uživatele
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright Vitex Software © 2011
 * @package LinkQuick
 * @subpackage WEBUI
 */
require_once 'includes/IEInit.php';
require_once 'Ease/EaseMail.php';
require_once 'Ease/EaseHtmlForm.php';
require_once 'Ease/EaseJQueryWidgets.php';

$OPage->onlyForLogged(); //Pouze pro přihlášené
$FormOK = true;

if (!isset($_POST['password']) || !strlen($_POST['password'])) {
    $OUser->addStatusMessage('Prosím zadejte nové heslo');
    $FormOK = false;
} else {
    if ($_POST['password'] == $OUser->GetUserLogin()) {
        $OUser->addStatusMessage('Heslo se nesmí shodovat s přihlašovacím jménem', 'waring');
        $FormOK = false;
    }
    /* TODO:
      if(!$OUser->passwordCrackCheck($_POST['password'])){
      $OUser->addStatusMessage('Heslo není dostatečně bezpečné');
      $FormOK = false;
      }
     */
}
if (!isset($_POST['passwordConfirm']) || !strlen($_POST['passwordConfirm'])) {
    $OUser->addStatusMessage('Prosím zadejte potvrzení hesla');
    $FormOK = false;
}
if ((isset($_POST['passwordConfirm']) && isset($_POST['password'])) && ($_POST['passwordConfirm'] != $_POST['password'])) {
    $OUser->addStatusMessage('Zadaná hesla se neshodují', 'waring');
    $FormOK = false;
}

if (!isset($_POST['CurrentPassword'])) {
    $OUser->addStatusMessage('Prosím zadejte stávající heslo');
    $FormOK = false;
} else {
    if (!$OUser->PasswordValidation($_POST['CurrentPassword'], $OUser->GetDataValue($OUser->PasswordColumn))) {
        $OUser->AddStatusMessage('Stávající heslo je neplatné', 'warning');
        $FormOK = false;
    }
}


$OPage->addItem(new IEPageTop(_('Změna hesla uživatele')));

if ($FormOK && isset($_POST)) {
    $OUser->setDataValue($OUser->PasswordColumn, $OUser->encryptPassword($_POST['password']));
    if ($OUser->saveToMySQL()) {
        $OUser->addStatusMessage('Heslo bylo změněno', 'success');

        $Email = $OPage->addItem(new EaseMail($OUser->getDataValue($OUser->MailColumn), 'Změněné heslo pro FragCC'));
        $Email->addItem("Vážený zákazníku vaše přihlašovací údaje byly změněny:\n");

        $Email->addItem(' Login: ' . $OUser->getUserLogin() . "\n");
        $Email->addItem(' Heslo: ' . $_POST['password'] . "\n");

        $Email->send();
    }
} else {
    $LoginForm = new EaseHtmlForm(NULL);

    $LoginForm->addItem(new EaseLabeledPasswordInput('CurrentPassword', NULL, _('Stávající heslo')));

    $LoginForm->addItem(new EaseLabeledPasswordStrongInput('password', NULL, _('Nové heslo') . ' *'));
    $LoginForm->addItem(new EaseLabeledPasswordControlInput('passwordConfirm', NULL, _('potvrzení hesla') . ' *', array('id' => 'confirmation')));

    $LoginForm->addItem(new EaseJQuerySubmitButton('Ok' , 'Změnit heslo'));

    $LoginForm->fillUp($_POST);

    $OPage->column2->addItem( new EaseHtmlFieldSet(_('změna hesla'), $LoginForm));
}

$OPage->AddItem(new IEPageBottom());

$OPage->Draw();
?>
