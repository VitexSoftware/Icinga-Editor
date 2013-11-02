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
if ($OPage->isPosted()) {
    $process = true;

    $email_address = addslashes(strtolower($_POST['email_address']));

    if (isset($_POST['parent'])) {
        $CustomerParent = addslashes($_POST['parent']);
    } else {
        $CustomerParent = $OUser->getUserID();
    }
    $login = addslashes($_POST['login']);
    if (isset($_POST['password']))
        $password = addslashes($_POST['password']);
    if (isset($_POST['confirmation']))
        $confirmation = addslashes($_POST['confirmation']);

    $error = false;

    if (strlen($email_address) < 5) {
        $error = true;
        $OUser->addStatusMessage(_('mailová adresa je příliš krátká'), 'warning');
    } else {
        if (!$OUser->IsEmail($email_address, true)) {
            $error = true;
            $OUser->addStatusMessage(_('chyba v mailové adrese'), 'warning');
        } else {
            $check_email = EaseShared::myDbLink()->queryToValue("SELECT COUNT(*) AS total FROM user WHERE email = '" . $OPage->EaseAddSlashes($email_address) . "'");
            if ($check_email > 0) {
                $error = true;
                $OUser->addStatusMessage(sprintf(_('Mailová adresa %s je již zaregistrována'), $email_address), 'warning');
            }
        }
    }



    if (strlen($password) < 5) {
        $error = true;
        $OUser->addStatusMessage(_('heslo je příliš krátké'), 'warning');
    } elseif ($password != $confirmation) {
        $error = true;
        $OUser->addStatusMessage(_('kontrola hesla nesouhlasí'), 'warning');
    }

    $UsedLogin = EaseShared::myDbLink()->QueryToValue('SELECT id FROM user WHERE login=\'' . $OPage->EaseAddSlashes($login) . '\'');
    if ($UsedLogin) {
        $error = true;
        $OUser->addStatusMessage(sprintf(_('Zadané uživatelské jméno %s je již v databázi použito. Zvolte prosím jiné.'), $login), 'warning');
    }

    if ($error == false) {



        $NewOUser = new IEUser();
        //TODO zde by se měly doplnit defaultní hodnoty z konfiguráku Registry.php
        $NewOUser->setData(
                array(
                    'email' => $email_address,
                    'password' => $NewOUser->encryptPassword($password),
                    'parent' => (int) $CustomerParent,
                    'login' => $login
                )
        );

        $UserID = $NewOUser->insertToMySQL();

        if ($UserID) {
            $NewOUser->setMyKey($UserID);

            $OUser->addStatusMessage(_('Uživatelský účet byl vytvořen'), 'success');
            $NewOUser->loginSuccess();

            $Email = $OPage->addItem(new EaseMail($NewOUser->getDataValue('email'), _('Potvrzení registrace')));
            $Email->setMailHeaders(array('From' => EMAIL_FROM));
            $Email->addItem(new EaseHtmlDivTag(null, "Právě jste byl/a zaregistrován/a do Aplikace VSMonitoring s těmito přihlašovacími údaji:\n"));
            $Email->addItem(new EaseHtmlDivTag(null, ' Login: ' . $NewOUser->GetUserLogin() . "\n"));
            $Email->addItem(new EaseHtmlDivTag(null, ' Heslo: ' . $_POST['password'] . "\n"));
            $Email->send();

            $Email = $OPage->addItem(new EaseMail(SEND_INFO_TO, sprintf(_('Nová registrace do VSmonitoringu: %s'), $NewOUser->GetUserLogin())));
            $Email->SetMailHeaders(array('From' => EMAIL_FROM));
            $Email->addItem(new EaseHtmlDivTag(null, _("Právě byl zaregistrován nový uživatel:\n")));
            $Email->addItem(new EaseHtmlDivTag('login', ' Login: ' . $NewOUser->GetUserLogin() . "\n"));
            $Email->addItem($NewOUser->CustomerAddress);
            $Email->send();

            EaseShared::user($NewOUser)->loginSuccess();

            $Contact = new IEContact();
            $Contact->setData(array('contact_name' => $login, 'email' => $email_address, 'alias' => $firstname . ' ' . $lastname, 'use' => 'generic-contact', $Contact->UserColumn => $UserID, 'generate' => true, 'register' => 1));
            $ContactID = $Contact->insertToMySQL();
            if ($ContactID) {
                $OUser->addStatusMessage(_('Prvotní kontakt byl založen'), 'success');
            } else {
                $OUser->addStatusMessage(_('Prvotní kontakt nebyl založen'), 'warning');
            }

            $CG = new IEContactgroup();
            $CG->setData(array('contactgroup_name' => $login, 'alias' => _('Skupina') . '_' . $login, 'use' => 'generic-contact', 'generate' => true, 'register' => 1,$Contactgroup->UserColumn => $UserID));
            $CG->addMember('members', $ContactID, $login);
            $CGID = $CG->insertToMySQL();

            if ($CGID) {
                $OUser->addStatusMessage(_('Prvotní kontaktní skupina byla založena'), 'success');
            } else {
                $OUser->addStatusMessage(_('Prvotní kontaktní skukpina nebyla založena'), 'warning');
            }

            $OPage->redirect('wizard.php');
            exit;
        } else {
            $OUser->addStatusMessage(_('Zápis do databáze se nezdařil!'), 'error');
            $Email = $OPage->addItem(new EaseMail(constant('SEND_ORDERS_TO'), 'Registrace uzivatel se nezdařila'));
            $Email->addItem(new EaseHtmlDivTag('Fegistrace', $OPage->PrintPre($CustomerData)));
            $Email->Send();
        }
    }
}


$OPage->AddCss('
input.ui-button { width: 220px; }
');


$OPage->addItem(new IEPageTop(_('Registrace')));

$OPage->column1->addItem(new EaseHtmlDivTag('WelcomeHint', _('Vítejte v registraci')));

$RegFace = $OPage->column2->addItem(new EaseHtmlDivTag('RegFace'));


$RegForm = $RegFace->addItem(new EaseHtmlForm('create_account', 'createaccount.php', 'POST', null, array('class' => 'form-horizontal')));
if ($OUser->getUserID()) {
    $RegForm->addItem(new EaseHtmlInputHiddenTag('u_parent', $OUser->GetUserID()));
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

$OPage->addItem(new IEPageBottom());
$OPage->draw();
?>

