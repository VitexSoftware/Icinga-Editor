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
require_once 'classes/IEService.php';
require_once 'classes/IECfgEditor.php';

$OPage->onlyForLogged();

$Service = new IEService($OPage->getRequestValue('service_id', 'int'));

if ($OPage->isPosted()) {
    $Service->takeData($_POST);
    $ServiceID = $Service->saveToMySQL();
    if (is_null($ServiceID)) {
        $OUser->addStatusMessage(_('Služba nebyla uložena'), 'warning');
    } else {
        $OUser->addStatusMessage(_('Služba byla uložena'), 'success');
    }
} else {
    $Use = $OPage->getGetValue('use');
    if ($Use) {
        $Service->setDataValue('use', $Use);
    }
}

$Service->saveMembers();

$Delete = $OPage->getGetValue('delete', 'bool');
if ($Delete == 'true') {
    $Service->delete();
}



$OPage->addItem(new IEPageTop(_('Editace služby') . ' ' . $Service->getName()));


$ServiceEdit = new IECfgEditor($Service);

$Form = $OPage->column2->addItem(new EaseHtmlForm('Service', 'service.php', 'POST', $ServiceEdit, array('class' => 'form-horizontal')));
$Form->setTagID($Form->getTagName());
if (!is_null($Service->getMyKey())) {
    $Form->addItem(new EaseHtmlInputHiddenTag($Service->getMyKeyColumn(), $Service->getMyKey()));
}
$Form->addItem('<br>');
$Form->addItem(new EaseTWSubmitButton(_('Uložit'),'success'));
$OPage->AddCss('
input.ui-button { width: 100%; }
');

$OPage->column3->addItem($Service->deleteButton());
if ($Service->getId()) {
    $OPage->column1->addItem($Service->ownerLinkButton());
}
$OPage->addItem(new IEPageBottom());


$OPage->draw();
?>
