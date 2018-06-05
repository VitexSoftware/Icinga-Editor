<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - Watch route
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

$oPage->addItem(new UI\PageTop(_('Add all hosts on route to watched')));

$hostId = $oPage->getRequestValue('host_id', 'int');

/**
 * host route target form
 * 
 * @param Engine\Host $host
 * @return \Ease\TWB\Panel
 */
function endRouteForm($host)
{
    $form = new \Ease\TWB\Form('traceto');
    $form->addInput(new UI\HostSelect('dest_host_id'), _('Gateway'), null,
        _('Choose allready defined gateway or enter an address.'));
    $form->addInput(new \Ease\Html\InputTextTag('ip'), _('IP Address'), null,
        _('First pingable pubic IP address on route to monitoring server'));
    $form->addItem(new \Ease\TWB\SubmitButton(_('Watch route'), 'success',
            ['onClick' => "$('#preload').css('visibility', 'visible');"]));
    \Ease\Shared::webPage()->addItem(new \Ease\Html\DivTag(
            new UI\FXPreloader(), ['class' => 'fuelux', 'id' => 'preload']));
    return new \Ease\TWB\Panel(_('Watch target route select').': '.$host->getName(),
        'default', $form, _('Choose host or enter an IP address'));
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

    $hgName    = sprintf(_('Route to %s'), $host->getName());
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
                $host->addStatusMessage(sprintf(_('New host %s %s created'),
                        $hop, $newHostName), 'success');
                $parents[$hop] = ['host_id' => $newHostId, 'address' => $hop, $host->nameColumn => $newHostName];
            }
        }
        if ($pos) {
            $parentIP = $trace[$pos - 1];
            $host->addMember('parents', $parents[$parentIP][$host->keyColumn],
                $parents[$parentIP][$host->nameColumn]);
        }
        $host->addMember('contacts', $defaultContactId, $defaultContactName);
        $host->setDataValue('config_hash', $host->getConfigHash());
        $host->setDataValue('check_command', 'check-host-alive');
        $host->setDataValue('active_checks_enabled', 1);
        $host->setDataValue('passive_checks_enabled', 0);
        $oldNotes = strval($host->getDataValue('notes'));
        if (strlen($oldNotes) && strstr($hostGroup->getName(), $oldNotes) == false) {
            $host->setDataValue('notes', $oldNotes."\n".$hostGroup->getName());
        }
        $host->saveToSQL();
        $hostGroup->addMember('members', $host->getId(), $host->getName());

        $listing->addItemSmart(new \Ease\Html\ATag('host.php?host_id='.$host->getId(),
                $host->getName()));
    }

    if ($hostGroup->saveToSQL()) {
        $hostGroup->addStatusMessage(sprintf(_('Hostgroup %s filled'),
                $hostGroup->getName()), 'success');
    } else {
        $hostGroup->addStatusMessage(sprintf(_('Hostgroup %s was not filled'),
                $hostGroup->getName()), 'warning');
    }
}


$oPage->addItem(new UI\PageBottom());

$oPage->draw();
