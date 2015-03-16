<?php

/**
 * Reset hesla
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-14 info@vitexsoftware.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'Ease/EaseMail.php';
require_once 'Ease/EaseHtmlForm.php';
$success = false;

$emailTo = $oPage->getPostValue('Email');

$oPage->includeJavaScript('js/jquery.validate.js');
$oPage->addJavascript('$("#PassworRecovery").validate({
  rules: {
    Email: {
      required: true,
      email: true
    }
  }
});',null,true);

if ($emailTo) {
    $oPage->takemyTable();
    $userEmail = $oPage->easeAddSlashes($emailTo);
    $userFound = $oPage->myDbLink->queryToArray('SELECT id,login FROM user WHERE email=\'' . $userEmail . '\'');
    if (count($userFound)) {
        $userID = intval($userFound[0]['id']);
        $userLogin = $userFound[0]['login'];
        $newPassword = $oPage->randomString(8);

        $passChanger = new EaseUser($userID);
        $passChanger->passwordChange($newPassword);

        $email = $oPage->addItem(new EaseMail($userEmail,'Icinga Editor -'. _('Nové heslo pro').' ' . $_SERVER['SERVER_NAME']));
        $email->addItem(_("Tvoje přihlašovací údaje byly změněny:\n"));

        $email->addItem(' Login: ' . $userLogin . "\n");
        $email->addItem(' Heslo: ' . $newPassword . "\n");

        $email->send();

        $oUser->addStatusMessage('Tvoje nové heslo vám bylo odesláno mailem na zadanou adresu <strong>' . $_REQUEST['Email'] . '</strong>');
        $success = true;
    } else {
        $oUser->addStatusMessage('Promiňnte, ale email <strong>' . $_REQUEST['Email'] . '</strong> nebyl v databázi nalezen', 'warning');
    }
} else {
    $oUser->addStatusMessage(_('Zadejte prosím váš eMail.'));
}

$oPage->addItem(new IEPageTop(_('Obnova zapomenutého hesla')));

if (!$success) {
    $oPage->columnI->addItem('<h1>Zapoměl jsem své heslo!</h1>');

    $oPage->columnIII->addItem(_('Zapoměl jste heslo? Vložte svou e-mailovou adresu, kterou jste zadal při registraci a my Vám pošleme nové.'));

    $EmailForm = $oPage->columnII->addItem(new EaseHtmlForm('PassworRecovery'));
    $EmailForm->addItem(new EaseLabeledTextInput('Email', null,_('Email'), array('size' => '40')));
    $EmailForm->addItem(new EaseJQuerySubmitButton('ok', _('Zaslat nové heslo')));

    if (isset($_POST)) {
        $EmailForm->fillUp($_POST);
    }
} else {
    $oPage->columnII->addItem(new EaseTWBLinkButton('login.php', _('Pokračovat')));
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
