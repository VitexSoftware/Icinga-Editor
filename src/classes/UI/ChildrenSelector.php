<?php

namespace Icinga\Editor\UI;

/**
 * Volba služeb patřičných k hostu
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class ChildrenSelector extends \Ease\Container
{

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEHosts $Host
     */
    public function __construct($Host)
    {
        $FieldName      = 'parents';
        $InitialContent = new \Ease\TWB\Panel(_('Children'));
        $InitialContent->setTagCss(['width' => '100%']);

        if (is_null($Host->getMyKey())) {
            $InitialContent->addItem(_('Please save record firs'));
        } else {

            $Service = new \Icinga\Editor\Engine\Service();

            $servicesAssigned = $Service->dblink->queryToArray('SELECT '.$Service->myKeyColumn.','.$Service->nameColumn.' FROM '.$Service->myTable.' WHERE '.$FieldName.' LIKE \'%"'.$Host->getName().'"%\'',
                $Service->myKeyColumn);

            $allServices = $Service->getListing();
            foreach ($allServices as $ServiceID => $serviceInfo) {
                if ($serviceInfo['register'] != 1) {
                    unset($allServices[$ServiceID]);
                }
            }

            foreach ($servicesAssigned as $ServiceID => $serviceInfo) {
                unset($allServices[$ServiceID]);
            }

            if (count($allServices)) {

                foreach ($allServices as $ServiceID => $serviceInfo) {
                    $Jellybean = new \Ease\Html\Span(
                        null,
                        ['class' => 'jellybean gray', 'id' => $serviceInfo[$Service->nameColumn]]);
                    $Jellybean->addItem(new \Ease\Html\ATag('?addservice='.$serviceInfo[$Service->nameColumn].'&amp;service_id='.$ServiceID.'&amp;'.$Host->getmyKeyColumn().'='.$Host->getMyKey().'&amp;'.$Host->nameColumn.'='.$Host->getName(),
                        $serviceInfo[$Service->nameColumn]));
                    $InitialContent->addItem($Jellybean);
                }
            }

            if (count($servicesAssigned)) {
                $InitialContent->addItem('</br>');
                foreach ($servicesAssigned as $ServiceID => $serviceInfo) {
                    $Jellybean = new \Ease\Html\Span(
                        null,
                        ['class' => 'jellybean', 'id' => $serviceInfo[$Service->nameColumn]]);
                    $Jellybean->addItem($serviceInfo[$Service->nameColumn]);
                    $Jellybean->addItem(new \Ease\Html\ATag('?delservice='.$serviceInfo[$Service->nameColumn].'&amp;service_id='.$ServiceID.'&amp;'.$Host->getmyKeyColumn().'='.$Host->getMyKey().'&amp;'.$Host->nameColumn.'='.$Host->getName(),
                        \Ease\TWB\Part::GlyphIcon('remove')));
                    $InitialContent->addItem($Jellybean);
                }
            }
        }
        parent::__construct($InitialContent);
    }

    /**
     * Uloží položky
     *
     * @param array $request
     */
    public static function saveMembers($request)
    {
        $service = new \Icinga\Editor\Engine\Service();
        if (isset($request[$service->myKeyColumn])) {
            if ($service->loadFromSQL($request[$service->myKeyColumn])) {
                if (isset($request['addservice']) || isset($request['delservice'])) {
                    if (isset($request['addservice'])) {
                        $service->addHostName($request['host_id'],
                            $request['host_name']);
                        if ($service->saveToSQL()) {
                            $service->addStatusMessage(sprintf(_('The item %s was added'),
                                    $request['addservice']), 'success');
                        } else {
                            $service->addStatusMessage(sprintf(_('The item %s was not added'),
                                    $request['addservice']), 'warning');
                        }
                    }
                    if (isset($request['delservice'])) {
                        $service->delHostName($request['host_id'],
                            $request['host_name']);
                        if ($service->saveToSQL()) {
                            $service->addStatusMessage(sprintf(_('The item %s has been removed'),
                                    $request['delservice']), 'success');
                        } else {
                            $service->addStatusMessage(sprintf(_('The item %s not removed'),
                                    $request['delservice']), 'warning');
                        }
                    }
                }
            }
        }
    }

}
