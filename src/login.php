<?php

namespace Icinga\Editor;

/**
 * Sign in page
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009-2019
 */
require_once 'includes/IEInit.php';

$login = $oPage->getRequestValue('login');
if ($login) {
    try {
        $oUser->setLogin($login);

        if ($oUser->tryToLogin($_POST)) {
            if ($oUser->getUserID() == 1) {
                $oUser->setSettingValue('admin', TRUE);
            }
            $oUser->setSettingValue('plaintext', $_POST[$oUser->passwordColumn]);
            \Ease\Shared::user($oUser);
            $_SESSION['test'] = $oUser;

            
            $backurl = $oPage->getRequestValue('backurl');
            if ($backurl) {
                $oPage->redirect($backurl);
            } else {
                $oPage->redirect('main.php');
            }
            exit;
        }
    } catch (\PDOException $e) {
        $oPage->addStatusMessage($e->getMessage(), 'error');
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

$loginForm = $loginFace->addItem(new \Ease\TWB\Form(['name'=>'Login']));

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
