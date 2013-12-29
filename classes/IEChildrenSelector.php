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

            $ServicesAssigned = $Service->myDbLink->queryToArray('SELECT ' . $Service->myKeyColumn . ',' . $Service->nameColumn . ' FROM ' . $Service->myTable . ' WHERE ' . $FieldName . ' LIKE \'%"' . $Host->getName() . '"%\'', $Service->myKeyColumn);

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
                    $Jellybean = new EaseHtmlSpanTag($ServiceInfo[$Service->nameColumn], null, array('class' => 'jellybean gray'));
                    $Jellybean->addItem(new EaseHtmlATag('?addservice=' . $ServiceInfo[$Service->nameColumn] . '&amp;service_id=' . $ServiceID . '&amp;' . $Host->getmyKeyColumn() . '=' . $Host->getMyKey() . '&amp;' . $Host->nameColumn . '=' . $Host->getName(), $ServiceInfo[$Service->nameColumn]));
                    $InitialContent->addItem($Jellybean);
                }
            }


            if (count($ServicesAssigned)) {
                $InitialContent->addItem('</br>');
                foreach ($ServicesAssigned as $ServiceID => $ServiceInfo) {
                    $Jellybean = new EaseHtmlSpanTag($ServiceInfo[$Service->nameColumn], null, array('class' => 'jellybean'));
                    $Jellybean->addItem($ServiceInfo[$Service->nameColumn]);
                    $Jellybean->addItem(new EaseHtmlATag('?delservice=' . $ServiceInfo[$Service->nameColumn] . '&amp;service_id=' . $ServiceID . '&amp;' . $Host->getmyKeyColumn() . '=' . $Host->getMyKey() . '&amp;' . $Host->nameColumn . '=' . $Host->getName(), EaseTWBPart::GlyphIcon('remove')));
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
        if (isset($Request[$Service->myKeyColumn])) {
            if ($Service->loadFromMySQL($Request[$Service->myKeyColumn])) {
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
