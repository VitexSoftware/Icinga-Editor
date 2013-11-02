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

if (!is_object($OUser)) {
    die(_('Cookies jsou vyžadovány'));
}

$Login = $OPage->getRequestValue('login');
if ($Login) {
    EaseShared::user(new IEUser());
    EaseShared::user()->SettingsColumn = 'settings';
    if ($OUser->tryToLogin($_POST)) {
        if($OUser->getUserID()==1){
            $OUser->setSettingValue('admin',TRUE);
        }
        $OUser->setSettingValue('plaintext',$_POST[$OUser->PasswordColumn]);
        $OPage->redirect('main.php');
        exit;
    }
} else {
    
    $ForceID = $OPage->getRequestValue('force_id','int');
    if(!is_null($ForceID)){
        EaseShared::user(new IEUser($ForceID));
        EaseShared::user()->SettingsColumn = 'settings';
        $OUser->setSettingValue('admin',TRUE);
        $OUser->addStatusMessage(_('Přihlášen jako: ').$OUser->getUserLogin(),'success');
        EaseShared::user()->loginSuccess();
        $OPage->redirect('main.php');
        exit;
    } else {
        $OPage->addStatusMessage(_('Prosím zadejte vaše přihlašovací udaje'));
    }
}


$OPage->addItem(new IEPageTop(_('Přihlaš se')));

$LoginFace = new EaseHtmlDivTag('LoginFace');


$OPage->column1->addItem(new EaseHtmlDivTag('WelcomeHint', _('Zadejte, prosím, Vaše přihlašovací údaje:')));

$LoginForm = $LoginFace->addItem(new EaseHtmlForm('Login'));
$LoginForm->addItem(new EaseLabeledTextInput('login', NULL, _('Login')));
$LoginForm->addItem(new EaseLabeledPasswordInput('password', NULL, _('Heslo')));
$LoginForm->addItem(new EaseJQuerySubmitButton('LogIn', _('Přihlášení')));

$OPage->column2->addItem($LoginFace);

$OPage->column1->addItem(new EaseTWBLinkButton('passwordrecovery.php', _('Obnova hesla')));

$OPage->column2->addItem(new EaseHtmlDivTag('TwitterAuth', IETwitter::AuthButton('twauth.php')));


$OPage->column3->addItem( '
<a class="twitter-timeline"  href="https://twitter.com/VSMonitoring" data-widget-id="255378607919210497">Tweets by @VSMonitoring</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
' );

$OPage->addItem(new IEPageBottom());

$OPage->draw();
?>