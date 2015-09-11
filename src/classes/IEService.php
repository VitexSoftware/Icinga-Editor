<?php

/**
 * Konfigurace Služeb
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

/**
 * Služby
 */
class IEService extends IECfg
{

    public $myTable = 'service';
    public $myKeyColumn = 'service_id';
    public $keyword = 'service';
    public $nameColumn = 'service_description';

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
    public $useKeywords = array(
      'display_name' => 'VARCHAR(64)',
      'service_description' => 'VARCHAR(255)',
      'host_name' => 'IDLIST',
      'hostgroup_name' => 'IDLIST',
      'servicegroups' => 'IDLIST',
      'is_volatile' => "RADIO(0,1,2)",
      'check_command' => 'SELECT+PARAMS',
      'check_command-remote' => 'VARCHAR(128)',
      'check_command-params' => 'VARCHAR(128)',
      'tcp_port' => 'INT',
      'initial_state' => "FLAGS('o','w','u','c')",
      'max_check_attempts' => 'SLIDER',
      'check_interval' => 'INT',
      'retry_interval' => 'INT',
      'active_checks_enabled' => 'BOOL',
      'passive_checks_enabled' => 'BOOL',
      'check_period' => 'SELECT',
      'parallelize_check' => 'BOOL',
      'obsess_over_service' => 'BOOL',
      'check_freshness' => 'BOOL',
      'freshness_threshold' => 'INT',
      'event_handler' => 'SELECT',
      'event_handler_enabled' => 'BOOL',
      'low_flap_threshold' => 'INT',
      'high_flap_threshold' => 'INT',
      'flap_detection_enabled' => 'BOOL',
      'flap_detection_options' => "FLAGS('o','w','u','c')",
      'failure_prediction_enabled' => 'BOOL',
      'process_perf_data' => 'BOOL',
      'retain_status_information' => 'BOOL',
      'retain_nonstatus_information' => 'BOOL',
      'notification_interval' => 'SLIDER',
      'first_notification_delay' => 'SLIDER',
      'notification_period' => 'SELECT',
      'notification_options' => "FLAGS('w','u','c','r','f','s')",
      'notifications_enabled' => 'BOOL',
      'contacts' => 'IDLIST',
      'contact_groups' => 'IDLIST',
      'stalking_options' => "FLAGS('o','w','u','c')",
      'notes' => 'VARCHAR(255)',
      'notes_url' => 'VARCHAR(64)',
      'action_url' => 'VARCHAR(64)',
      'icon_image' => 'VARCHAR(64)',
      'icon_image_alt' => 'VARCHAR(64)',
      'configurator' => 'VARCHAR(64)',
      'platform' => "PLATFORM"
    );
    public $keywordsInfo = array(
      'host_name' => array(
        'severity' => 'mandatory',
        'title' => 'hosty služby',
        'required' => true,
        'refdata' => array(
          'table' => 'host',
          'captioncolumn' => 'host_name',
          'idcolumn' => 'host_id',
          'condition' => array('register' => 1)
        )
      ),
      'hostgroup_name' => array(
        'severity' => 'optional',
        'title' => 'skupiny hostů služby',
        'refdata' => array(
          'table' => 'hostgroup',
          'captioncolumn' => 'hostgroup_name',
          'idcolumn' => 'hostgroup_id')
      ),
      'service_description' => array(
        'severity' => 'mandatory',
        'title' => 'popisek služby', 'required' => true),
      'display_name' => array(
        'severity' => 'basic',
        'title' => 'zobrazované jméno'),
      'tcp_port' => array(
        'severity' => 'advanced',
        'title' => 'sledovaný port služby'),
      'servicegroups' => array(
        'severity' => 'optional',
        'title' => 'skupiny služeb',
        'refdata' => array(
          'table' => 'servicegroup',
          'captioncolumn' => 'servicegroup_name',
          'idcolumn' => 'servicegroup_id')
      ),
      'is_volatile' => array(
        'severity' => 'advanced',
        'title' => 'volatile',
        '0' => 'service is not volatile',
        '1' => 'service is volatile',
        '2' => 'service is volatile but will respect the re-notification interval for notifications'
      ),
      'check_command' => array(
        'severity' => 'mandatory',
        'title' => 'příkaz testu',
        'required' => true,
        'refdata' => array(
          'table' => 'command',
          'captioncolumn' => 'command_name',
          'idcolumn' => 'command_id',
          'condition' => array('command_type' => 'check')
        )
      ),
      'check_command-remote' => array(
        'severity' => 'basic',
        'title' => 'vzdálený příkaz'),
      'check_command-params' => array(
        'severity' => 'basic',
        'title' => 'parametry testů'),
      'initial_state' => array(
        'severity' => 'advanced',
        'title' => 'výchozí stav',
        'o' => 'Ok',
        'w' => 'Warning',
        'u' => 'Up',
        'c' => 'Critical'),
      'max_check_attempts' => array(
        'severity' => 'advanced',
        'title' => 'maximální počet pokusů o test',
        'required' => true
      ),
      'check_interval' => array(
        'severity' => 'mandatory',
        'title' => 'interval testu', 'required' => true),
      'retry_interval' => array(
        'severity' => 'optional',
        'title' => 'interval opakování testu', 'required' => true),
      'active_checks_enabled' => array(
        'severity' => 'mandatory',
        'title' => 'Aktivní režim'),
      'passive_checks_enabled' => array(
        'severity' => 'mandatory',
        'title' => 'Pasivní režim'),
      'check_period' => array(
        'severity' => 'optional',
        'title' => 'perioda provádění testu', 'required' => true,
        'refdata' => array(
          'table' => 'timeperiod',
          'captioncolumn' => 'timeperiod_name',
          'idcolumn' => 'timeperiod_id')
      ),
      'parallelize_check' => array(
        'severity' => 'advanced',
        'value' => '1', 'title' => 'paraelizovat checky'),
      'obsess_over_service' => array(
        'severity' => 'advanced',
        'title' => 'posedlost službou'),
      'check_freshness' => array(
        'severity' => 'advanced',
        'title' => 'testovat čersvost'),
      'freshness_threshold' => array(
        'severity' => 'advanced',
        'title' => 'práh čerstvosti'),
      'event_handler' => array(
        'severity' => 'advanced',
        'title' => 'príkaz ošetření události',
        'refdata' => array(
          'table' => 'command',
          'captioncolumn' => 'command_name',
          'idcolumn' => 'command_id',
          'condition' => array('command_type' => 'handler')
        )
      ),
      'event_handler_enabled' => array(
        'severity' => 'advanced',
        'title' => 'povolit ošetření události'),
      'low_flap_threshold' => array(
        'severity' => 'advanced',
        'title' => 'klapka nízkého prahu'),
      'high_flap_threshold' => array(
        'severity' => 'advanced',
        'title' => 'klapka vysokého prahu'),
      'flap_detection_enabled' => array(
        'severity' => 'advanced',
        'title' => 'detekce klapání'),
      'flap_detection_options' => array(
        'severity' => 'advanced',
        'title' => 'nastavení detekce klapání'),
      'failure_prediction_enabled' => array(
        'severity' => 'advanced',
        'title' => 'předpovídat výpadek'),
      'process_perf_data' => array(
        'severity' => 'advanced',
        'title' => 'zpracovávat výkonostní data'),
      'retain_status_information' => array(
        'severity' => 'advanced',
        'title' => 'uchovávat informace o stavu'),
      'retain_nonstatus_information' => array(
        'severity' => 'advanced',
        'title' => 'uchovávat nestavové informace'),
      'notification_interval' => array(
        'severity' => 'optional',
        'title' => 'notifikační interval'),
      'first_notification_delay' => array(
        'severity' => 'advanced',
        'title' => 'první prodleva notifikace'),
      'notification_period' => array(
        'severity' => 'optional',
        'title' => 'notifikační perioda',
        'refdata' => array(
          'table' => 'timeperiod',
          'captioncolumn' => 'timeperiod_name',
          'idcolumn' => 'timeperiod_id')
      ),
      'notification_options' => array(
        'severity' => 'advanced',
        'title' => 'možnosti oznamování',
        'w' => 'send notifications on a WARNING state',
        'u' => 'send notifications on an UNKNOWN state',
        'c' => 'send notifications on a CRITICAL state',
        'r' => 'send notifications on recoveries OK',
        'f' => 'send notifications when the service starts and stops flapping',
        's' => 'send notifications when scheduled downtime starts and ends',
      ),
      'notifications_enabled' => array(
        'severity' => 'basic',
        'title' => 'povolit oznamování'),
      'contacts' => array(
        'severity' => 'basic',
        'title' => 'kontakty',
        'refdata' => array(
          'table' => 'contact',
          'captioncolumn' => 'contact_name',
          'idcolumn' => 'contact_id')),
      'contact_groups' => array(
        'severity' => 'optional',
        'title' => 'členské skupiny kontaktů',
        'refdata' => array(
          'table' => 'contactgroup',
          'captioncolumn' => 'contactgroup_name',
          'idcolumn' => 'contactgroup_id')
      ),
      'stalking_options' => array(
        'severity' => 'advanced',
        'title' => 'možnosti stopování',
        'o' => 'stalk on OK states',
        'w' => 'stalk on WARNING states',
        'u' => 'stalk on UNKNOWN states',
        'c' => 'stalk on CRITICAL states'
      ),
      'notes' => array(
        'severity' => 'basic',
        'title' => 'poznámka'),
      'notes_url' => array(
        'severity' => 'advanced',
        'title' => 'url dodatečných poznámek'),
      'action_url' => array(
        'severity' => 'advanced',
        'title' => 'url dodatečné akce'),
      'icon_image' => array(
        'severity' => 'advanced',
        'title' => 'ikona služby'),
      'icon_image_alt' => array(
        'severity' => 'advanced',
        'title' => 'alternativní ikona služby'),
      'configurator' => array(
        'severity' => 'advanced',
        'title' => 'Plugin pro konfiguraci služby'),
      'platform' => array(
        'severity' => 'basic',
        'title' => 'Platforma', 'mandatory' => true)
    );

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-service';

