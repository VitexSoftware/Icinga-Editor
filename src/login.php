<?php

/**
 * Icinga Editor - Login page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2012-2020 Vitex Software
 */

namespace Icinga\Editor;

use Ease\Html\DivTag;
use Ease\Html\InputPasswordTag;
use Ease\Html\InputTextTag;
use Ease\Shared;
use Ease\TWB\Form;
use Ease\TWB\FormGroup;
use Ease\TWB\LinkButton;
use Ease\TWB\Panel;
use Ease\TWB\SubmitButton;
use Icinga\Editor\UI\PageBottom;
use Icinga\Editor\UI\PageTop;
use PDOException;

require_once 'includes/IEInit.php';

$shared = Shared::singleton();

$login = $oPage->getRequestValue('login');
if ($login) {
    try {
        $oUser = Shared::user(new User());
    } catch (PDOException $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }
    if ($oUser->tryToLogin($_POST)) {
        $oPage->redirect('main.php');
        exit;
    }
}

$oPage->addItem(new PageTop(_('Sign in')));
$oPage->addPageColumns();

$loginFace = new DivTag();

$oPage->columnI->addItem(new DivTag(_('Please enter your login details:')));

$loginForm = $loginFace->addItem(new Form(['name' => 'Login']));

$loginForm->addItem(new FormGroup(_('User Name'),
                new InputTextTag('login', $login)));
$loginForm->addItem(new FormGroup(_('Pasword'),
                new InputPasswordTag('password')));
$loginForm->addItem(new SubmitButton(_('Sign In'), 'success'));

$loginPanel = new Panel(_('Sign in'), 'info', $loginFace);
$loginPanel->body->setTagProperties(['style' => 'margin: 20px']);

$oPage->columnII->addItem($loginPanel);

$oPage->columnI->addItem(new LinkButton('passwordrecovery.php',
                _('Password recovery')));

$oPage->addItem(new PageBottom());

$oPage->draw();
