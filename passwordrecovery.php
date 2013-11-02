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

$EmailTo = $OPage->getPostValue('Email');

$OPage->includeJavaScript('js/jquery.validate.js');
$OPage->addJavascript('$("#PassworRecovery").validate({
  rules: {
    Email: {
      required: true,
      email: true
    }
  }
});',null,true);


if ($EmailTo) {
    $OPage->takeMyTable();
    $UserEmail = $OPage->easeAddSlashes($EmailTo);
    $UserFound = $OPage->MyDbLink->queryToArray('SELECT id,login FROM user WHERE email=\'' . $UserEmail . '\'');
    if (count($UserFound)) {
        $UserID = intval($UserFound[0]['id']);
        $UserLogin = $UserFound[0]['login'];
        $NewPassword = $OPage->randomString(8);

        $PassChanger = new EaseUser($UserID);
        $PassChanger->passwordChange($NewPassword);

        $Email = $OPage->addItem(new EaseShopMail($UserEmail, _('Nové heslo pro ') . $_SERVER['SERVER_NAME']));
        $Email->addItem(_("Tvoje přihlašovací údaje byly změněny:\n"));

        $Email->addItem(' Login: ' . $UserLogin . "\n");
        $Email->addItem(' Heslo: ' . $NewPassword . "\n");

        $Email->send();

        $OUser->addStatusMessage('Tvoje nové heslo vám bylo odesláno mailem na zadanou adresu <strong>' . $_REQUEST['Email'] . '</strong>');
        $Success = true;
    } else {
        $OUser->addStatusMessage('Promiňnte, ale email <strong>' . $_REQUEST['Email'] . '</strong> nebyl v databázi nalezen', 'warning');
    }
} else {
    $OUser->addStatusMessage(_('Zadejte prosím váš eMail.'));
}


$OPage->addItem(new IEPageTop(_('Obnova zapomenutého hesla')));



if (!$Success) {
    $OPage->column1->addItem('<h1>Zapoměl jsem své heslo!</h1>');

    $OPage->column3->addItem(_('Zapoměl jste heslo? Vložte svou e-mailovou adresu, kterou jste zadal při registraci a my Vám pošleme nové.'));

    $EmailForm = $OPage->column2->addItem(new EaseHtmlForm('PassworRecovery'));
    $EmailForm->addItem(new EaseLabeledTextInput('Email', null,_('Email'), array('size' => '40')));
    $EmailForm->addItem(new EaseJQuerySubmitButton('ok', _('Zaslat nové heslo')));

    if (isset($_POST)) {
        $EmailForm->fillUp($_POST);
    }
} else {
    $OPage->column2->addItem(new EaseTWBLinkButton('login.php', _('Pokračovat')));
}

$OPage->addItem(new IEPageBottom());

$OPage->draw();
?>
