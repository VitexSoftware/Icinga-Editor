<?php

/**
 * Správce hostů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEService.php';
require_once 'IEPortScanner.php';
require_once 'IEHostgroup.php';

/**
 * Description of IEHosts
 *
 * @author vitex
 */
class IEHost extends IECfg
{

    public $myTable = 'hosts';
    public $Keyword = 'host';
    public $NameColumn = 'host_name';
    public $MyKeyColumn = 'host_id';

    /**
     * Weblink
     * @var string 
     */
    public $WebLinkColumn = 'action_url';

    /**
     * Přidat položky register a use ?
     * @var boolean 
     */
    public $AllowTemplating = true;

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean 
     */
    public $PublicRecords = true;
    public $UseKeywords = array(
        'host_name' => 'VARCHAR(255)',
        'alias' => 'VARCHAR(64)',
        'display_name' => 'VARCHAR(64)',
        'address' => 'VARCHAR(64)',
        'address6' => 'VARCHAR(128)',
        'parents' => 'IDLIST',
        'hostgroups' => 'IDLIST',
        'check_command' => 'SELECT',
        'initial_state' => "RADIO('o','d','u')",
        'max_check_attempts' => 'SLIDER',
        'check_interval' => 'SLIDER',
        'retry_interval' => 'SLIDER',
        'active_checks_enabled' => 'BOOL',
        'passive_checks_enabled' => 'BOOL',
        'check_period' => 'SELECT',
        'obsess_over_host' => 'BOOL',
        'check_freshness' => 'BOOL',
        'freshness_threshold' => 'INT',
        'event_handler' => 'SELECT',
        'event_handler_enabled' => 'BOOL',
        'low_flap_threshold' => 'INT',
        'high_flap_threshold' => 'INT',
        'flap_detection_enabled' => 'BOOL',
        'flap_detection_options' => "FLAGS('o','d','u')",
        'failure_prediction_enabled' => 'BOOL',
        'process_perf_data' => 'BOOL',
        'retain_status_information' => 'BOOL',
        'retain_nonstatus_information' => 'BOOL',
        'contacts' => 'IDLIST',
        'contact_groups' => 'IDLIST',
        'notification_interval' => 'SLIDER',
        'first_notification_delay' => 'SLIDER',
        'notification_period' => 'SELECT',
        'notification_options' => "FLAGS('d','u','r','f','s')",
        'notifications_enabled' => 'BOOL',
        'stalking_options' => "FLAGS('o','d','u')",
        'notes' => 'TEXT',
        'notes_url' => 'VARCHAR(128)',
        'action_url' => 'VARCHAR(128)',
        'icon_image' => 'VARCHAR(64)',
        'icon_image_alt' => 'VARCHAR(64)',
        'vrml_image' => 'VARCHAR(64)',
        'statusmap_image' => 'VARCHAR(64)',
        '2d_coords' => 'VARCHAR(32)',
        '3d_coords' => 'VARCHAR(64)');
    public $KeywordsInfo = array(
        'host_name' => array('title' => 'Jméno hosta', 'required' => true),
        'alias' => array('title' => 'alias hosta', 'required' => true),
        'display_name' => array('title' => 'zobrazované jméno'),
        'address' => array('title' => 'IPv4 adresa ', 'mandatory' => true),
        'address6' => array('title' => 'IPv6 adresa', 'mandatory' => true),
        'parents' => array(
            'title' => 'rodiče',
            'refdata' => array(
                'table' => 'hosts',
                'captioncolumn' => 'host_name',
                'idcolumn' => 'host_id',
                'public' => true,
                'condition' => array('register' => 1))),
        'hostgroups' => array('title' => 'skupiny hostů',
            'refdata' => array(
                'table' => 'hostgroup',
                'captioncolumn' => 'hostgroup_name',
                'idcolumn' => 'hostgroup_id')
        ),
        'check_command' => array('title' => 'testovací příkaz',
            'refdata' => array(
                'table' => 'command',
                'captioncolumn' => 'command_name',
                'idcolumn' => 'command_id',
                'public' => true,
                'condition' => array('command_type' => 'check')
            )
        ),
        'initial_state' => array('title' => 'výchozí předpokládaný stav',
            'o' => 'UP - spuštěn',
            'd' => 'DOWN - vypnut',
            'u' => 'UNREACHABLE - nedostupný',
        ),
        'max_check_attempts' => array('title' => 'maximální počet pokusů'),
        'check_interval' => array('title' => 'interval otestování'),
        'retry_interval' => array('title' => 'interval dalšího pokusu o test'),
        'active_checks_enabled' => array('title' => 'povolit aktivní testy'),
        'passive_checks_enabled' => array('title' => 'povolit pasivní testy'),
        'check_period' => array(
            'title' => 'testovací perioda',
            'refdata' => array(
                'table' => 'timeperiods',
                'captioncolumn' => 'timeperiod_name',
                'public' => true,
                'idcolumn' => 'timeperiod_id')
        ),
        'obsess_over_host' => array('title' => 'Posedlost přes host'),
        'check_freshness' => array('title' => 'testovat čerstvost'),
        'freshness_threshold' => array('title' => 'práh čertvosti'),
        'event_handler' => array('title' => 'ošetřovač událostí',
            'refdata' => array(
                'table' => 'command',
                'captioncolumn' => 'command_name',
                'idcolumn' => 'command_id',
                'public' => true,
                'condition' => array('command_type' => 'handler')
            )
        ),
        'event_handler_enabled' => array('title' => 'povolit ošetření událostí'),
        'low_flap_threshold' => array('title' => 'nižší práh plácání'),
        'high_flap_threshold' => array('title' => 'vyšší práh plácání'),
        'flap_detection_enabled' => array('title' => 'detekovat plácání'),
        'flap_detection_options' => array(
            'title' => 'možnosti detekce plácání',
            'o' => 'Up',
            'd' => 'Down',
            'u' => 'Nedostupný',
        ),
        'failure_prediction_enabled' => array('title' => 'Předpokládat výpadek'),
        'process_perf_data' => array('title' => 'zpracovávat výkonostní data'),
        'retain_status_information' => array('title' => 'držet stavové informace'),
        'retain_nonstatus_information' => array('title' => 'držet nestavové informace'),
        'contacts' => array(
            'title' => 'kontakty',
            'refdata' => array(
                'table' => 'contact',
                'captioncolumn' => 'contact_name',
                'idcolumn' => 'contact_id')),
        'contact_groups' => array(
            'title' => 'členské skupiny kontaktů',
            'refdata' => array(
                'table' => 'contactgroup',
                'captioncolumn' => 'contactgroup_name',
                'idcolumn' => 'contactgroup_id')
        ),
        'notification_interval' => array('title' => 'interval notifikace'),
        'first_notification_delay' => array('title' => 'první prodleva v oznamování'),
        'notification_period' => array(
            'title' => 'perioda oznamování',
            'required' => true,
            'refdata' => array(
                'table' => 'timeperiods',
                'captioncolumn' => 'timeperiod_name',
                'public' => true,
                'idcolumn' => 'timeperiod_id')
        ),
        'notification_options' => array(
            'title' => 'oznamovat událost',
            'd' => 'Vypnutí',
            'u' => 'Nedostupnost',
            'r' => 'Obnovení',
            'f' => 'škytání',
            's' => 'plánovaný výpadek'
        ),
        'notifications_enabled' => array('title' => 'povolit oznamování'),
        'stalking_options' => array('title' => 'nastavení sledování',
            'o' => 'sledovat UP stavy',
            'd' => 'sledovat DOWN stavy',
            'u' => 'sledovat UNREACHABLE stavy'),
        'notes' => array('title' => 'poznámka', 'mandatory' => true),
        'notes_url' => array('title' => 'url externí poznámky'),
        'action_url' => array('title' => 'url externí aplikace'),
        'icon_image' => array('title' => 'ikona hostu', 'mandatory' => true),
        'icon_image_alt' => array('title' => 'alternativní ikona'),
        'vrml_image' => array('title' => '3D ikona'),
        'statusmap_image' => array('title' => 'ikona statusmapy'),
        '2d_coords' => array('title' => 'dvourozměrné koordináty'),
        '3d_coords' => array('title' => 'třírozměrné koordináty'));

