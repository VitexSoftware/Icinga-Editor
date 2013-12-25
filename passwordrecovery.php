<?php

/**
 * Reset hesla
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'Ease/EaseMail.php';
require_once 'Ease/EaseHtmlForm.php';
$Success = false;

$EmailTo = $oPage->getPostValue('Email');

$oPage->includeJavaScript('js/jquery.validate.js');
$oPage->addJavascript('$("#PassworRecovery").validate({
  rules: {
    Email: {
      required: true,
      email: true
    }
  }
});',null,true);


if ($EmailTo) {
    $oPage->takeMyTable();
    $UserEmail = $oPage->easeAddSlashes($EmailTo);
    $UserFound = $oPage->MyDbLink->queryToArray('SELECT id,login FROM user WHERE email=\'' . $UserEmail . '\'');
    if (count($UserFound)) {
        $UserID = intval($UserFound[0]['id']);
        $UserLogin = $UserFound[0]['login'];
        $NewPassword = $oPage->randomString(8);

        $PassChanger = new EaseUser($UserID);
        $PassChanger->passwordChange($NewPassword);

        $Email = $oPage->addItem(new EaseShopMail($UserEmail, _('Nové heslo pro ') . $_SERVER['SERVER_NAME']));
        $Email->addItem(_("Tvoje přihlašovací údaje byly změněny:\n"));

        $Email->addItem(' Login: ' . $UserLogin . "\n");
        $Email->addItem(' Heslo: ' . $NewPassword . "\n");

        $Email->send();

        $oUser->addStatusMessage('Tvoje nové heslo vám bylo odesláno mailem na zadanou adresu <strong>' . $_REQUEST['Email'] . '</strong>');
        $Success = true;
    } else {
        $oUser->addStatusMessage('Promiňnte, ale email <strong>' . $_REQUEST['Email'] . '</strong> nebyl v databázi nalezen', 'warning');
    }
} else {
    $oUser->addStatusMessage(_('Zadejte prosím váš eMail.'));
}


$oPage->addItem(new IEPageTop(_('Obnova zapomenutého hesla')));



if (!$Success) {
    $oPage->column1->addItem('<h1>Zapoměl jsem své heslo!</h1>');

    $oPage->column3->addItem(_('Zapoměl jste heslo? Vložte svou e-mailovou adresu, kterou jste zadal při registraci a my Vám pošleme nové.'));

    $EmailForm = $oPage->column2->addItem(new EaseHtmlForm('PassworRecovery'));
    $EmailForm->addItem(new EaseLabeledTextInput('Email', null,_('Email'), array('size' => '40')));
    $EmailForm->addItem(new EaseJQuerySubmitButton('ok', _('Zaslat nové heslo')));

    if (isset($_POST)) {
        $EmailForm->fillUp($_POST);
    }
} else {
    $oPage->column2->addItem(new EaseTWBLinkButton('login.php', _('Pokračovat')));
}

$oPage->addItem(new IEPageBottom());

$oPage->draw();
?>
