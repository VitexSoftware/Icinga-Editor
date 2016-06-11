<?php

namespace Icinga\Editor;

/**
 * Icinga Editor služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$contact = new Engine\IEContact($oPage->getRequestValue('contact_id', 'int'));
if (!$contact->getId()) {
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
                $oUser->addStatusMessage(_('Kontakt nebyl přejmenován'),
                    'warning');
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

$oPage->addItem(new UI\PageTop(_('Editace kontaktu').' '.$contact->getName()));
$oPage->addPageColumns();

$oPage->columnII->addItem(new \Ease\Html\H3Tag($contact->getName()));

$oPage->columnII->addItem(new UI\ContactTweaker($contact));

if ($contact->getName() != $oUser->getUserLogin()) {
    $oPage->columnIII->addItem($contact->deleteButton($contact->getName(),
            'contact_id='.$contact->getId()));
}
$renameForm = new \Ease\TWB\Form('Rename',
    '?action=rename&amp;contact_id='.$contact->getID().'&contact_id='.$contact->getId());
$renameForm->addItem(new \Ease\Html\InputTextTag('newname'),
    $contact->getName(), ['class' => 'form-control']);
$renameForm->addItem(new \Ease\TWB\SubmitButton(_('Přejmenovat'), 'success'));

$oPage->columnIII->addItem(new \Ease\TWB\Panel(_('Přejmenování'), 'default',
    $renameForm));

//$oPage->columnI->addItem(new IEHostSelector($contact));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
