<?php

namespace Icinga\Editor;

/**
 * Reset hesla
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2019 info@vitexsoftware.cz (G)
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
    $oUser = new User();
    $userFound = $oUser->listingQuery()->where('email',addslashes( $emailTo))->fetch();
    
    if (count($userFound)) {
        $userID      = intval($userFound['id']);
        $userLogin   = $userFound['login'];
        $newPassword = \Ease\Functions::randomString(8);

        $passChanger = new User($userID);
        $passChanger->passwordChange($newPassword);

        $email = $oPage->addItem(new \Ease\HtmlMailer($emailTo,
                _('Icinga Editor').' - '.sprintf(_('New password for %s'),
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
        $oUser->addStatusMessage(sprintf(_('unknown email address %s'),
                '<strong>'.$_REQUEST['Email'].'</strong>'), 'warning');
    }
} else {
    $oUser->addStatusMessage(_('Please enter your email.'));
}

$oPage->addItem(new UI\PageTop(_('Lost password recovery')));
$oPage->addPageColumns();

if (!$success) {
    $oPage->columnI->addItem(new \Ease\Html\H1Tag('Lost password'));

    $oPage->columnIII->addItem(_('Forgot your password? Enter your e-mail address you entered during the registration and we will send you a new one.'));

    $emailForm = $oPage->columnII->addItem(new \Ease\TWB\Form('PassworRecovery'));


    $emailForm->addInput(new \Ease\Html\InputEmailTag('Email', null,
            ['type' => 'email']), _('Email'));
    $emailForm->addItem(new \Ease\TWB\SubmitButton(_('Send New Password'),
            'warning'));

    if (isset($_POST)) {
        $emailForm->fillUp($_POST);
    }
} else {
    $oPage->columnII->addItem(new \Ease\TWB\Well([_('Please check your mailbox for new password')
            , ' '._('and').' ', new \Ease\TWB\LinkButton('login.php',
                _('Sign In'), 'success')]));
}

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
