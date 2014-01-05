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

    $email_address = addslashes(strtolower($_POST['email_address']));

    if (isset($_POST['parent'])) {
        $CustomerParent = addslashes($_POST['parent']);
    } else {
        $CustomerParent = $oUser->getUserID();
    }
    $login = addslashes($_POST['login']);
    if (isset($_POST['password']))
        $password = addslashes($_POST['password']);
    if (isset($_POST['confirmation']))
        $confirmation = addslashes($_POST['confirmation']);

    $error = false;

    if (strlen($email_address) < 5) {
        $error = true;
        $oUser->addStatusMessage(_('mailová adresa je příliš krátká'), 'warning');
    } else {
        if (!$oUser->IsEmail($email_address, true)) {
            $error = true;
            $oUser->addStatusMessage(_('chyba v mailové adrese'), 'warning');
        } else {
            $check_email = EaseShared::myDbLink()->queryToValue("SELECT COUNT(*) AS total FROM user WHERE email = '" . $oPage->EaseAddSlashes($email_address) . "'");
            if ($check_email > 0) {
                $error = true;
                $oUser->addStatusMessage(sprintf(_('Mailová adresa %s je již zaregistrována'), $email_address), 'warning');
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

    $UsedLogin = EaseShared::myDbLink()->QueryToValue('SELECT id FROM user WHERE login=\'' . $oPage->EaseAddSlashes($login) . '\'');
    if ($UsedLogin) {
        $error = true;
        $oUser->addStatusMessage(sprintf(_('Zadané uživatelské jméno %s je již v databázi použito. Zvolte prosím jiné.'), $login), 'warning');
    }

    if ($error == false) {

        $newOUser = new IEUser();
        //TODO zde by se měly doplnit defaultní hodnoty z konfiguráku registry.php
        $newOUser->setData(
                array(
                    'email' => $email_address,
                    'password' => $newOUser->encryptPassword($password),
                    'parent' => (int) $CustomerParent,
                    'login' => $login
                )
        );

        $UserID = $newOUser->insertToMySQL();

        if ($UserID) {
            $newOUser->setMyKey($UserID);

            $oUser->addStatusMessage(_('Uživatelský účet byl vytvořen'), 'success');
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
            $contact->setData(array('contact_name' => $login, 'email' => $email_address, 'alias' => $firstname . ' ' . $lastname, 'use' => 'generic-contact', $contact->userColumn => $UserID, 'generate' => true, 'register' => 1));
            $contactID = $contact->saveToMySQL();
            if ($contactID) {
                $oUser->addStatusMessage(_('Prvotní kontakt byl založen'), 'success');
            } else {
                $oUser->addStatusMessage(_('Prvotní kontakt nebyl založen'), 'warning');
            }

            $contactGroup = new IEContactgroup();
            $contactGroup->setData(array('contactgroup_name' => $login, 'alias' => _('Skupina') . '_' . $login, 'generate' => true, $contactGroup->userColumn => $UserID));
            $contactGroup->addMember('members', $contactID, $login);
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
            $email->Send();
        }
    }
}

$oPage->AddCss('
input.ui-button { width: 220px; }
');

$oPage->addItem(new IEPageTop(_('Registrace')));

$oPage->columnI->addItem(new EaseHtmlDivTag('WelcomeHint', _('Vítejte v registraci')));

$RegFace = $oPage->columnII->addItem(new EaseHtmlDivTag('RegFace'));

$RegForm = $RegFace->addItem(new EaseHtmlForm('create_account', 'createaccount.php', 'POST', null, array('class' => 'form-horizontal')));
if ($oUser->getUserID()) {
    $RegForm->addItem(new EaseHtmlInputHiddenTag('u_parent', $oUser->GetUserID()));
}

$Account = new EaseHtmlH3Tag(_('Účet'));
$Account->addItem(new EaseLabeledTextInput('login', NULL, _('přihlašovací jméno') . ' *'));
$Account->addItem(new EaseLabeledPasswordStrongInput('password', NULL, _('heslo') . ' *'));
$Account->addItem(new EaseLabeledPasswordControlInput('confirmation', NULL, _('potvrzení hesla') . ' *', array('id' => 'confirmation')));
$Account->addItem(new EaseLabeledTextInput('email_address', NULL, _('emailová adresa') . ' *' . _(' (pouze malými písmeny)')));

$RegForm->addItem(new EaseHtmlDivTag('Account', $Account));
$RegForm->addItem(new EaseHtmlDivTag('Submit', new EaseHtmlInputSubmitTag('Register', _('Registrovat'), array('title'=>_('dokončit registraci'),'class' => 'btn btn-success'))));

if (isset($_POST)) {
    $RegForm->fillUp($_POST);
}

$oPage->addItem(new IEPageBottom());
$oPage->draw();