    /**
     * Vrací všechna data uživatele
     *
     * @return array
     */
    public function getAllUserData()
    {
        $user = EaseShared::user();
        $userID = $user->getUserID();
        $allData = parent::getAllUserData();
        foreach ($allData as $adKey => $ad) {
            if ($allData[$adKey]['check_command-remote']) {
                $params = ' ' . $allData[$adKey]['check_command-remote'] . '!' .
                    $allData[$adKey]['check_command-params'];
            } else {
                $params = ' ' . $allData[$adKey]['check_command-params'];
            }
            unset($allData[$adKey]['check_command-remote']);
            unset($allData[$adKey]['check_command-params']);
            $allData[$adKey]['check_command'].= $params;
            unset($allData[$adKey]['tcp_port']);

            if (is_array($ad['contacts']) && count($ad['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($ad['contacts'] as $ContactID => $ContactName) {
                    $contactUserID = $this->myDbLink->QueryToValue('SELECT `user_id` FROM ' . 'contact WHERE contact_id=' . $ContactID);
                    if ($userID != $contactUserID) {
                        unset($allData[$adKey]['contacts'][$ContactID]);
                    }
                }
            }

            if (is_array($ad['host_name']) && count($ad['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($ad['host_name'] as $hostID => $HostName) {
                    $hostUserID = $this->myDbLink->QueryToValue('SELECT `user_id` FROM host WHERE host_id=' . $hostID);
                    if ($userID != $hostUserID) {
                        unset($allData[$adKey]['host_name'][$hostID]);
                    }
                }
            }

            if (!$this->isTemplate($allData[$adKey])) {
                if (!strlen($allData[$adKey]['display_name'])) {
                    $allData[$adKey]['display_name'] = $allData[$adKey][$this->nameColumn];
                }
                $allData[$adKey][$this->nameColumn] = $allData[$adKey][$this->nameColumn] . '-' .
                    EaseShared::user()->getUserLogin(); //Přejmenovat službu podle uživatele
                if (!count($allData[$adKey]['host_name'])) { //Negenerovat nepoužité služby
                    unset($allData[$adKey]);
                }
            }
        }

        return $allData;
    }

    /**
     * Vrací všechna data
     *
     * @return array
     */
    public function getAllData()
    {
        $allData = parent::getAllData();
        foreach ($allData as $adKey => $AD) {
            $params = $allData[$adKey]['check_command-params'];

            if (strlen($allData[$adKey]['check_command-remote'])) {
                if (!is_null($params)) {
                    $allData[$adKey]['check_command'].= '!' . $allData[$adKey]['check_command-remote'] . '!' . $params;
                } else {
                    $allData[$adKey]['check_command'].= '!' . $allData[$adKey]['check_command-remote'];
                }
            } else {
                if (strlen($params)) {
                    $allData[$adKey]['check_command'].= '!' . $params;
                }
            }
            unset($allData[$adKey]['check_command-remote']);
            unset($allData[$adKey]['check_command-params']);
            unset($allData[$adKey]['tcp_port']);
            unset($allData[$adKey]['configurator']);
            unset($allData[$adKey]['price']);
        }

        return $allData;
    }

    /**
     * Zkontroluje všechny položky
     *
     * @param  array $allData
     * @return array
     */
    public function controlAllData($allData)
    {
        $user = EaseShared::user();
        $userID = $user->getUserID();
        foreach ($allData as $adKey => $ad) {

            if ($ad[$this->nameColumn] == 'FTP') {
                echo '';
            }

            if ($this->isTemplate($ad)) { //Předloha
                if ($userID != (int) $ad[$this->userColumn]) {
                    //Patří jinému než právě generovanému
                    unset($allData[$adKey]);
                    continue;
                }
                if (!(int) $this->myDbLink->QueryToValue(
                        'SELECT COUNT(*) FROM ' . $this->myTable .
                        ' WHERE '
                        . '`use` LIKE \'' . $ad['name'] . ',%\' OR '
                        . '`use` LIKE \'%,' . $ad['name'] . '\' OR '
                        . '`use` LIKE \'%,' . $ad['name'] . ',%\' OR '
                        . '`use` LIKE \'' . $ad['name'] . '\''
                    )
                ) {
                    //$this->addStatusMessage(sprintf(_('Předloha služby %s není použita. Negeneruji do konfigurace'), $ad['name']), 'info');
                    unset($allData[$adKey]);
                    continue;
                }
            } else { //záznam
                $allData[$adKey][$this->nameColumn] = str_replace(' ', '_', $allData[$adKey][$this->nameColumn]) . '-' . $user->getUserLogin();

                if (is_array($ad['contacts']) && count($ad['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($ad['contacts'] as $ContactID => $ContactName) {
                        $ContactUserID = $this->myDbLink->QueryToValue('SELECT `user_id` FROM `contact` WHERE contact_id=' . $ContactID);
                        if ($userID != $ContactUserID) {
                            unset($allData[$adKey]['contacts'][$ContactID]);
                        }
                    }
                }

                if (is_array($ad['host_name']) && count($ad['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($ad['host_name'] as $HostID => $HostName) {
                        $hostUserID = (int) $this->myDbLink->QueryToValue('SELECT `user_id` FROM `host` WHERE host_id=' . $HostID);
                        if ($userID != $hostUserID) {
                            unset($allData[$adKey]['host_name'][$HostID]);
                        }
                    }
                }

                if (is_null($allData[$adKey]['host_name']) || !count($allData[$adKey]['host_name'])) {
//                    if ($ad[$this->userColumn] == $userID) {
//                        //$service_link = 'service.php?' . $this->myKeyColumn . '=' . $ad[$this->myKeyColumn];
//                        //$this->addStatusMessage(sprintf(_('Služba <a href="%s">%s</a> není použita. Negeneruji do konfigurace'), $service_link, $ad[$this->nameColumn]), 'info');
//                    }
                    unset($allData[$adKey]);
                    continue;
                }

                if ($allData[$adKey]['max_check_attempts'] == 0) {
                    unset($allData[$adKey]['max_check_attempts']);
                }
                if ($allData[$adKey]['check_interval'] == 0) {
                    unset($allData[$adKey]['check_interval']);
                }
                if ($allData[$adKey]['retry_interval'] == 0) {
                    $allData[$adKey]['retry_interval'] == 60;
                }
                if ($allData[$adKey]['notification_interval'] == 0) {
                    unset($allData[$adKey]['notification_interval']);
                }
            }
        }

        return $allData;
    }

    /**
     * Přestane službu sledovat pro daný host
     *
     * @param int    $hostID
     * @param string $hostName
     *
     * @return bool success
     */
    public function delHostName($hostID, $hostName)
    {
        return $this->delMember('host_name', $hostID, $hostName);
    }

    /**
     * Provede přejmenování hostu
     *
     * @param  int    $hostid
     * @param  string $newname
     * @return type
     */
    public function renameHostName($hostid, $newname)
    {
        return $this->renameMember('host_name', $hostid, $newname);
    }

    /**
     * Přejmenuje službu
     *
     * @param string $newname
     * @return boolean
     */
    public function rename($newname)
    {
        $oldname = $this->getName();
        $this->setDataValue($this->nameColumn, $newname);

        $renameAll = true;

        if ($this->save() && $renameAll) {
            return true;
        }

        return false;
    }

    /**
     * Připraví podřízenu službu
     *
     * @param  IEHost $host
     * @param  int    $ownerId
     * @return int    ID nově vytvořené služby
     */
    public function fork($host, $ownerId = null)
    {
        if (is_null($ownerId)) {
            $ownerId = EaseShared::user()->getUserID();
        }
        $this->delMember('host_name', $host->getId(), $host->getName());
        $this->saveToMySQL();

        $this->setDataValue('parent_id', $this->getId());
        $this->unsetDataValue($this->getmyKeyColumn());
        $this->setDataValue('public', 0);
        $this->unsetDataValue('tcp_port');
        $this->unsetDataValue('DatSave');
        $this->unsetDataValue('DatCreate');
        $this->setDataValue('action_url', $_SERVER['REQUEST_URI']);
        $this->setDataValue($this->userColumn, $ownerId);
        $this->setDataValue('contacts', $host->owner->getFirstContact());

        $newname = $this->getName() . ' ' . $host->getName();

        $servcount = $this->myDbLink->queryToCount('SELECT ' . $this->getmyKeyColumn() . ' FROM ' . $this->myTable . ' WHERE ' . $this->nameColumn . ' LIKE \'' . $newname . '%\' ');

        if ($servcount) {
            $newname .= ' ' . ($servcount + 1);
        }

        $this->setDataValue($this->nameColumn, $newname);
        $this->setDataValue('host_name', array());
        $this->addMember('host_name', $host->getId(), $host->getName());

        return $this->saveToMySQL();
    }

    /**
     * Přehodí
     * @param type $swapToID
     * @return boolean
     */
    public function swapTo($swapToID)
    {
        $newService = new IEService($swapToID);
        $thisName = $this->getName();
        $hostsOK = array();
        $hostsErr = array();
        $hostsAssigned = array();
        $host = new IEHost();

        if (EaseShared::user()->getSettingValue('admin')) {
            $allHosts = $host->getAllFromMySQL(NULL, array($host->myKeyColumn, $host->nameColumn, 'platform', 'register'), null, $host->nameColumn, $host->myKeyColumn);
        } else {
            $allHosts = $host->getListing(null, true, array('platform', 'register'));
        }
        $hosts = $this->getDataValue('host_name');
        foreach ($hosts as $hostId => $hostName) {
            if (isset($allHosts[$hostId])) {
                $hostsAssigned[$hostId] = $allHosts[$hostId];
            }
        }

        foreach ($hostsAssigned as $host_id => $hostAssigned) {
            if ($this->delMember('host_name', $host_id, $hostAssigned['host_name']) && $newService->addMember('host_name', $host_id, $hostAssigned['host_name'])) {
                $hostsOK[] = $hostAssigned['host_name'];
            } else {
                $hostsErr[] = $hostAssigned['host_name'];
            }
        }
        if ($this->saveToMySQL() && $newService->saveToMySQL() && count($hostsOK)) {
            $this->addStatusMessage(sprintf(_('%s byl přesunut z %s/%s do %s'), implode(',', $hostsOK), $this->keyword, $this->getName(), $newService->getName()), 'success');
            return true;
        } else {
            $this->addStatusMessage(sprintf(_(' %s nebyl přesunut z %s/%s do %s'), implode(',', $hostsErr), $this->keyword, $this->getName(), $newService->getName()), 'warning');
            return false;
        }
    }

}
