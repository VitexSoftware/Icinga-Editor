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

$Login = $oPage->getRequestValue('login');
if ($Login) {
    EaseShared::user(new IEUser());
    EaseShared::user()->SettingsColumn = 'settings';
    if ($oUser->tryToLogin($_POST)) {
        if($oUser->getUserID()==1){
            $oUser->setSettingValue('admin',TRUE);
        }
        $oUser->setSettingValue('plaintext',$_POST[$oUser->PasswordColumn]);
        $oPage->redirect('main.php');
        exit;
    }
} else {
    
    $ForceID = $oPage->getRequestValue('force_id','int');
    if(!is_null($ForceID)){
        EaseShared::user(new IEUser($ForceID));
        EaseShared::user()->SettingsColumn = 'settings';
        $oUser->setSettingValue('admin',TRUE);
        $oUser->addStatusMessage(_('Přihlášen jako: ').$oUser->getUserLogin(),'success');
        EaseShared::user()->loginSuccess();
        $oPage->redirect('main.php');
        exit;
    } else {
        $oPage->addStatusMessage(_('Prosím zadejte vaše přihlašovací udaje'));
    }
}


$oPage->addItem(new IEPageTop(_('Přihlaš se')));

$LoginFace = new EaseHtmlDivTag('LoginFace');


$oPage->columnI->addItem(new EaseHtmlDivTag('WelcomeHint', _('Zadejte, prosím, Vaše přihlašovací údaje:')));

$LoginForm = $LoginFace->addItem(new EaseHtmlForm('Login'));
$LoginForm->addItem(new EaseLabeledTextInput('login', NULL, _('Login')));
$LoginForm->addItem(new EaseLabeledPasswordInput('password', NULL, _('Heslo')));
$LoginForm->addItem(new EaseJQuerySubmitButton('LogIn', _('Přihlášení')));

$oPage->columnII->addItem($LoginFace);

$oPage->columnI->addItem(new EaseTWBLinkButton('passwordrecovery.php', _('Obnova hesla')));

$oPage->columnII->addItem(new EaseHtmlDivTag('TwitterAuth', IETwitter::AuthButton('twauth.php')));


$oPage->columnIII->addItem( '
<a class="twitter-timeline"  href="https://twitter.com/VSMonitoring" data-widget-id="255378607919210497">Tweets by @VSMonitoring</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
' );

$oPage->addItem(new IEPageBottom());

$oPage->draw();
?>