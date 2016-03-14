<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - skupina hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$hostgroup = new IEHostgroup($oPage->getRequestValue('hostgroup_id', 'int'));


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
        if ($oPage->isPosted()) {
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
        }
        $delete = $oPage->getGetValue('delete', 'bool');
        if ($delete == 'true') {
            $hostgroup->delete();
            $oPage->redirect('hostgroups.php');
            exit();
        }

        break;
}


$oPage->addItem(new UI\PageTop(_('Editace skupiny hostů') . ' ' . $hostgroup->getName()));






$hostgroupEdit = new IECfgEditor($hostgroup);

$form = new \Ease\TWB\Form('Hostgroup', 'hostgroup.php', 'POST', $hostgroupEdit, array('class' => 'form-horizontal'));
$form->setTagID($form->getTagName());
if (!is_null($hostgroup->getMyKey())) {
    $form->addItem(new \Ease\Html\InputHiddenTag($hostgroup->getmyKeyColumn(), $hostgroup->getMyKey()));
}
$form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));

$oPage->addItem(new UI\PageBottom());



$infopanel = new IEInfoBox($hostgroup);
$tools = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
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


$operations = $tools->addItem(new \Ease\TWB\Panel(_('Hromadné operace')), 'success');
$operations->addItem(new IEContactAsignForm);

$tools->addItem(new \Ease\TWB\LinkButton('wizard-host.php?hostgroup_id=' . $hostgroup->getId(), \Ease\TWB\Part::GlyphIcon('plus') . _('nový host ve skupině'), 'success'));

//$tools->addItem(new \Ease\TWB\LinkButton('hglayouteditor.php?hostgroup_id=' . $hostgroup->getId(), \Ease\TWB\Part::GlyphIcon('globe') . _('Rozvržení topologie'), 'info'));


$oPage->draw();
