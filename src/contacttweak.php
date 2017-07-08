<?php

namespace Icinga\Editor;

/**
 * Contacts Editor
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$contact = new Engine\Contact($oPage->getRequestValue('contact_id', 'int'));
if (!$contact->getId()) {
    $oPage->redirect('contacts.php');
    exit();
}

switch ($oPage->getRequestValue('action')) {
    case 'rename':
        $newname = $oPage->getRequestValue('newname');
        if (strlen($newname)) {
            if ($contact->rename($newname)) {
                $oUser->addStatusMessage(_('Contact was renamed'), 'success');
            } else {
                $oUser->addStatusMessage(_('Contact was not renamed'), 'warning');
            }
        }
        break;
}

$delete = $oPage->getGetValue('delete', 'string');
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

$oPage->addItem(new UI\PageTop(_('Contact editor') . ' ' . $contact->getName()));
$oPage->addPageColumns();

$oPage->columnII->addItem(new \Ease\Html\H3Tag($contact->getName()));

$oPage->columnII->addItem(new UI\ContactTweaker($contact));

if ($contact->getName() != $oUser->getUserLogin()) {
    $oPage->columnIII->addItem($contact->deleteButton($contact->getName(), 'contact_id=' . $contact->getId()));
}
$renameForm = new \Ease\TWB\Form('Rename', '?action=rename&amp;contact_id=' . $contact->getID() . '&contact_id=' . $contact->getId());
$renameForm->addItem(new \Ease\Html\InputTextTag('newname'), $contact->getName(), ['class' => 'form-control']);
$renameForm->addItem(new \Ease\TWB\SubmitButton(_('Rename'), 'success'));

$oPage->columnIII->addItem(new \Ease\TWB\Panel(_('Renaming'), 'default', $renameForm));

$oPage->addItem(new UI\PageBottom());

$oPage->draw();
