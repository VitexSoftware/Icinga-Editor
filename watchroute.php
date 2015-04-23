<?php

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

$oPage->addItem(new IEPageTop(_('Vygeneruje sledování cesty k hostu')));

$hostId = $oPage->getRequestValue('host_id', 'int');

/**
 * Formulář cíle cesty pro hosta
 * @param IEHost $host
 * @return \EaseTWBPanel
 */
function endRouteForm($host)
{
    $form = new EaseTWBForm('traceto');
    $form->addInput(new IEHostSelect('dest_host_id'), _('Gateway'), null, _('Zvolte již definovanou gateway, nabo zadejte konkrétní adresu.'));
    $form->addInput(new EaseHtmlInputTextTag('ip'), _('IP Adresa'), null, _('První pingnutelná veřejá adresa po cestě z hostu na monitorovací server'));
    $form->addItem(new EaseTWSubmitButton(_('Sledovat cestu'), 'success', array('onClick' => "$('#preload').css('visibility', 'visible');")));
    EaseShared::webPage()->addItem(new EaseHtmlDivTag('preload', new IEFXPreloader(), array('class' => 'fuelux')));
    return new EaseTWBPanel(_('Volba cíle sledování') . ': ' . $host->getName(), 'default', $form, _('Vyberte hosta nebo zadejte IP adresu'));
}

$host = new IEHost($hostId);
$ip = $host->getDataValue('address');
if (!$ip) {
    $ip = $oPage->getRequestValue('ip');
    if (!$ip) {
        $destHost = new IEHost($oPage->getRequestValue('dest_host_id', 'int'));
        $ip = $destHost->getDataValue('address');
    }
    $forceIP = true;
} else {
    $forceIP = false;
}

if (is_null($hostId) || !$ip) {
    $oPage->container->addItem(endRouteForm($host));
} else {

    $defaultContactId = $oUser->getDefaultContact()->getId();
    $defaultContactName = $oUser->getDefaultContact()->getName();

    $hgName = sprintf(_('Cesta k %s'), $host->getName());
    $hostGroup = new IEHostgroup($hgName);
    if ($hostGroup->getId()) {

    } else {
        $hostGroup->setUpUser($oUser);
        $hostGroup->setDataValue($hostGroup->nameColumn, $hgName);
    }

    $listing = new EaseHtmlOlTag();

    $infopanel = $oPage->container->addItem(new EaseTWBPanel($hostGroup->getName(), 'info', $listing));


    $trace = array();


//??? $mtr = shell_exec('mtr -4 --no-dns -c 1 -p   ' . $ip);
    $mtr = shell_exec('traceroute -n -w 1 ' . $ip);
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

    $parents = array();
    $newHost = FALSE;

    foreach ($trace as $pos => $hop) {
        $host->dataReset();

        if ($hop == end($trace)) {
            $host->loadFromMySQL($hostId);
        } else {
            $host->nameColumn = 'address';
            $host->loadFromMySQL($hop);
            $host->nameColumn = 'host_name';
        }

        if ($host->getId()) {
            //Ok Známe
            $newHost = FALSE;
            $parents[$hop] = $host->getData();
        } else {
            //Nový host
            $newHost = true;
            $host->setUpUser($oUser);
            $newHostName = gethostbyaddr($hop);
            if (!$newHostName) {
                $newHostName = $hop;
            }
            $host->setDataValue('use', 'generic-host');
            $host->setDataValue('generate', true);
            $host->setDataValue('address', $hop);
            $host->setDataValue($host->nameColumn, $newHostName);
            $newHostId = (int) $host->insertToMySQL();
            if ($newHostId) {
                $host->addStatusMessage(sprintf(_('Nový host %s %s založen'), $hop, $newHostName), 'success');
                $parents[$hop] = array('host_id' => $newHostId, 'address' => $hop, $host->nameColumn => $newHostName);
            }
        }
        if ($pos) {
            $parentIP = $trace[$pos - 1];
            $host->addMember('parents', $parents[$parentIP][$host->myKeyColumn], $parents[$parentIP][$host->nameColumn]);
        }
        $host->addMember('contacts', $defaultContactId, $defaultContactName);
        $host->setDataValue('config_hash', $host->getConfigHash());
        $host->setDataValue('check_command', 'check-host-alive');
        $host->setDataValue('active_checks_enabled', 1);
        $host->setDataValue('passive_checks_enabled', 0);
        $oldNotes = $host->getDataValue('notes');
        if (!$oldNotes || !strstr($hostGroup->getName(), $oldNotes)) {
            $host->setDataValue('notes', $oldNotes . "\n" . $hostGroup->getName());
        }
        $host->saveToMySQL();
        $hostGroup->addMember('members', $host->getId(), $host->getName());

        $listing->addItemSmart(new EaseHtmlATag('host.php?host_id=' . $host->getId(), $host->getName()));
    }

    if ($hostGroup->saveToMySQL()) {
        $hostGroup->addStatusMessage(sprintf(_('Hostgrupa %s naplněna'), $hostGroup->getName()), 'success');
    } else {
        $hostGroup->addStatusMessage(sprintf(_('Hostgrupa %s nebyla naplněna'), $hostGroup->getName()), 'warning');
    }
}


$oPage->addItem(new IEPageBottom());

$oPage->draw();
