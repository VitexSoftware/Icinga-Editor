<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - hostgroup
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$hostgroup = new Engine\Hostgroup($oPage->getRequestValue('hostgroup_id', 'int'));


switch ($oPage->getRequestValue('action')) {
    case 'contactAsign':
        $contact = new Engine\Contact($oPage->getRequestValue('contact_id',
                'int'));
        if ($contact->getId()) {
            $host         = new Engine\Host;
            $groupMembers = $hostgroup->getMembers();
            foreach ($groupMembers as $gmID => $hostName) {
                $host->loadFromSQL((int) $gmID);
                $host->addMember('contacts', $contact->getId(),
                    $contact->getName());
                if ($host->saveToSQL()) {
                    $host->addStatusMessage(sprintf(_('<strong>%s</strong> was add to contacts <strong>%s</strong>'),
                            $contact->getName(), $host->getName()), 'success');
                } else {
                    $host->addStatusMessage(sprintf(_('<strong>%s</strong> was not add to contacts <strong>%s</strong>'),
                            $contact->getName(), $host->getName()), 'warning');
                }
            }
        } else {
            $hostgroup->addStatusMessage(_('Contact assigning error'), 'warning');
        }
        break;
    default :
        if ($oPage->isPosted()) {
            $hostgroup->takeData($_POST);

            if (!$hostgroup->getId()) {
                $hostgroup->setDataValue('members', []);
            }

            $hostgroupID = $hostgroup->saveToSQL();
            if (is_null($hostgroupID)) {
                $oUser->addStatusMessage(_('Hostgroup was not saved'), 'warning');
            } else {
                $oUser->addStatusMessage(_('Hostgroup was saved'), 'success');
            }
            $hostgroup->saveMembers();
        }
        $delete = $oPage->getGetValue('delete', 'string');
        if ($delete == 'true') {
            $hostgroup->delete();
            $oPage->redirect('hostgroups.php');
            exit();
        }

        break;
}


$oPage->addItem(new UI\PageTop(_('Hostgroup Editor').' '.$hostgroup->getName()));






$hostgroupEdit = new UI\CfgEditor($hostgroup);

$form = new \Ease\TWB\Form('Hostgroup', 'hostgroup.php', 'POST', $hostgroupEdit,
    ['class' => 'form-horizontal']);
$form->setTagID($form->getTagName());
if (!is_null($hostgroup->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($hostgroup->getmyKeyColumn(),
        $hostgroup->getMyKey()));
}
$form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));

$oPage->addItem(new UI\PageBottom());



$infopanel = new UI\InfoBox($hostgroup);
$tools     = new \Ease\TWB\Panel(_('Tools'), 'warning');
if ($hostgroup->getId()) {
    $tools->addItem($hostgroup->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning',
        $hostgroup->transferForm()));
}
$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6,
    new \Ease\TWB\Panel(new \Ease\Html\H1Tag($hostgroup->getDataValue('alias').' <small>'.$hostgroup->getName().'</small>')
    , 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);


$operations = $tools->addItem(new \Ease\TWB\Panel(_('Bulk operations')),
    'success');
$operations->addItem(new UI\ContactAsignForm());

$tools->addItem(new \Ease\TWB\LinkButton('wizard-host.php?hostgroup_id='.$hostgroup->getId(),
    \Ease\TWB\Part::GlyphIcon('plus')._('New Host in Group'), 'success'));

//$tools->addItem(new \Ease\TWB\LinkButton('hglayouteditor.php?hostgroup_id=' . $hostgroup->getId(), \Ease\TWB\Part::GlyphIcon('globe') . _('Rozvržení topologie'), 'info'));


$oPage->draw();
