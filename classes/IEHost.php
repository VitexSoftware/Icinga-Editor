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
require_once 'IEIconSelector.php';

/**
 * Description of IEHosts
 *
 * @author vitex
 */
class IEHost extends IECfg
{

    public $myTable = 'hosts';
    public $keyword = 'host';
    public $nameColumn = 'host_name';
    public $myKeyColumn = 'host_id';

    /**
     * Weblink
     * @var string
     */
    public $webLinkColumn = 'action_url';

    /**
     * Přidat položky register a use ?
     * @var boolean
     */
    public $allowTemplating = true;

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * Sloupce záznamu
     * @var array
     */
    public $useKeywords = array(
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
      '3d_coords' => 'VARCHAR(64)',
      'platform' => "PLATFORM"
    );
    public $keywordsInfo = array(
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
      '3d_coords' => array('title' => 'třírozměrné koordináty'),
      'platform' => array('title' => 'Platforma', 'mandatory' => true)
    );

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-host';

    /**
     * Vrací mazací tlačítko
     *
     * @param  string                     $name
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('Hosta'), $addUrl);
    }

    /**
     * Smaže záznam
     */
    public function delete($id = null)
    {
        if (!is_null($id)) {
            $this->loadFromMySQL($id);
        }

        $hostGroup = new IEHostgroup();
        $hostGroup->deleteHost($this->getName());

        $delAll = true;
        $service = new IEService();
        $servicesAssigned = $service->myDbLink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ' FROM ' . $service->myTable . ' WHERE ' . 'host_name' . ' LIKE \'%"' . $this->getName() . '"%\'', $service->myKeyColumn);
        foreach ($servicesAssigned as $ServiceID => $ServiceInfo) {
            $service->loadFromMySQL($ServiceID);
            $service->delHostName($this->getId(), $this->getName());
            if (!$service->saveToMySQL()) {
                $this->addStatusMessage(sprintf(_('Nepodařilo se odregistrovat %s ze služby %s'), $this->getName(), $service->getName()), 'Error');
                $delAll = false;
            }
        }
        if ($delAll) {
            return parent::delete();
        }

        return false;
    }

    /**
     * Zkontroluje všechny položky
     *
     * @param  array $allData
     * @return array
     */
    public function controlAllData($allData)
    {
        foreach ($allData as $aDkey => $aD) {
            if ($allData[$aDkey]['max_check_attempts'] == 0) {
                unset($allData[$aDkey]['max_check_attempts']);
            }
        }

        return parent::controlAllData($allData);
    }

    /**
     * Vrací všechna data
     *
     * @return array Data hostu k uložení do konfiguráků
     */
    public function getAllData()
    {
        $allData = parent::getAllData();
        foreach ($allData as $hostID => $hostInfo) {
            if (!intval($hostInfo['register'])) {
                continue;
            }
            if (intval($hostInfo['user_id'])) {
                if (is_object($this->owner)) {
                    if ($this->owner->getUserID() != $hostInfo['user_id']) {
                        $this->owner->loadFromMySQL((int) $hostInfo['user_id']);
                    }
                } else {
                    $this->owner = new IEUser((int) $hostInfo['user_id']);
                }

                $hostOwnerLogin = $this->owner->getUserLogin();
                /* Každý host musí mít jak kontakt login uživatele který ho má vidět */
                if (is_array($hostInfo['contacts'])) {
                    if (array_search($hostOwnerLogin, $hostInfo['contacts']) === false) {
                        $allData[$hostID]['contacts'][] = $hostOwnerLogin;
                    }
                } else {
                    $allData[$hostID]['contacts'] = array($hostOwnerLogin);
                }
            } else {
                $this->addStatusMessage(_('Host bez vlastníka') . ': #' . $hostInfo[$this->myKeyColumn] . ': ' . $hostInfo[$this->nameColumn], 'warning');
            }
        }

        return $allData;
    }

    /**
     * Začne sledovat právě běžící TCP služby
     * @return int počet sledovaných
     */
    public function autoPopulateServices()
    {
        $scanner = new IEPortScanner($this);

        return $scanner->assignServices();
    }

    /**
     * Přejmenuje hosta a závistlosti
     * @param type $newname
     */
    public function rename($newname)
    {
        $oldname = $this->getName();
        $this->setDataValue($this->nameColumn, $newname);

        $hostGroup = new IEHostgroup();
        $hostGroup->renameHost($oldname, $newname);

        $renameAll = true;
        $service = new IEService();
        $servicesAssigned = $service->myDbLink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ' FROM ' . $service->myTable . ' WHERE ' . 'host_name' . ' LIKE \'%"' . $oldname . '"%\'', $service->myKeyColumn);
        foreach ($servicesAssigned as $ServiceID => $ServiceInfo) {
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

    /**
     * Zjistí ikonu, stahne jí z netu, zkonvertuje a použije jako ikonu hosta
     */
    public function favToIcon()
    {
        $icoUrl = false;
        $baseUrl = 'http://' . $this->getDataValue('host_name') . '/';
        $indexpage = @file_get_contents($baseUrl);
        $icoUrls = array();
        if (strlen($indexpage)) {
            $dom = new DOMDocument();
            @$dom->loadHTML($indexpage);
            $links = $dom->getElementsByTagName('link');
            foreach ($links as $link) {
                $urlLink = false;
                if (isset($link->attributes)) {
                    foreach ($link->attributes as $atribut) {
                        if (isset($atribut->name)) {
                            if (($atribut->name == 'rel') && stristr($atribut->value, 'icon')) {
                                $urlLink = true;
                                $rel = $atribut->value;
                            }
                            if (($atribut->name == 'href')) {
                                $url = $atribut->value;
                            }
                        }
                    }
                    if ($urlLink) {
                        if (strstr($url, '://')) {
                            $icoUrls[$rel] = $url;
                        } else {
                            $icoUrls[$rel] = $baseUrl . $url;
                        }
                    }
                }
            }
        }

        if (!count($icoUrls)) {
            $icoUrls[] = $baseUrl . '/favicon.ico';
        } else {
            if (count($icoUrls) == 1) {
                $icoUrl = current($icoUrls);
            } else {
                foreach ($icoUrls as $ico) {
                    if (strstr($ico, '.png')) {
                        $icoUrl = $ico;
                    }
                }

                if (!$icoUrl) {
                    foreach ($icoUrls as $ico) {
                        if (strstr($ico, '.gif')) {
                            $icoUrl = $ico;
                        }
                    }
                }

                if (!$icoUrl) {
                    foreach ($icoUrls as $ico) {
                        if (strstr($ico, '.jpg')) {
                            $icoUrl = $ico;
                        }
                    }
                }

                if (!$icoUrl) {
                    $icoUrl = current($icoUrls);
                }
            }
        }



        $tmpfilename = sys_get_temp_dir() . '/' . EaseSand::randomString();


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $icoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $downloaded = curl_exec($ch);
        curl_close($ch);

        file_put_contents($tmpfilename, $downloaded);



        if (IEIconSelector::imageTypeOK($tmpfilename)) {

            EaseShared::webPage()->addStatusMessage(sprintf(_('Nalezena ikona %s'), $icoUrl), 'success');

            $newicon = IEIconSelector::saveIcon($tmpfilename, $this);

            if ($newicon) {
                $this->setDataValue('icon_image', $newicon);
                $this->setDataValue('statusmap_image', $newicon);
                return true;
            }
        } else {
            unlink($tmpfilename);
        }

        return false;
    }

    function draw()
    {
        echo IEHostOverview::icon($this);
    }

}
