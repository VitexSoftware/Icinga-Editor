<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - nový kontakt
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$contact = new Engine\Contact($oPage->getRequestValue('contact_id', 'int'));

$name = $oPage->getRequestValue('name');

if ($oPage->isPosted()) {

    $contact->setData(
        [
            'contact_name' => $name,
            'use' => 'generic-contact',
            $contact->userColumn => $oUser->getUserID(),
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
    if (is_null($contactID)) {
        $oUser->addStatusMessage(_('Kontakt nebyl založen'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Kontakt byl založen'), 'success');
        $oPage->redirect('contacttweak.php?contact_id='.$contact->getId());
        exit;
    }
}

$autoCreate = $oPage->getRequestValue('autocreate');
if ($autoCreate == 'default') {
    $contact->setData(IEContact::ownContactData());
    $contactID = $contact->saveToSQL();
}

$oPage->addItem(new UI\PageTop(_('Založení kontaktu').' '.$contact->getName()));
$oPage->addPageColumns();

$form = $oPage->columnII->addItem(new \Ease\TWB\Form('Contact', 'newcontact.php'));
$form->addItem(new \Ease\TWB\FormGroup(_('Jméno'),
    new \Ease\Html\InputTextTag('name', $name)));
$form->setTagID($form->getTagName());
if (!is_null($contact->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($contact->getmyKeyColumn(),
        $contact->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
