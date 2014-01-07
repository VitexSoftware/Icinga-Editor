<?php

/**
 * Volba služeb patřičných k hostu
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEServiceSelector extends EaseContainer
{

    public $myKeyColumn = 'host_name';

    /**
     * Editor k přidávání členů skupiny
     * 
     * @param IEHosts $host
     */
    function __construct($host)
    {
        parent::__construct();
        $fieldName = $this->getmyKeyColumn();
        $initialContent = new EaseHtmlFieldSet(_('Sledované služby'));
        $initialContent->setTagCss(array('width' => '100%'));


        if (is_null($host->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {

            $service = new IEService();

            $servicesAssigned = $service->myDbLink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ' FROM ' . $service->myTable . ' WHERE ' . $fieldName . ' LIKE \'%"' . $host->getName() . '"%\'', $service->myKeyColumn);

            $allServices = $service->getListing(null, true, array('platform'));
            foreach ($allServices as $serviceID => $serviceInfo) {
                if ($serviceInfo['register'] != 1) {
                    unset($allServices[$serviceID]);
                }
                
                if( ($serviceInfo['platform']!='generic') && $serviceInfo['platform'] != $host->getDataValue('platform') ) {
                    unset($allServices[$serviceID]);
                }
                
                
            }

            foreach ($servicesAssigned as $serviceID => $serviceInfo) {
                unset($allServices[$serviceID]);
            }

            if (count($allServices)) {

                foreach ($allServices as $serviceID => $serviceInfo) {
                    $Jellybean = new EaseHtmlSpanTag($serviceInfo[$service->nameColumn], null, array('class' => 'jellybean gray'));
                    $Jellybean->addItem(new EaseHtmlATag('?addservice=' . $serviceInfo[$service->nameColumn] . '&amp;service_id=' . $serviceID . '&amp;' . $host->getmyKeyColumn() . '=' . $host->getMyKey() . '&amp;' . $host->nameColumn . '=' . $host->getName(), $serviceInfo[$service->nameColumn]));
                    $initialContent->addItem($Jellybean);
                }
            }


            if (count($servicesAssigned)) {
                $initialContent->addItem('</br>');
                foreach ($servicesAssigned as $serviceID => $serviceInfo) {
                    $Jellybean = new EaseHtmlSpanTag($serviceInfo[$service->nameColumn], null, array('class' => 'jellybean'));
                    $Jellybean->addItem($serviceInfo[$service->nameColumn]);
                    $Jellybean->addItem(new EaseHtmlATag('?delservice=' . $serviceInfo[$service->nameColumn] . '&amp;service_id=' . $serviceID . '&amp;' . $host->getmyKeyColumn() . '=' . $host->getMyKey() . '&amp;' . $host->nameColumn . '=' . $host->getName(), EaseTWBPart::GlyphIcon('remove')));
                    $initialContent->addItem($Jellybean);
                }
            }
        }
        $this->addItem($initialContent);
    }

    /**
     * Uloží položky
     * 
     * @param array $Request 
     */
    static function saveMembers($Request)
    {
        $Service = new IEService();
        if (isset($Request[$Service->myKeyColumn])) {
            if ($Service->loadFromMySQL($Request[$Service->myKeyColumn])) {
                if (isset($Request['addservice']) || isset($Request['delservice'])) {
                    if (isset($Request['addservice'])) {
                        $Service->addMember('host_name', $Request['host_id'], $Request['host_name']);
                        if ($Service->saveToMySQL()) {
                            $Service->addStatusMessage(sprintf(_('položka %s byla přidána'), $Request['addservice']), 'success');
                        } else {
                            $Service->addStatusMessage(sprintf(_('položka %s nebyla přidána'), $Request['addservice']), 'warning');
                        }
                    }
                    if (isset($Request['delservice'])) {
                        $Service->delMember('host_name', $Request['host_id'], $Request['host_name']);
                        if ($Service->saveToMySQL()) {
                            $Service->addStatusMessage(sprintf(_('položka %s byla odebrána'), $Request['delservice']), 'success');
                        } else {
                            $Service->addStatusMessage(sprintf(_('položka %s nebyla odebrána'), $Request['delservice']), 'warning');
                        }
                    }
                }
            }
        }
    }

}

?>
