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
        $noParents = array();
        $noIcon = array();
        $noContacts = array();

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

            if (!isset($host_info['parents']) || !count($host_info['parents'])) {
                $noParents[$host_id] = $host_info;
                //Host bez rodičů
            }

            if (!isset($host_info['icon_image']) || !strlen(trim($host_info['icon_image']))) {
                $noIcon[$host_id] = $host_info;
                //Host bez ikony
            }

            if ((!isset($host_info['contacts']) || !count($host_info['contacts'])) || (!isset($host_info['contact_groups']) || !count($host_info['contact_groups']))) {
                $noContacts[$host_id] = $host_info;
                //Host bez kontaktů
            }
        }

        $hostsTabs = new EaseTWBTabs('hostsTabs');
        if (count($oldSensor)) {
            $oldHostsTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($oldSensor as $host_id => $host_info) {
                $row = $oldHostsTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('sensor.php?host_id=' . $host_id, _('aktualizovat senzor'))));
                $row->setTagClass('warning');
            }
            $hostsTabs->addTab(sprintf(_('Neakt. Senzor <span class="badge">%s</span>'), count($oldSensor)), $oldHostsTable);
        }

        if (count($noSensor)) {
            $noSensorTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($noSensor as $host_id => $host_info) {
                $row = $noSensorTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('sensor.php?host_id=' . $host_id, _('nasadit senzor'))));
                $row->setTagClass('danger');
            }
            $hostsTabs->addTab(sprintf(_('Bez senzoru <span class="badge">%s</span>'), count($noSensor)), $noSensorTable);
        }

        if (count($noParents)) {
            $noParentsTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($noParents as $host_id => $host_info) {
                $row = $noParentsTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('host.php?action=parent&host_id=' . $host_id, _('přiřadit rodiče'))));
                $row->setTagClass('info');
            }
            $hostsTabs->addTab(sprintf(_('Bez rodičů <span class="badge">%s</span>'), count($noParents)), $noParentsTable);
        }

        if (count($noIcon)) {
            $noIconTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($noIcon as $host_id => $host_info) {
                $row = $noIconTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('host.php?action=icon&host_id=' . $host_id, _('přiřadit ikonu'))));
                $row->setTagClass('default');
            }
            $hostsTabs->addTab(sprintf(_('Bez ikony <span class="badge">%s</span>'), count($noIcon)), $noIconTable);
        }

        if (count($noContacts)) {
            $noContactsTable = new EaseHtmlTableTag(null, array('class' => 'table'));
            foreach ($noContacts as $host_id => $host_info) {
                $row = $noContactsTable->addRowColumns(array(new EaseHtmlATag('host.php?host_id=' . $host_id, $host_info['host_name']), new EaseTWBLinkButton('host.php?host_id=' . $host_id, _('přiřadit kontakty'))));
                $row->setTagClass('default');
            }
            $hostsTabs->addTab(sprintf(_('Bez kontaktů <span class="badge">%s</span>'), count($noContacts)), $noContactsTable);
        }

        parent::__construct(_('Hosty dle stavu konfigurace'), 'info', $hostsTabs, sprintf(_('Celkem %s hostů bez aktuální konfigurace. (%s aktuální)'), count($hosts), $ok));
    }

}
