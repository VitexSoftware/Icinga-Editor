<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - index page
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

if ($oUser->getUserId()) {
    $oPage->redirect('main.php');
    exit;
}

$oPage->addItem(new UI\PageTop(_('Icinga Editor')));
$oPage->addPageColumns();

$oPage->columnI->addItem('<li>'._('Watch the hosts'));
$oPage->columnI->addItem('<li>'._('Watch the services'));
$oPage->columnI->addItem('<li>'._('Notifications on mail/jabber/sms/twitter'));


$oPage->columnII->addItem(new \Ease\Html\ImgTag('img/vsmonitoring.png'));
$oPage->columnII->addItem(new \Ease\Html\ATag('http://icinga.org/',
        new \Ease\Html\ImgTag('img/icinga_logo4-300x109.png')));

$loginForm = new \Ease\TWB\Form('Login', 'login.php');

$loginForm->addItem(new \Ease\TWB\FormGroup(_('User Name'),
        new \Ease\Html\InputTextTag('login')));
$loginForm->addItem(new \Ease\TWB\FormGroup(_('Pasword'),
        new \Ease\Html\InputPasswordTag('password')));
$loginForm->addItem(new \Ease\TWB\SubmitButton(_('Sign In'), 'success'));

$loginPanel = new \Ease\TWB\Panel(_('Sign in'), 'info', $loginForm);
$loginPanel->body->setTagProperties(['style' => 'margin: 20px']);

$oPage->columnIII->addItem($loginPanel);

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
