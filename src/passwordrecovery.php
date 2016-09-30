<?php

namespace Icinga\Editor;

/**
 * Reset hesla
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-14 info@vitexsoftware.cz (G)
 */
require_once 'includes/IEInit.php';
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
});', null, true);

if ($emailTo) {
    $userEmail = \Ease\Shared::db()->easeAddSlashes($emailTo);
    $userFound = \Ease\Shared::db()->queryToArray('SELECT id,login FROM user WHERE email=\''.$userEmail.'\'');
    if (count($userFound)) {
        $userID      = intval($userFound[0]['id']);
        $userLogin   = $userFound[0]['login'];
        $newPassword = $oPage->randomString(8);

        $passChanger = new User($userID);
        $passChanger->passwordChange($newPassword);

        $email = $oPage->addItem(new EaseMail($userEmail,
            'FlexiHuBee - '.sprintf(_('New password for %s'),
                $_SERVER['SERVER_NAME'])));
        $email->setMailHeaders(['From' => constant('EMAIL_FROM')]);
        $email->addItem(_("Sign On informations was changed:\n"));

        $email->addItem(_('Username').': '.$userLogin."\n");
        $email->addItem(_('Password').': '.$newPassword."\n");

        $email->send();

        $oUser->addStatusMessage(sprintf(_('Your new password was sent to %s'),
                '<strong>'.$_REQUEST['Email'].'</strong>'));
        $success = true;
    } else {
        $oUser->addStatusMessage(sprintf(_('unknow email address %s'),
                '<strong>'.$_REQUEST['Email'].'</strong>'), 'warning');
    }
} else {
    $oUser->addStatusMessage(_('Please enter your email.'));
}

$oPage->addItem(new UI\PageTop(_('Lost password recovery')));
$oPage->addPageColumns();

if (!$success) {
    $columnI->addItem(new \Ease\Html\H1Tag('Lost password'));

    $columnIII->addItem(_('Forgot your password? Enter your e-mail address you entered during the registration and we will send you a new one.'));

    $emailForm = $columnII->addItem(new \Ease\TWB\Form('PassworRecovery'));
    $emailForm->addItem(new EaseLabeledTextInput('Email', null, _('Email'),
        ['size' => '40', 'class' => 'form-control']));
    $emailForm->addItem(new EaseTWSubmitButton(_('Send New Password')));

    if (isset($_POST)) {
        $mailForm->fillUp($_POST);
    }
} else {
    $columnII->addItem(new \Ease\TWB\LinkButton('login.php', _('Continue')));
    $oPage->redirect('login.php');
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
