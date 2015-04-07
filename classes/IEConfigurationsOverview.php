<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEConfigurationsOvervier
 *
 * @author vitex
 */
class IEConfigurationsOverview extends EaseTWBPanel
{

    public function __construct($hosts)
    {
        $ok = 0;
        $hosts_table = new EaseHtmlTableTag(null, array('class' => 'table'));
        foreach ($hosts as $host_id => $host_info) {
            if (is_null($host_info['host_name']) || !strlen($host_info['host_name'])) {
                unset($hosts[$host_id]);
                continue;
            }

            if (isset($host_info['config_hash'])) {
                $host = new IEHost((int) $host_id);
                if ($host->getConfigHash() == $host_info['config_hash']) {
                    unset($hosts[$host_id]);
                    $ok++;
                    continue;
                } else {
                    //Zastaralá konfigurace
                    $hosts_table->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('sensor.php?host_id=' . $host_id, _('aktualizovat senzor'))), array('class' => 'warning'));
                }
            } else {
                //senzor neregistrován
                $hosts_table->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('sensor.php?host_id=' . $host_id, _('nasadit senzor'))), array('class' => 'danger'));
            }
        }
        parent::__construct(_('Hosty s neaktuální konfigurací'), 'info', $hosts_table, sprintf(_('Celkem %s hostů bez aktuální konfigurace. (%s aktuální)'), count($hosts), $ok));
    }

}
