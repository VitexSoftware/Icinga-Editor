<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Contacgroup
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$contactgroup = new Engine\Contactgroup($oPage->getRequestValue('contactgroup_id',
        'int'));

$oPage->addItem(new UI\PageTop(_('Contactgroup').' '.$contactgroup->getName()));

if ($oPage->isPosted()) {
    $contactgroup->takeData($_POST);
    $ContactgroupID = $contactgroup->saveToSQL();
    if (is_null($ContactgroupID)) {
        $oUser->addStatusMessage(_('Contactgroup was not saved'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Contactgroup saved'), 'success');
    }
}

$contactgroup->saveMembers();

$delete = $oPage->getGetValue('delete', 'string');
if ($delete == 'true') {
    $contactgroup->delete();
    $oPage->redirect('contactgroups.php');
}

$contactgroupEdit = new UI\CfgEditor($contactgroup);

$form = new \Ease\TWB\Form('Contactgroup', 'contactgroup.php', 'POST',
    $contactgroupEdit, ['class' => 'form-horizontal']);
$form->setTagID($form->getTagName());
if (!is_null($contactgroup->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($contactgroup->getKeyColumn(),
        $contactgroup->getMyKey()));
}
$form->addItem('<br>');
$form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));

$oPage->addItem(new UI\PageBottom());

$infopanel = new UI\InfoBox($contactgroup);
$tools     = new \Ease\TWB\Panel(_('Tools'), 'warning');
if ($contactgroup->getId()) {
    $tools->addItem($contactgroup->deleteButton());
}
$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6,
    new \Ease\TWB\Panel(_('Contactgroup').' <strong>'.$contactgroup->getName().'</strong>',
    'default', $form));
$pageRow->addColumn(4, $tools);

$oPage->container->addItem($pageRow);

$oPage->draw();
