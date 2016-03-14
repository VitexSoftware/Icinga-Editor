<?php

namespace Icinga\Editor;

/**
 * Přihlašovací stránka
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

if (!is_object($oUser)) {
    die(_('Cookies jsou vyžadovány'));
}

$login = $oPage->getRequestValue('login');
if ($login) {
    \Ease\Shared::user(new User());
    \Ease\Shared::user()->SettingsColumn = 'settings';
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
        $oUser->addStatusMessage(_('Přihlášen jako: ').$oUser->getUserLogin(),
            'success');
        \Ease\Shared::user()->loginSuccess();
        $oPage->redirect('main.php');
        exit;
    } else {
        $oPage->addStatusMessage(_('Prosím zadejte vaše přihlašovací udaje'));
    }
}

$oPage->addItem(new UI\PageTop(_('Přihlaš se')));
$oPage->addPageColumns();

$loginFace = new \Ease\Html\Div();

$oPage->columnI->addItem(new \Ease\Html\Div(_('Zadejte, prosím, Vaše přihlašovací údaje:')));

$loginForm = $loginFace->addItem(new \Ease\TWB\Form('Login'));
$loginForm->addItem(new \Ease\TWB\FormGroup(_('Uživatelské jméno'),
    new \Ease\Html\InputTextTag('login', $login)));
$loginForm->addItem(new \Ease\TWB\FormGroup(_('Heslo'),
    new \Ease\Html\InputPasswordTag('password')));
$loginForm->addItem(new \Ease\TWB\SubmitButton('LogIn', _('Přihlášení')));

$oPage->columnII->addItem($loginFace);

$oPage->columnI->addItem(new \Ease\TWB\LinkButton('passwordrecovery.php',
    _('Obnova hesla')));

/*
  $oPage->columnII->addItem(new \Ease\Html\DivTag('TwitterAuth', IETwitter::AuthButton('twauth.php')));

  $oPage->columnIII->addItem( '
  <a class="twitter-timeline"  href="https://twitter.com/VSMonitoring" data-widget-id="255378607919210497">Tweets by @VSMonitoring</a>
  <script>!function (d,s,id) {var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)) {js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
  ' );
 */

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
