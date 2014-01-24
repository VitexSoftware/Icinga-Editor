<?php

/**
 * Icinga Editor služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEContact.php';
require_once 'classes/IECfgEditor.php';
require_once 'classes/IEHostOverview.php';
require_once 'classes/IEContactTweaker.php';
require_once 'classes/IEHostSelector.php';

$oPage->onlyForLogged();

$contact = new IEContact($oPage->getRequestValue('contact_id', 'int'));
if(!$contact->getId()){
    $oPage->redirect('contacts.php');
    exit();
}

switch ($oPage->getRequestValue('action')) {
    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($contact->rename($newname)) {
                $oUser->addStatusMessage(_('Kontakt byl přejmenován'), 'success');
            } else {
                $oUser->addStatusMessage(_('Kontakt nebyl přejmenován'), 'warning');
            }
        }
        break;
}

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $contact->delete();
    $oPage->redirect('contacts.php');
    exit();
}

$delsubcont = $oPage->getGetValue('delsubcont_id', 'int');
if ($delsubcont) {
    $delcnt = clone $contact;
    $delcnt->delete($delsubcont);
}


$oPage->addItem(new IEPageTop(_('Editace kontaktu') . ' ' . $contact->getName()));

$oPage->columnII->addItem(new EaseHtmlH3Tag($contact->getName()));

$oPage->columnII->addItem(new IEContactTweaker($contact));

$oPage->columnIII->addItem($contact->deleteButton($contact->getName(), 'contact_id=' . $contact->getId()));

$renameForm = new EaseTWBForm('Rename', '?action=rename&amp;contact_id=' . $contact->getID() . '&contact_id=' . $contact->getId());
$renameForm->addItem(new EaseHtmlInputTextTag('newname'), $contact->getName(), array('class' => 'form-control'));
$renameForm->addItem(new EaseTWSubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnIII->addItem(new EaseHtmlFieldSet(_('Přejmenování'), $renameForm));

//$oPage->columnI->addItem(new IEHostSelector($contact));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
