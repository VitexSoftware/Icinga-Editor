<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - hostgroup
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$hostgroup = new Engine\Hostgroup($oPage->getRequestValue('hostgroup_id', 'int'));


switch ($oPage->getRequestValue('action')) {
    case 'applystemplate':
        $stemplate = new Stemplate($oPage->getRequestValue('stemplate_id', 'int'));
        $services = $stemplate->getDataValue('services');
        if (count($services)) {
            if ($oPage->getRequestValue('groupHosts') == 1) { //Assign service to hostgroup
                $service = new Engine\Service();
                foreach ($services as $service_id => $service_name) {
                    if ($service->loadFromSQL($service_id)) {
                        $service->addMember('hostgroup_name', $hostGroup->getId(), $hostGroup->getName());
                        $service->saveToSQL();
                        $service->dataReset();
                    } else {
                        $service->addStatusMessage(_(sprintf(_('Service used in template (#%s: %s) not found'), $service_id, $service_name)));
                    }
                }
            } else { //Several Hosts
                foreach ($hostGroup->getHosts() as $hostID => $hostName) {
                    $host = new Engine\Host($hostID);
                    $service = new Engine\Service();
                    foreach ($services as $service_id => $service_name) {
                        if ($service->loadFromSQL($service_id)) {
                            $service->addMember('host_name', $host->getId(), $host->getName());
                            $service->saveToSQL();
                            $service->dataReset();
                        } else {
                            $service->addStatusMessage(_(sprintf(_('Service used in template (#%s: %s) not found'), $service_id, $service_name)));
                        }
                    }
                }
            }
        }
        break;

    case 'contactAsign':
        $contact = new Engine\Contact($oPage->getRequestValue('contact_id', 'int'));
        if ($contact->getId()) {
            $host = new Engine\Host;
            $groupMembers = $hostgroup->getMembers();
            foreach ($groupMembers as $gmID => $hostName) {
                $host->loadFromSQL((int) $gmID);
                $host->addMember('contacts', $contact->getId(), $contact->getName());
                if ($host->saveToSQL()) {
                    $host->addStatusMessage(sprintf(_('%s was add to contacts %s'),
                            '<strong>'.$contact->getName().'</strong>',
                            '<strong>'.$host->getName().'</strong>'), 'success');
                } else {
                    $host->addStatusMessage(sprintf(_('%s was not add to contacts %s'),
                            '<strong>'.$contact->getName().'</strong>',
                            '<strong>'.$host->getName().'</strong>'), 'warning');
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


$oPage->addItem(new UI\PageTop(_('Hostgroup Editor') . ' ' . $hostgroup->getName()));






$hostgroupEdit = new UI\CfgEditor($hostgroup);

$form = new \Ease\TWB\Form('Hostgroup', 'hostgroup.php', 'POST', $hostgroupEdit, ['class' => 'form-horizontal']);
$form->setTagID($form->getTagName());
if (!is_null($hostgroup->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($hostgroup->getKeyColumn(), $hostgroup->getMyKey()));
}
$form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));

$oPage->addItem(new UI\PageBottom());



$infopanel = new UI\InfoBox($hostgroup);
$tools = new \Ease\TWB\Panel(_('Tools'), 'warning');
if ($hostgroup->getId()) {
    $tools->addItem($hostgroup->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning', $hostgroup->transferForm()));
}
$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6, new \Ease\TWB\Panel(new \Ease\Html\H1Tag($hostgroup->getDataValue('alias') . ' <small>' . $hostgroup->getName() . '</small>')
    , 'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);


$operations = $tools->addItem(new \Ease\TWB\Panel(_('Bulk operations')), 'success');
$operations->addItem(new UI\ContactAsignForm());


$presetSelForm = new UI\ServicePresetSelectForm();
$presetSelForm->addItem(new \Ease\Html\InputHiddenTag($hostgroup->getKeyColumn(), $hostgroup->getId()));

$presetSelForm->addInput(new UI\TWBSwitch('groupHosts', false, true, ['onText' => _('hostgroup'), 'offText' => _('hosts')]), _('Apply to'), null, _('Apply preset to several Hosts or hostgroup itself'));

$presetSelForm->setTagClass('form-inline');
$operations->addItem($presetSelForm);

$tools->addItem(new \Ease\TWB\LinkButton('wizard-host.php?hostgroup_id=' . $hostgroup->getId(), \Ease\TWB\Part::GlyphIcon('plus') . _('New Host in Group'), 'success'));

//$tools->addItem(new \Ease\TWB\LinkButton('hglayouteditor.php?hostgroup_id=' . $hostgroup->getId(), \Ease\TWB\Part::GlyphIcon('globe') . _('Rozvržení topologie'), 'info'));


$oPage->draw();
