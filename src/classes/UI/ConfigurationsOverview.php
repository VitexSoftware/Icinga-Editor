<?php

namespace Icinga\Editor\UI;

/**
 * Hosts without confirmerd configuration or sensor deployed overview
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
class ConfigurationsOverview extends \Ease\TWB\Panel
{

    /**
     * Hosts without confirmerd configuration or sensor deployed overview
     *
     * @param array $hosts
     */
    public function __construct($hosts)
    {
        $ok = 0;

        $noSensor   = [];
        $oldSensor  = [];
        $noParents  = [];
        $noIcon     = [];
        $noContacts = [];

        \Ease\Shared::webPage()->addItem(new \Ease\Html\Div(new FXPreloader(),
            ['class' => 'fuelux', 'id' => 'preload']));

        foreach ($hosts as $host_id => $host_info) {
            if (is_null($host_info['host_name']) || !strlen($host_info['host_name'])) {
                unset($hosts[$host_id]);
                continue;
            }

            if (isset($host_info['config_hash'])) {
                $host = new \Icinga\Editor\Engine\Host((int) $host_id);
                if ($host->getConfigHash() == $host_info['config_hash']) {
                    unset($hosts[$host_id]);
                    $ok++;
                } else {
                    //Obsoleted configuration
                    $oldSensor[$host_id] = $host_info;
                }
            } else {
                $noSensor[$host_id] = $host_info;
                //sensor undeployed yet
            }

            if (!isset($host_info['parents']) || !count($host_info['parents'])) {
                $noParents[$host_id] = $host_info;
                //Host without parents
            }

            if (!isset($host_info['icon_image']) || !strlen(trim($host_info['icon_image']))) {
                $noIcon[$host_id] = $host_info;
                //Host without icon
            }

            if (!count($host_info['contacts']) + count($host_info['contact_groups'])) {
                $noContacts[$host_id] = $host_info;
                //Host without contact
            }
        }

        $hostsTabs = new \Ease\TWB\Tabs('hostsTabs');
        if (count($oldSensor)) {
            $oldHostsTable = new \Ease\Html\TableTag(null, ['class' => 'table']);
            foreach ($oldSensor as $host_id => $host_info) {
                $row = $oldHostsTable->addRowColumns([new \Ease\Html\ATag('host.php?host_id='.$host_id,
                        $host_info['host_name']), new \Ease\TWB\LinkButton('sensor.php?host_id='.$host_id,
                        _('Actualise Sensor'))]);
                $row->setTagClass('warning');
            }
            $hostsTabs->addTab(sprintf(_('Outdated Sensor <span class="badge">%s</span>'),
                    count($oldSensor)), $oldHostsTable);
        }

        if (count($noSensor)) {
            $noSensorTable = new \Ease\Html\TableTag(null, ['class' => 'table']);
            foreach ($noSensor as $host_id => $host_info) {
                $row = $noSensorTable->addRowColumns([new \Ease\Html\ATag('host.php?host_id='.$host_id,
                        $host_info['host_name']), new \Ease\TWB\LinkButton('sensor.php?host_id='.$host_id,
                        _('Sensor Deploy'))]);
                $row->setTagClass('danger');
            }
            $hostsTabs->addTab(sprintf(_('Without sensor <span class="badge">%s</span>'),
                    count($noSensor)), $noSensorTable);
        }

        if (count($noParents)) {
            $noParentsTable = new \Ease\Html\TableTag(null, ['class' => 'table']);
            foreach ($noParents as $host_id => $host_info) {
                $row = $noParentsTable->addRowColumns(
                    [
                        new \Ease\Html\ATag('host.php?host_id='.$host_id,
                            $host_info['host_name']),
                        new \Ease\TWB\LinkButton('host.php?action=parent&host_id='.$host_id,
                            _('Assign parents')),
                        new \Ease\TWB\LinkButton('watchroute.php?action=parent&host_id='.$host_id,
                            _('Watch hosts on route'), 'warning',
                            ['onClick' => "$('#preload').css('visibility', 'visible');"])
                    ]
                );


                $row->setTagClass('info');
            }
            $hostsTabs->addTab(sprintf(_('Without parents <span class="badge">%s</span>'),
                    count($noParents)), $noParentsTable);
        }

        if (count($noIcon)) {
            $noIconTable = new \Ease\Html\TableTag(null, ['class' => 'table']);
            foreach ($noIcon as $host_id => $host_info) {
                $row = $noIconTable->addRowColumns([new \Ease\Html\ATag('host.php?host_id='.$host_id,
                        $host_info['host_name']), new \Ease\TWB\LinkButton('host.php?action=icon&host_id='.$host_id,
                        _('Assign icon'))]);
                $row->setTagClass('default');
            }
            $hostsTabs->addTab(sprintf(_('Without icon <span class="badge">%s</span>'),
                    count($noIcon)), $noIconTable);
        }

        if (count($noContacts)) {
            $noContactsTable = new \Ease\Html\TableTag(null,
                ['class' => 'table']);
            foreach ($noContacts as $host_id => $host_info) {
                $row = $noContactsTable->addRowColumns([new \Ease\Html\ATag('host.php?host_id='.$host_id,
                        $host_info['host_name']), new \Ease\TWB\LinkButton('host.php?host_id='.$host_id,
                        _('Assign Contact'))]);
                $row->setTagClass('default');
            }
            $hostsTabs->addTab(sprintf(_('Without contacts <span class="badge">%s</span>'),
                    count($noContacts)), $noContactsTable);
        }

        parent::__construct(_('Hosts by configuration state'), 'info',
            $hostsTabs,
            sprintf(_('Overall %s hosts without actual configuration. (%s actual)'),
                count($hosts), $ok));
    }

}