    /**
     * URL dokumentace objektu
     * @var string 
     */
    public $DocumentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-host';

    /**
     * Vrací mazací tlačítko
     * 
     * @param string $Name
     * @return \EaseJQConfirmedLinkButton 
     */
    function deleteButton($Name = null)
    {
        return parent::deleteButton(_('Hosta'));
    }

    /**
     * Smaže záznam
     */
    function delete()
    {
        $HostGroup = new IEHostgroup();
        $HostGroup->deleteHost($this->getName());
        
        $DelAll = true;
        $Service = new IEService();
        $ServicesAssigned = $Service->myDbLink->queryToArray('SELECT ' . $Service->MyKeyColumn . ',' . $Service->NameColumn . ' FROM ' . $Service->myTable . ' WHERE ' . 'host_name' . ' LIKE \'%"' . $this->getName() . '"%\'', $Service->MyKeyColumn);
        foreach ($ServicesAssigned as $ServiceID => $ServiceInfo) {
            $Service->loadFromMySQL($ServiceID);
            $Service->delHostName($this->getId(), $this->getName());
            if (!$Service->saveToMySQL()) {
                $this->addStatusMessage(sprintf(_('Nepodařilo se odregistrovat %s ze služby %s'), $this->getName(), $Service->getName()), $Type);
                $DelAll = false;
            }
        }
        if ($DelAll) {
            return parent::delete();
        }
        return false;
    }

