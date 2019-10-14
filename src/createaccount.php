<?php

namespace Icinga\Editor;

/**
 * Založení nového accoutu
 *
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

$process = false;

$email_address = $oPage->getPostValue('email_address');
$login         = $oPage->getPostValue('login');
$password      = $oPage->getPostValue('password');
$confirmation  = $oPage->getPostValue('confirmation');


if ($oPage->isPosted()) {

    $process = true;

    $emailAddress = addslashes(strtolower($_POST['email_address']));

    if (isset($_POST['parent'])) {
        $customerParent = addslashes($_POST['parent']);
    } else {
        $customerParent = $oUser->getUserID();
    }
    $login = addslashes($_POST['login']);
    if (isset($_POST['password'])) {
        $password = addslashes($_POST['password']);
    }
    if (isset($_POST['confirmation'])) {
        $confirmation = addslashes($_POST['confirmation']);
    }

    $error = false;

    if (strlen($emailAddress) < 5) {
        $error = true;
        $oUser->addStatusMessage(_('Mail address is too short'), 'warning');
    } else {
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            $error = true;
            $oUser->addStatusMessage(_('invalid email address'), 'warning');
        } else {
            $oUser       = new User();
            $check_email = $oUser->listingQuery()->where('email',
                    addslashes($emailAddress))->fetch();

            if (!empty($check_email)) {
                $error = true;
                $oUser->addStatusMessage(sprintf(_('Mail address %s is allready registered'),
                        $emailAddress), 'warning');
            }
        }
    }

    if (strlen($password) < 5) {
        $error = true;
        $oUser->addStatusMessage(_('password is too short'), 'warning');
    } elseif ($password != $confirmation) {
        $error = true;
        $oUser->addStatusMessage(_('Password control does not match'), 'warning');
    }

    $usedLogin = $oUser->listingQuery()->where($oUser->loginColumn, addslashes($login))->fetch();
    if (!empty($usedLogin)) {
        $error = true;
        $oUser->addStatusMessage(sprintf(_('Username %s is used. Please choose another one'),
                $login), 'warning');
    }

    if ($error == false) {

        $oUser->setData(
            [
                'email' => $emailAddress,
                'parent' => (int) $customerParent,
                'login' => $login,
                'password' => \Ease\User::encryptPassword($password)
            ]
        );

        $userID = $oUser->insertToSQL();

        if (!is_null($userID)) {
            $oUser->setMyKey($userID);
            $oUser->passwordChange($password);

            if ($userID == 1) {
                $oUser->setSettingValue('admin', true);
                $oUser->addStatusMessage(_('Admin prvileges assigned'),
                    'success');
                $oUser->saveToSQL();
            } else {
                $oUser->addStatusMessage(_('User account created'), 'success');
            }

            system('sudo htpasswd -b /etc/icinga/htpasswd.users '.$oUser->getUserLogin().' '.$password);

            $email = new \Ease\HtmlMailer($oUser->getDataValue('email'), _('Sign On info'));
            $email->setMailHeaders(['From' => constant('SEND_MAILS_FROM')]);
            $email->addItem(new \Ease\Html\DivTag(_('Icinga Editor Account')."\n"));
            $email->addItem(new \Ease\Html\DivTag(' Login: '.$oUser->GetUserLogin()."\n"));
            $email->addItem(new \Ease\Html\DivTag(' Password: '.$_POST['password']."\n"));
            $email->send();

            $email = new \Ease\HtmlMailer(constant('SEND_MAILS_FROM'),
                    sprintf(_('New Icinga Editor account: %s'),
                        $oUser->getDataValue('login')));
            $email->setMailHeaders(['From' => constant('SEND_MAILS_FROM')]);
            $email->addItem(new \Ease\Html\DivTag(_("New User:\n")));
            $email->addItem(new \Ease\Html\DivTag(
                    ' Login: '.$oUser->GetUserLogin()."\n", ['id' => 'login']));
            $email->send();

           $oUser->loginSuccess();

            $contact   = new Engine\Contact();
            $contact->setData(
                [
                    'contact_name' => $login,
                    'use' => 'generic-contact',
                    $contact->userColumn => $userID,
                    'generate' => true,
                    'host_notifications_enabled' => true,
                    'service_notifications_enabled' => true,
                    'host_notification_period' => '24x7',
                    'service_notification_period' => '24x7',
                    'service_notification_options' => ' w,u,c,r',
                    'host_notification_options' => 'd,u,r',
                    'service_notification_commands' => 'notify-service-by-email',
                    'host_notification_commands' => 'notify-host-by-email',
                    'register' => 1]
            );
            $contactID = $contact->saveToSQL();
            if ($contactID) {
                $oUser->addStatusMessage(_('Initial contact was created'),
                    'success');
            } else {
                $oUser->addStatusMessage(_('Initial contact was not created'),
                    'warning');
            }

            $mailID = $contact->fork(['email' => $emailAddress]);
            if ($mailID) {
                $oUser->addStatusMessage(_('email contact was created'),
                    'success');
            } else {
                $oUser->addStatusMessage(_('email contact was not created'),
                    'warning');
            }

            $contactGroup = new Engine\Contactgroup();
            $contactGroup->setData(['contactgroup_name' => _('Group').'_'.$login,
                'alias' => _('Group').'_'.$login, 'generate' => true, $contactGroup->userColumn => $userID]);
            $contactGroup->addMember('members', $contactID, $login);
            $contactGroup->addMember('members', $mailID, $contact->getName());
            $cgID         = $contactGroup->saveToSQL();

            if ($cgID) {
                $oUser->addStatusMessage(_('Initial contact group was created'),
                    'success');
            } else {
                $oUser->addStatusMessage(_('Initial contact group was not created'),
                    'warning');
            }

            $hostGroup = new Engine\Hostgroup;
            $hostGroup->setName($oUser->getUserLogin());
            $hostGroup->setDataValue('alias',
                _('Initial Group').' '.$oUser->getUserLogin());
            $hostGroup->setDataValue('generate', true);
            $hostGroup->setUpUser($oUser);
            $hostGroup->insertToSQL();

            $oPage->redirect('wizard-host.php');
            exit;
        } else {
            $oUser->addStatusMessage(_('Error writing to database'), 'error');
            $email = $oPage->addItem(new EaseMail(constant('SEND_ORDERS_TO'),
                    'Sign on Failed'));
            $email->addItem(new \Ease\Html\DivTag('Sign On',
                    $oPage->PrintPre($oUser->getData())));
            $email->send();
        }
    }
}

$oPage->addItem(new UI\PageTop(_('Sign On')));
$oPage->addPageColumns();

$oPage->columnI->addItem(new \Ease\Html\H2Tag(_('Wellcome')));
$oPage->columnI->addItem(
    new \Ease\Html\UlTag(
        [
        _('After registering, you will be prompted to enter straight first host.'),
        _('All notifications target to your email'),
        _('For XMPP (jabber) or SMS, add this on Contact edit page.')
        ]
    )
);

$regFace = $oPage->columnII->addItem(new \Ease\Html\DivTag());

$regForm = $regFace->addItem(new \Ease\TWB\Form('create_account',
        'createaccount.php', 'POST', null, ['class' => 'form-horizontal']));
if ($oUser->getMyKey()) {
    $regForm->addItem(new \Ease\Html\InputHiddenTag('u_parent',
            $oUser->getMyKey()));
}

$regForm->addItem(new \Ease\Html\H3Tag(_('Account')));

$regForm->addInput(
    new \Ease\Html\InputTextTag('login', $login), _('Login name'), null,
    _('Requied'));

$regForm->addInput(
    new \Ease\Html\InputTextTag('email_address', $email_address,
        ['type' => 'email']), _('Email Address'));

$regForm->addInput(
    new \Ease\Html\InputPasswordTag('password', $password), _('Password'), null,
    _('Requied'));
$regForm->addInput(
    new \Ease\Html\InputPasswordTag('confirmation', $confirmation),
    _('Password confirm'), null, _('Requied'));
$regForm->addItem(new \Ease\Html\DivTag(
        new \Ease\Html\InputSubmitTag('Register', _('Singn On'),
            ['title' => _('Finish'), 'class' => 'btn btn-success'])));

if (isset($_POST)) {
    $regForm->fillUp($_POST);
}

$oPage->addItem(new UI\PageBottom());
$oPage->draw();
