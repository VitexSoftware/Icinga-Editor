<?php

/**
 * Přihlašovací stránka
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';
require_once 'Ease/EaseJQueryWidgets.php';
require_once 'classes/IETwitter.php';

if (!is_object($oUser)) {
    die(_('Cookies jsou vyžadovány'));
}

$login = $oPage->getRequestValue('login');
if ($login) {
    EaseShared::user(new IEUser());
    EaseShared::user()->SettingsColumn = 'settings';
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
        EaseShared::user(new IEUser($forceID));
        EaseShared::user()->SettingsColumn = 'settings';
        $oUser->setSettingValue('admin', TRUE);
        $oUser->addStatusMessage(_('Přihlášen jako: ') . $oUser->getUserLogin(), 'success');
        EaseShared::user()->loginSuccess();
        $oPage->redirect('main.php');
        exit;
    } else {
        $oPage->addStatusMessage(_('Prosím zadejte vaše přihlašovací udaje'));
    }
}

$oPage->addItem(new IEPageTop(_('Přihlaš se')));
$oPage->addPageColumns();

$loginFace = new EaseHtmlDivTag('LoginFace');

$oPage->columnI->addItem(new EaseHtmlDivTag('WelcomeHint', _('Zadejte, prosím, Vaše přihlašovací údaje:')));

$loginForm = $loginFace->addItem(new EaseTWBForm('Login'));
$loginForm->addItem(new EaseTWBFormGroup(_('Uživatelské jméno'), new EaseHtmlInputTextTag('login', $login)));
$loginForm->addItem(new EaseTWBFormGroup(_('Heslo'), new EaseHtmlInputPasswordTag('password')));
$loginForm->addItem(new EaseTWSubmitButton('LogIn', _('Přihlášení')));

$oPage->columnII->addItem($loginFace);

$oPage->columnI->addItem(new EaseTWBLinkButton('passwordrecovery.php', _('Obnova hesla')));

/*
  $oPage->columnII->addItem(new EaseHtmlDivTag('TwitterAuth', IETwitter::AuthButton('twauth.php')));

  $oPage->columnIII->addItem( '
  <a class="twitter-timeline"  href="https://twitter.com/VSMonitoring" data-widget-id="255378607919210497">Tweets by @VSMonitoring</a>
  <script>!function (d,s,id) {var js,fjs=d.getElementsByTagName(s)[0];if (!d.getElementById(id)) {js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
  ' );
 */

$oPage->addItem(new IEPageBottom());

$oPage->draw();
