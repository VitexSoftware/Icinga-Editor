<?php

/**
 * Založení nového accoutu
 *
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';
require_once 'Ease/EaseMail.php';
require_once 'classes/IEContact.php';
require_once 'classes/IEContactgroup.php';

$process = false;
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
        $oUser->addStatusMessage(_('mailová adresa je příliš krátká'), 'warning');
    } else {
        if (!$oUser->IsEmail($emailAddress, true)) {
            $error = true;
            $oUser->addStatusMessage(_('chyba v mailové adrese'), 'warning');
        } else {
            $check_email = EaseShared::myDbLink()->queryToValue("SELECT COUNT(*) AS total FROM " . constant('DB_PREFIX') . "user WHERE email = '" . $oPage->EaseAddSlashes($emailAddress) . "'");
            if ($check_email > 0) {
                $error = true;
                $oUser->addStatusMessage(sprintf(_('Mailová adresa %s je již zaregistrována'), $emailAddress), 'warning');
            }
        }
    }

    if (strlen($password) < 5) {
        $error = true;
        $oUser->addStatusMessage(_('heslo je příliš krátké'), 'warning');
    } elseif ($password != $confirmation) {
        $error = true;
        $oUser->addStatusMessage(_('kontrola hesla nesouhlasí'), 'warning');
    }

    $usedLogin = EaseShared::myDbLink()->QueryToValue('SELECT id FROM ' . constant('DB_PREFIX') . 'user WHERE login=\'' . $oPage->EaseAddSlashes($login) . '\'');
    if ($usedLogin) {
        $error = true;
        $oUser->addStatusMessage(sprintf(_('Zadané uživatelské jméno %s je již v databázi použito. Zvolte prosím jiné.'), $login), 'warning');
    }

    if ($error == false) {

        $newOUser = new IEUser();
        //TODO zde by se měly doplnit defaultní hodnoty z konfiguráku registry.php
        $newOUser->setData(
            array(
              'email' => $emailAddress,
              'parent' => (int) $customerParent,
              'login' => $login
            )
        );

        $userID = $newOUser->insertToMySQL();

        if (!is_null($userID)) {
            $newOUser->setMyKey($userID);
            $newOUser->passwordChange($password);

            if ($userID == 0) {
                $newOUser->setSettingValue('admin', TRUE);
                $oUser->addStatusMessage(_('Administrátirský účet byl vytvořen'), 'success');
                $newOUser->saveToMySQL();
            } else {
                $oUser->addStatusMessage(_('Uživatelský účet byl vytvořen'), 'success');
            }

            system('sudo htpasswd -b /etc/icinga/htpasswd.users ' . $newOUser->getUserLogin() . ' ' . $password);

            $newOUser->loginSuccess();

            $email = $oPage->addItem(new EaseMail($newOUser->getDataValue('email'), _('Potvrzení registrace')));
            $email->setMailHeaders(array('From' => EMAIL_FROM));
            $email->addItem(new EaseHtmlDivTag(null, "Právě jste byl/a zaregistrován/a do Aplikace VSMonitoring s těmito přihlašovacími údaji:\n"));
            $email->addItem(new EaseHtmlDivTag(null, ' Login: ' . $newOUser->GetUserLogin() . "\n"));
            $email->addItem(new EaseHtmlDivTag(null, ' Heslo: ' . $_POST['password'] . "\n"));
            $email->send();

            $email = $oPage->addItem(new EaseMail(SEND_INFO_TO, sprintf(_('Nová registrace do VSmonitoringu: %s'), $newOUser->GetUserLogin())));
            $email->setMailHeaders(array('From' => EMAIL_FROM));
            $email->addItem(new EaseHtmlDivTag(null, _("Právě byl zaregistrován nový uživatel:\n")));
            $email->addItem(new EaseHtmlDivTag('login', ' Login: ' . $newOUser->GetUserLogin() . "\n"));
            $email->addItem($newOUser->customerAddress);
            $email->send();

            EaseShared::user($newOUser)->loginSuccess();

            $contact = new IEContact();
            $contact->setData(
                array(
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
                  'register' => 1)
            );
            $contactID = $contact->saveToMySQL();
            if ($contactID) {
                $oUser->addStatusMessage(_('Výchozí kontakt byl založen'), 'success');
            } else {
                $oUser->addStatusMessage(_('Výchozí kontakt nebyl založen'), 'warning');
            }

            $mailID = $contact->fork(array('email' => $emailAddress));
            if ($mailID) {
                $oUser->addStatusMessage(_('Mailový kontakt byl založen'), 'success');
            } else {
                $oUser->addStatusMessage(_('Mailový kontakt nebyl založen'), 'warning');
            }

            $contactGroup = new IEContactgroup();
            $contactGroup->setData(array('contactgroup_name' => _('Skupina') . '_' . $login, 'alias' => _('Skupina') . '_' . $login, 'generate' => true, $contactGroup->userColumn => $userID));
            $contactGroup->addMember('members', $contactID, $login);
            $contactGroup->addMember('members', $mailID, $contact->getName());
            $cgID = $contactGroup->saveToMySQL();

            if ($cgID) {
                $oUser->addStatusMessage(_('Prvotní kontaktní skupina byla založena'), 'success');
            } else {
                $oUser->addStatusMessage(_('Prvotní kontaktní skupina nebyla založena'), 'warning');
            }

            $oPage->redirect('wizard.php');
            exit;
        } else {
            $oUser->addStatusMessage(_('Zápis do databáze se nezdařil!'), 'error');
            $email = $oPage->addItem(new EaseMail(constant('SEND_ORDERS_TO'), 'Registrace uzivatel se nezdařila'));
            $email->addItem(new EaseHtmlDivTag('Fegistrace', $oPage->PrintPre($CustomerData)));
            $email->send();
        }
    }
}

$oPage->addCss('input.ui-button { width: 220px; }');

$oPage->addItem(new IEPageTop(_('Registrace')));

$oPage->columnI->addItem(new EaseHtmlH2Tag(_('Vítejte v registraci')));
$oPage->columnI->addItem(
    new EaseHtmlUlTag(
    array(
  _('Po zaregistování budete rovnou vyzváni k zadání prvního sledovaného hosta.'),
  _('Veškeré notifikace o výsledcích testů vám budou přicházet na zadaný email.'),
  _('Pro zasílání notifikací pomocí XMPP (jabber) či SMS, zadejte tyto v nastavení vašeho kontaktu.')
    )
    )
);

$regFace = $oPage->columnII->addItem(new EaseHtmlDivTag('RegFace'));

$RegForm = $regFace->addItem(new EaseHtmlForm('create_account', 'createaccount.php', 'POST', null, array('class' => 'form-horizontal')));
if ($oUser->getUserID()) {
    $RegForm->addItem(new EaseHtmlInputHiddenTag('u_parent', $oUser->GetUserID()));
}

$Account = new EaseHtmlH3Tag(_('Účet'));
$Account->addItem(new EaseLabeledTextInput('login', NULL, _('přihlašovací jméno') . ' *'));
$Account->addItem(new EaseLabeledPasswordStrongInput('password', NULL, _('heslo') . ' *'));
$Account->addItem(new EaseLabeledPasswordControlInput('confirmation', NULL, _('potvrzení hesla') . ' *', array('id' => 'confirmation')));
$Account->addItem(new EaseLabeledTextInput('email_address', NULL, _('emailová adresa') . ' *' . _(' (pouze malými písmeny)')));

$RegForm->addItem(new EaseHtmlDivTag('Account', $Account));
$RegForm->addItem(new EaseHtmlDivTag('Submit', new EaseHtmlInputSubmitTag('Register', _('Registrovat'), array('title' => _('dokončit registraci'), 'class' => 'btn btn-success'))));

if (isset($_POST)) {
    $RegForm->fillUp($_POST);
}

$oPage->addItem(new IEPageBottom());
$oPage->draw();