    /**
     * Zkontroluje všechny položky
     * 
     * @param array $AllData
     * @return array
     */
    function controlAllData($AllData)
    {
        foreach ($AllData as $ADkey => $AD) {
            if ($AllData[$ADkey]['max_check_attempts'] == 0) {
                unset($AllData[$ADkey]['max_check_attempts']);
            }
        }
        return parent::controlAllData($AllData);
    }

    /**
     * Začne sledovat právě běžící TCP služby
     * @return int počet sledovaných
     */
    public function autoPopulateServices()
    {
        $Scanner = new IEPortScanner($this);
        return $Scanner->assignServices(); 
    }

    /**
     * Přejmenuje hosta a závistlosti
     * @param type $newname
     */
    public function rename($newname){
        $oldname = $this->getName();
        $this->setDataValue($this->NameColumn, $newname);
    
        $hostGroup = new IEHostgroup();
        $hostGroup->renameHost($oldname,$newname);
        
        $renameAll = true;
        $service = new IEService();
        $ServicesAssigned = $service->myDbLink->queryToArray('SELECT ' . $service->MyKeyColumn . ',' . $service->NameColumn . ' FROM ' . $service->myTable . ' WHERE ' . 'host_name' . ' LIKE \'%"' . $oldname . '"%\'', $service->MyKeyColumn);
        foreach ($ServicesAssigned as $ServiceID => $ServiceInfo) {
            $service->loadFromMySQL($ServiceID);
            $service->renameHostName($this->getId(), $newname);
            if (!$service->saveToMySQL()) {
                $this->addStatusMessage(sprintf(_('Nepodařilo se přejmenovat %s ve službě %s'), $this->getName(), $service->getName()), $Type);
                $renameAll = false;
            }
        }
        if ($this->save() && $renameAll) {
            return true;
        }
        return false;
    }
    
}

?>
