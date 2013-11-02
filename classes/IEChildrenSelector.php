<?php

/**
 * Volba služeb patřičných k hostu
 * 
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEChildrenSelector extends EaseContainer
{

    /**
     * Editor k přidávání členů skupiny
     * 
     * @param IEHosts $Host
     */
    function __construct($Host)
    {
        $FieldName = 'parents';
        $InitialContent = new EaseHtmlFieldSet(_('Sledované služby'));
        $InitialContent->setTagCss(array('width' => '100%'));


        if (is_null($Host->getMyKey())) {
            $InitialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {

            $Service = new IEService();

            $ServicesAssigned = $Service->MyDbLink->queryToArray('SELECT ' . $Service->MyKeyColumn . ',' . $Service->NameColumn . ' FROM ' . $Service->MyTable . ' WHERE ' . $FieldName . ' LIKE \'%"' . $Host->getName() . '"%\'', $Service->MyKeyColumn);

            $AllServices = $Service->getListing();
            foreach ($AllServices as $ServiceID => $ServiceInfo) {
                if($ServiceInfo['register']!=1){
                    unset($AllServices[$ServiceID]);
                }
            }

            foreach ($ServicesAssigned as $ServiceID => $ServiceInfo) {
                unset($AllServices[$ServiceID]);
            }

            if (count($AllServices)) {

                foreach ($AllServices as $ServiceID => $ServiceInfo) {
                    $Jellybean = new EaseHtmlSpanTag($ServiceInfo[$Service->NameColumn], null, array('class' => 'jellybean gray'));
                    $Jellybean->addItem(new EaseHtmlATag('?addservice=' . $ServiceInfo[$Service->NameColumn] . '&amp;service_id=' . $ServiceID . '&amp;' . $Host->getMyKeyColumn() . '=' . $Host->getMyKey() . '&amp;' . $Host->NameColumn . '=' . $Host->getName(), $ServiceInfo[$Service->NameColumn]));
                    $InitialContent->addItem($Jellybean);
                }
            }


            if (count($ServicesAssigned)) {
                $InitialContent->addItem('</br>');
                foreach ($ServicesAssigned as $ServiceID => $ServiceInfo) {
                    $Jellybean = new EaseHtmlSpanTag($ServiceInfo[$Service->NameColumn], null, array('class' => 'jellybean'));
                    $Jellybean->addItem($ServiceInfo[$Service->NameColumn]);
                    $Jellybean->addItem(new EaseHtmlATag('?delservice=' . $ServiceInfo[$Service->NameColumn] . '&amp;service_id=' . $ServiceID . '&amp;' . $Host->getMyKeyColumn() . '=' . $Host->getMyKey() . '&amp;' . $Host->NameColumn . '=' . $Host->getName(), '<i class="icon-remove"></i>'));
                    $InitialContent->addItem($Jellybean);
                }
            }
        }
        parent::__construct($InitialContent);
    }

    /**
     * Uloží položky
     * 
     * @param array $Request 
     */
    static function saveMembers($Request)
    {
        $Service = new IEService();
        if (isset($Request[$Service->MyKeyColumn])) {
            if ($Service->loadFromMySQL($Request[$Service->MyKeyColumn])) {
                if (isset($Request['addservice']) || isset($Request['delservice'])) {
                    if (isset($Request['addservice'])) {
                        $Service->addHostName($Request['host_id'], $Request['host_name']);
                        if ($Service->saveToMySQL()) {
                            $Service->addStatusMessage(sprintf(_('položka %s byla přidána'),$Request['addservice']), 'success');
                        } else {
                            $Service->addStatusMessage(sprintf(_('položka %s nebyla přidána'),$Request['addservice']), 'warning');
                        }
                    }
                    if (isset($Request['delservice'])) {
                        $Service->delHostName($Request['host_id'], $Request['host_name']);
                        if ($Service->saveToMySQL()) {
                            $Service->addStatusMessage(sprintf(_('položka %s byla odebrána'),$Request['delservice']), 'success');
                        } else {
                            $Service->addStatusMessage(sprintf(_('položka %s nebyla odebrána'),$Request['delservice']), 'warning');
                        }
                    }
                }
            }
        }
    }

}

?>
