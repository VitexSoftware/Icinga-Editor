<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$contact = new Engine\IEContact($oPage->getRequestValue('contact_id', 'int'));

if ($oPage->isPosted()) {
    $contact->takeData($_POST);
    $contactID = $contact->saveToSQL();
    if (is_null($contactID)) {
        $oUser->addStatusMessage(_('Kontakt nebyl uložen'), 'warning');
    } else {
        $oUser->addStatusMessage(_('Kontakt byl uložen'), 'success');
    }
}

$contact->saveMembers();

$delete = $oPage->getGetValue('delete', 'bool');
if ($delete == 'true') {
    $contact->delete();
    $oPage->redirect('contacts.php');
    exit();
}

$oPage->addItem(new UI\PageTop(_('Editace kontaktu').' '.$contact->getName()));

switch ($oPage->getRequestValue('action')) {
    case 'delete':
        $form        = new \Ease\Container;
        $form->addItem(new \Ease\Html\H2Tag($contact->getName()));
        $confirmator = $form->addItem(new \Ease\TWB\Panel(_('Opravdu smazat ?')),
            'danger');
        $confirmator->addItem(new \Ease\TWB\LinkButton('?'.$contact->myKeyColumn.'='.$contact->getID(),
            _('Ne').' '.\Ease\TWB\Part::glyphIcon('ok'), 'success'));
        $confirmator->addItem(new \Ease\TWB\LinkButton('?delete=true&'.$contact->myKeyColumn.'='.$contact->getID(),
            _('Ano').' '.\Ease\TWB\Part::glyphIcon('remove'), 'danger'));
        break;
    default :


        $contactEdit = new Engine\IEcfgEditor($contact);

        $form = new \Ease\Html\Form('Contact', 'contact.php', 'POST',
            $contactEdit, ['class' => 'form-horizontal']);
        $form->setTagID($form->getTagName());
        if (!is_null($contact->getMyKey())) {
            $form->addItem(new \Ease\Html\InputHiddenTag($contact->getmyKeyColumn(),
                $contact->getMyKey()));
        }
        $form->addItem('<br>');
        $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));
        break;
}



$service       = new Engine\IEService;
$serviceUsages = $service->getColumnsFromSQL([$service->getMyKeyColumn(), $service->nameColumn],
    ['contacts' => '%'.$contact->getName().'%'], $service->nameColumn,
    $service->getMyKeyColumn());



$oPage->addItem(new UI\PageBottom());

$infopanel = new Engine\IEInfoBox($contact);
$tools     = new \Ease\TWB\Panel(_('Nástroje'), 'warning');
if ($contact->getId()) {
    $tools->addItem($contact->deleteButton());
    $tools->addItem(new \Ease\TWB\Panel(_('Transfer'), 'warning',
        $contact->transferForm()));



    if (count($serviceUsages)) {
        $usedBy  = new \Ease\TWB\Panel(_('Používaný službami'));
        $listing = $usedBy->addItem(new \Ease\Html\UlTag(null,
            ['class' => 'list-group']));
        foreach ($serviceUsages as $usage) {
            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('service.php?service_id='.$usage['service_id'],
                $usage[$service->nameColumn])
                , ['class' => 'list-group-item'])
            );
        }
        $infopanel->addItem($usedBy);
    }

    $host       = new Engine\IEHost;
    $hostUsages = $host->getColumnsFromSQL([$host->getMyKeyColumn(), $host->nameColumn],
        ['contacts' => '%'.$contact->getName().'%'], $host->nameColumn,
        $host->getMyKeyColumn());

    if (count($hostUsages)) {
        $usedBy  = new \Ease\TWB\Panel(_('Používaný hosty'));
        $listing = $usedBy->addItem(new \Ease\Html\UlTag(null,
            ['class' => 'list-group']));
        foreach ($hostUsages as $usage) {
            $listing->addItem(
                new \Ease\Html\LiTag(
                new \Ease\Html\ATag('host.php?host_id='.$usage['host_id'],
                $usage[$host->nameColumn])
                , ['class' => 'list-group-item'])
            );
        }
        $infopanel->addItem($usedBy);
    }
}

$pageRow = new \Ease\TWB\Row;
$pageRow->addColumn(2, $infopanel);
$pageRow->addColumn(6,
    new \Ease\TWB\Panel(_('Příkaz').' <strong>'.$contact->getName().'</strong>',
    'default', $form));
$pageRow->addColumn(4, $tools);
$oPage->container->addItem($pageRow);



$oPage->draw();
