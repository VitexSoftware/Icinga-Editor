<?php

/**
 * Icinga Editor - skupina hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
require_once 'classes/IEHostgroup.php';
require_once 'classes/IECfgEditor.php';

$oPage->onlyForLogged();

$hostgroup = new IEHostgroup($oPage->getRequestValue('hostgroup_id', 'int'));

if ($oPage->isPosted()) {

    switch ($oPage->getRequestValue('action')) {
        case 'contactAsign':
            $contact = new IEContact($oPage->getRequestValue('contact_id', 'int'));
            if ($contact->getId()) {
                $host = new IEHost;
                $groupMembers = $hostgroup->getMembers();
                foreach ($groupMembers as $gmID => $hostName) {
                    $host->loadFromSQL((int) $gmID);
                    $host->addMember('contacts', $contact->getId(), $contact->getName());
                    if ($host->saveToMySQL()) {
                        $host->addStatusMessage(sprintf(_('<strong>%s</strong> byl přidán mezi kontakty <strong>%s</strong>'), $contact->getName(), $host->getName()), 'success');
                    } else {
                        $host->addStatusMessage(sprintf(_('<strong>%s</strong> nebyl přidán mezi kontakty <strong>%s</strong>'), $contact->getName(), $host->getName()), 'warning');
                    }
                }
            } else {
                $hostgroup->addStatusMessage(_('Chyba přiřazení kontaktu'), 'warning');
            }
            break;
        default :
            $hostgroup->takeData($_POST);

            if (!$hostgroup->getId()) {
                $hostgroup->setDataValue('members', array());
            }

            $hostgroupID = $hostgroup->saveToMySQL();
            if (is_null($hostgroupID)) {
                $oUser->addStatusMessage(_('Skupina hostů nebyla uložena'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Skupina hostů byla uložena'), 'success');
            }
            $hostgroup->saveMembers();

            $delete = $oPage->getGetValue('delete', 'bool');
            if ($delete == 'true') {
                $hostgroup->delete();
                $oPage->redirect('hostgroups.php');
                exit();
            }

            break;
    }
}


$oPage->addItem(new IEPageTop(_('Editace skupiny hostů') . ' ' . $hostgroup->getName()));
$oPage->addPageColumns();

$hostgroupEdit = new IECfgEditor($hostgroup);

$form = $oPage->columnII->addItem(new EaseHtmlForm('Hostgroup', 'hostgroup.php', 'POST', $hostgroupEdit, array('class' => 'form-horizontal')));
$form->setTagID($form->getTagName());
if (!is_null($hostgroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($hostgroup->getmyKeyColumn(), $hostgroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$oPage->AddCss('
input.ui-button { width: 100%; }
');

$oPage->columnIII->addItem($hostgroup->deleteButton());

if ($hostgroup->getId()) {
    $oPage->columnI->addItem($hostgroup->ownerLinkButton());
}


$operations = $oPage->columnIII->addItem(new EaseTWBPanel(_('Hromadné operace')), 'success');
$operations->addItem(new IEContactAsignForm);


$oPage->columnIII->addItem(new EaseTWBLinkButton('wizard-host.php?hostgroup_id=' . $hostgroup->getId(), EaseTWBPart::GlyphIcon('plus') . _('nový host ve skupině'), 'success'));

$oPage->addItem(new IEPageBottom());

$oPage->draw();
