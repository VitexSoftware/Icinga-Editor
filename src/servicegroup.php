<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - skupina služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$serviceGroup = new Engine\Servicegroup($oPage->getRequestValue('servicegroup_id',
        'int'));

if ($oPage->isPosted()) {
    $serviceGroup->takeData($_POST);
    $ServicegroupID = $serviceGroup->saveToSQL();
    if (is_null($ServicegroupID)) {
        $oUser->addStatusMessage(_('Skupina služeb nebyla uložena'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Skupina služeb byla uložena'), 'success');
    }
}

$serviceGroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $serviceGroup->delete();
    $oPage->redirect('servicegroups.php');
}

$oPage->addItem(new UI\PageTop(_('Editace skupiny služeb').' '.$serviceGroup->getName()));

$servicegroupEdit = new UI\CfgEditor($serviceGroup);

$form = new \Ease\Html\Form('Servicegroup', 'servicegroup.php', 'POST',
    $servicegroupEdit, ['class' => 'form-horizontal']);
$form->setTagID($form->getTagName());
if (!is_null($serviceGroup->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($serviceGroup->getmyKeyColumn(),
        $serviceGroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));

$oPage->addItem(new UI\PageBottom());

$infopanel = new UI\InfoBox($serviceGroup);
$tools     = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
if ($serviceGroup->getId()) {
    $tools->addItem($serviceGroup->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning',
        $serviceGroup->transferForm()));
}
$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6,
    new \Ease\TWB\Panel(_('Příkaz').' <strong>'.$serviceGroup->getName().'</strong>',
    'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);




$oPage->draw();
