<?php

namespace Icinga\Editor;

/**
 * Sign in page
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

if (!is_object($oUser)) {
    die(_('Cookies requied'));
}

$login = $oPage->getRequestValue('login');
if ($login) {
    $oUser = \Ease\Shared::user(new User());

    if ($oUser->tryToLogin($_POST)) {
        if ($oUser->getUserID() == 1) {
            $oUser->setSettingValue('admin', TRUE);
        }
        $oUser->setSettingValue('plaintext', $_POST[$oUser->passwordColumn]);

        $backurl = $oPage->getRequestValue('backurl');
        if ($backurl) {
            $oPage->redirect($backurl);
        } else {
            $oPage->redirect('main.php');
        }
        exit;
    }
} else {

    $forceID = $oPage->getRequestValue('force_id', 'int');
    if (!is_null($forceID)) {
        \Ease\Shared::user(new User($forceID));
        \Ease\Shared::user()->SettingsColumn = 'settings';
        $oUser->setSettingValue('admin', TRUE);
        $oUser->addStatusMessage(_('Signed in as: ').$oUser->getUserLogin(),
            'success');
        \Ease\Shared::user()->loginSuccess();
        $oPage->redirect('main.php');
        exit;
    } else {
        $oPage->addStatusMessage(_('Please enter your login name'));
    }
}

$oPage->addItem(new UI\PageTop(_('Sign in')));
$oPage->addPageColumns();

$loginFace = new \Ease\Html\DivTag();

$oPage->columnI->addItem(new \Ease\Html\DivTag(_('Please enter your login details:')));

$loginForm = $loginFace->addItem(new \Ease\TWB\Form('Login'));

$loginForm->addItem(new \Ease\TWB\FormGroup(_('User Name'),
    new \Ease\Html\InputTextTag('login', $login)));
$loginForm->addItem(new \Ease\TWB\FormGroup(_('Pasword'),
    new \Ease\Html\InputPasswordTag('password')));
$loginForm->addItem(new \Ease\TWB\SubmitButton(_('Sign In'), 'success'));

$loginPanel = new \Ease\TWB\Panel(_('Sign in'), 'info', $loginFace);
$loginPanel->body->setTagProperties(['style' => 'margin: 20px']);

$oPage->columnII->addItem($loginPanel);

$oPage->columnI->addItem(new \Ease\TWB\LinkButton('passwordrecovery.php',
    _('Password recovery')));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
