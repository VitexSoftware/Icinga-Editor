<?php

/**
 * Přehled hostů bez potvrzeného senzoru, nebo se zastaralou konf. senzoru.
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEConfigurationsOverview extends EaseTWBPanel
{

    /**
     * Přehled hostů bez potvrzeného senzoru, nebo se zastaralou konf. senzoru.
     *
     * @param array $hosts
     */
    public function __construct($hosts)
    {
        $ok = 0;

        $noSensor = array();
        $oldSensor = array();

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
                    $oldSensor[$host_id] = $host_info;
                }
            } else {
                $noSensor[$host_id] = $host_info;
                //senzor neregistrován
            }
        }

        $hostsTabs = new EaseTWBTabs('hostsTabs');
        if (count($oldSensor)) {
            $oldHostsTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($oldSensor as $host_id => $host_info) {
                $row = $oldHostsTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('sensor.php?host_id=' . $host_id, _('aktualizovat senzor'))));
                $row->setTagClass('warning');
            }
            $hostsTabs->addTab(sprintf(_('Neaktuální <span class="badge">%s</span>'), count($oldSensor)), $oldHostsTable);
        }

        if (count($noSensor)) {
            $noHostsTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($hosts as $host_id => $host_info) {
                $row = $noHostsTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('sensor.php?host_id=' . $host_id, _('nasadit senzor'))));
                $row->setTagClass('danger');
            }
            $hostsTabs->addTab(sprintf(_('Bez senzoru <span class="badge">%s</span>'), count($noSensor)), $noHostsTable);
        }

        parent::__construct(_('Hosty dle stavu konfigurace'), 'info', $hostsTabs, sprintf(_('Celkem %s hostů bez aktuální konfigurace. (%s aktuální)'), count($hosts), $ok));
    }

}
