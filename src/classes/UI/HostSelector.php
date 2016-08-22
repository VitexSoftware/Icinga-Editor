<?php

namespace Icinga\Editor\UI;

/**
 * Choose hosts checked by an service
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
class HostSelector extends \Ease\Container
{
    public $myKeyColumn = 'service_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEServices $service
     */
    public function __construct($service)
    {
        $hostsAssigned  = [];
        parent::__construct();
        $fieldName      = $this->myKeyColumn;
        $initialContent = new \Ease\TWB\Panel(_('Hosts checked for service'),
            'default');
        $initialContent->setTagCss(['width' => '100%']);

        if (is_null($service->getMyKey())) {
            $initialContent->addItem(_('Please save record first'));
        } else {
            $serviceName = $service->getName();
            $host        = new \Icinga\Editor\Engine\Host();

            if (\Ease\Shared::user()->getSettingValue('admin')) {
                $allHosts = $host->getAllFromSQL(NULL,
                    [$host->myKeyColumn, $host->nameColumn, 'platform', 'register'],
                    null, $host->nameColumn, $host->myKeyColumn);
            } else {
                $allHosts = $host->getListing(null, true,
                    ['platform', 'register']);
            }
            if ($service->getDataValue('host_name')) {
                foreach ($service->getDataValue('host_name') as $hostId => $hostName) {
                    if (isset($allHosts[$hostId])) {
                        $hostsAssigned[$hostId] = $allHosts[$hostId];
                    }
                }
            }
            foreach ($allHosts as $hostID => $hostInfo) {
                if ($hostInfo['register'] != 1) {
                    unset($allHosts[$hostID]);
                }

                if (($hostInfo['platform'] != 'generic') && $hostInfo['platform']
                    != $service->getDataValue('platform')) {
                    unset($allHosts[$hostID]);
                }
            }

            foreach ($hostsAssigned as $hostID => $hostInfo) {
                unset($allHosts[$hostID]);
            }

            if (count($allHosts)) {

                foreach ($allHosts as $hostID => $hostInfo) {
                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $hostInfo[$host->nameColumn], 'inverse', 'xs',
                        [
                        new \Ease\Html\ATag('host.php?host_id='.$hostID.'&amp;service_id='.$service->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Edit')),
                        new \Ease\Html\ATag('?addhost='.$hostInfo[$host->nameColumn].'&amp;host_id='.$hostID.'&amp;'.$service->getmyKeyColumn().'='.$service->getMyKey().'&amp;'.$service->nameColumn.'='.$service->getName(),
                            \Ease\TWB\Part::GlyphIcon('plus').' '._('Start checking'))
                    ]));
                }
            }

            if (count($hostsAssigned)) {
                $initialContent->addItem('<br/>');
                foreach ($hostsAssigned as $hostID => $hostInfo) {

                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $hostInfo[$host->nameColumn], 'success', 'xs',
                        [
                        new \Ease\Html\ATag(
                            '?delhost='.$hostInfo[$host->nameColumn].'&amp;host_id='.$hostID.'&amp;'.$service->getmyKeyColumn().'='.$service->getMyKey().'&amp;'.$service->nameColumn.'='.$service->getName(),
                            \Ease\TWB\Part::GlyphIcon('remove').' '._('Přestat sledovat'))
                        , new \Ease\Html\ATag('host.php?host_id='.$hostID.'&amp;service_id='.$service->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace'))
                        ]
                        )
                    );
                }
            }
        }
        $this->addItem($initialContent);
    }

    /**
     * Save items
     *
     * @param array $request
     */
    public static function saveMembers($request)
    {
        $host = new \Icinga\Editor\Engine\Host();
        if (isset($request[$host->myKeyColumn])) {
            if ($host->loadFromSQL($request[$host->myKeyColumn])) {
                if (isset($request['addhost']) || isset($request['delhost'])) {
                    if (isset($request['addhost'])) {
                        $host->addMember('service_name', $request['service_id'],
                            $request['service_name']);
                        if ($host->saveToSQL()) {
                            $host->addStatusMessage(sprintf(_('Item %s was added'),
                                    $request['addhost']), 'success');
                        } else {
                            $host->addStatusMessage(sprintf(_('item %s was not added'),
                                    $request['addhost']), 'warning');
                        }
                    }
                    if (isset($request['delhost'])) {
                        $host->delMember('service_name', $request['service_id'],
                            $request['service_name']);
                        if ($host->saveToSQL()) {
                            $host->addStatusMessage(sprintf(_('item %s was removed'),
                                    $request['delhost']), 'success');
                        } else {
                            $host->addStatusMessage(sprintf(_('item %s was not removed'),
                                    $request['delhost']), 'warning');
                        }
                    }
                }
            }
        }
    }

}
