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

$oPage->addItem(new UI\PageTop(_('Vygeneruje sledování cesty k hostu')));

$hostId = $oPage->getRequestValue('host_id', 'int');

/**
 * Formulář cíle cesty pro hosta
 * @param IEHost $host
 * @return \\Ease\TWB\Panel
 */
function endRouteForm($host)
{
    $form = new \Ease\TWB\Form('traceto');
    $form->addInput(new IEHostSelect('dest_host_id'), _('Gateway'), null,
        _('Zvolte již definovanou gateway, nabo zadejte konkrétní adresu.'));
    $form->addInput(new \Ease\Html\InputTextTag('ip'), _('IP Adresa'), null,
        _('První pingnutelná veřejá adresa po cestě z hostu na monitorovací server'));
    $form->addItem(new \Ease\TWB\SubmitButton(_('Sledovat cestu'), 'success',
        ['onClick' => "$('#preload').css('visibility', 'visible');"]));
    \Ease\Shared::webPage()->addItem(new \Ease\Html\DivTag('preload',
        new UI\FXPreloader(), ['class' => 'fuelux']));
    return new \Ease\TWB\Panel(_('Volba cíle sledování').': '.$host->getName(),
        'default', $form, _('Vyberte hosta nebo zadejte IP adresu'));
}
$host = new Engine\Host($hostId);
$ip   = $host->getDataValue('address');
if (!$ip) {
    $ip = $oPage->getRequestValue('ip');
    if (!$ip) {
        $destHost = new Engine\Host($oPage->getRequestValue('dest_host_id',
                'int'));
        $ip       = $destHost->getDataValue('address');
    }
    $forceIP = true;
} else {
    $forceIP = false;
}

if (is_null($hostId) || !$ip) {
    $oPage->container->addItem(endRouteForm($host));
} else {

    $defaultContactId   = $oUser->getDefaultContact()->getId();
    $defaultContactName = $oUser->getDefaultContact()->getName();

    $hgName    = sprintf(_('Cesta k %s'), $host->getName());
    $hostGroup = new Engine\Hostgroup($hgName);
    if ($hostGroup->getId()) {

    } else {
        $hostGroup->setUpUser($oUser);
        $hostGroup->setName($hgName);
    }

    $listing = new \Ease\Html\OlTag();

    $infopanel = $oPage->container->addItem(new \Ease\TWB\Panel($hostGroup->getName(),
        'info', $listing));


    $trace = [];


//??? $mtr = shell_exec('mtr -4 --no-dns -c 1 -p   ' . $ip);
    $mtr      = shell_exec('traceroute -n -w 1 '.$ip);
    $mtrlines = explode("\n", $mtr);
    foreach ($mtrlines as $mtrline) {
        $linea = explode(' ', trim($mtrline));
        if (($linea[0] == 'traceroute') || !isset($linea[2])) {
            continue;
        }
        if ($linea[2] != '*') {
            $trace[] = $linea[2];
        }
    }
    $trace[] = $ip;

    $parents = [];
    $newHost = FALSE;

    foreach ($trace as $pos => $hop) {
        $host->dataReset();

        if ($hop == end($trace)) {
            $host->loadFromSQL($hostId);
        } else {
            $host->nameColumn = 'address';
            $host->loadFromSQL($hop);
            $host->nameColumn = 'host_name';
        }

        if ($host->getId()) {
            //Ok Známe
            $newHost       = FALSE;
            $parents[$hop] = $host->getData();
        } else {
            //Nový host
            $newHost     = true;
            $host->setUpUser($oUser);
            $newHostName = gethostbyaddr($hop);
            if (!$newHostName) {
                $newHostName = $hop;
            }
            $host->setDataValue('use', 'generic-host');
            $host->setDataValue('generate', true);
            $host->setDataValue('address', $hop);
            $host->setDataValue($host->nameColumn, $newHostName);
            $newHostId = (int) $host->insertToSQL();
            if ($newHostId) {
                $host->addStatusMessage(sprintf(_('Nový host %s %s založen'),
                        $hop, $newHostName), 'success');
                $parents[$hop] = ['host_id' => $newHostId, 'address' => $hop, $host->nameColumn => $newHostName];
            }
        }
        if ($pos) {
            $parentIP = $trace[$pos - 1];
            $host->addMember('parents', $parents[$parentIP][$host->myKeyColumn],
                $parents[$parentIP][$host->nameColumn]);
        }
        $host->addMember('contacts', $defaultContactId, $defaultContactName);
        $host->setDataValue('config_hash', $host->getConfigHash());
        $host->setDataValue('check_command', 'check-host-alive');
        $host->setDataValue('active_checks_enabled', 1);
        $host->setDataValue('passive_checks_enabled', 0);
        $oldNotes = strval($host->getDataValue('notes'));
        if (strstr($hostGroup->getName(), $oldNotes) == false) {
            $host->setDataValue('notes', $oldNotes."\n".$hostGroup->getName());
        }
        $host->saveToSQL();
        $hostGroup->addMember('members', $host->getId(), $host->getName());

        $listing->addItemSmart(new \Ease\Html\ATag('host.php?host_id='.$host->getId(),
            $host->getName()));
    }

    if ($hostGroup->saveToSQL()) {
        $hostGroup->addStatusMessage(sprintf(_('Hostgrupa %s naplněna'),
                $hostGroup->getName()), 'success');
    } else {
        $hostGroup->addStatusMessage(sprintf(_('Hostgrupa %s nebyla naplněna'),
                $hostGroup->getName()), 'warning');
    }
}


$oPage->addItem(new UI\PageBottom());

$oPage->draw();
