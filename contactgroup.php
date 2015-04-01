<?php

/**
 * Icinga Editor - skupina kontaktů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new IEPageTop(_('Editace skupiny kontaktu')));


$contactgroup = new IEContactgroup($oPage->getRequestValue('contactgroup_id', 'int'));

if ($oPage->isPosted()) {
    $contactgroup->takeData($_POST);
    $ContactgroupID = $contactgroup->saveToMySQL();
    if (is_null($ContactgroupID)) {
        $oUser->addStatusMessage(_('Skupina kontaktů nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina kontaktů byla uložena'), 'success');
    }
}

$contactgroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $contactgroup->delete();
}

$contactgroupEdit = new IECfgEditor($contactgroup);

$form = new EaseTWBForm('Contactgroup', 'contactgroup.php', 'POST', $contactgroupEdit, array('class' => 'form-horizontal'));
$form->setTagID($form->getTagName());
if (!is_null($contactgroup->getMyKey())) {
    $form->addItem(new EaseHtmlInputHiddenTag($contactgroup->getmyKeyColumn(), $contactgroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$oPage->addItem(new IEPageBottom());

$infopanel = new IEInfoBox($contactgroup);
$tools = new EaseTWBPanel(_('Nástroje'), 'warning');
if ($contactgroup->getId()) {
    $tools->addItem($contactgroup->deleteButton());
}
$pageRow = new EaseTWBRow;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new EaseTWBPanel(_('Skupina kontaktů') . ' <strong>' . $contactgroup->getName() . '</strong>', 'default', $form));
$pageRow->addColumn(4, $tools);

$oPage->container->addItem($pageRow);

$oPage->draw();
