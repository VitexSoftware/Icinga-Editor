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

    public $myTable = 'services';
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
      'check_interval' => 'SLIDER',
      'retry_interval' => 'SLIDER',
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
      'platform' => "ENUM('generic','linux','windows')"
    );
    public $keywordsInfo = array(
      'host_name' => array(
        'title' => 'hosty služby',
        'required' => true,
        'refdata' => array(
          'table' => 'hosts',
          'captioncolumn' => 'host_name',
          'idcolumn' => 'host_id',
          'condition' => array('register' => 1)
        )
      ),
      'hostgroup_name' => array(
        'title' => 'skupiny hostů služby',
        'refdata' => array(
          'table' => 'hostgroup',
          'captioncolumn' => 'hostgroup_name',
          'idcolumn' => 'hostgroup_id')
      ),
      'service_description' => array('title' => 'popisek služby', 'required' => true),
      'display_name' => array('title' => 'zobrazované jméno'),
      'tcp_port' => array('title' => 'sledovaný port služby'),
      'servicegroups' => array('title' => 'skupiny služeb',
        'refdata' => array(
          'table' => 'servicegroup',
          'captioncolumn' => 'servicegroup_name',
          'idcolumn' => 'servicegroup_id')
      ),
      'is_volatile' => array('title' => 'volatile',
        '0' => 'service is not volatile',
        '1' => 'service is volatile',
        '2' => 'service is volatile but will respect the re-notification interval for notifications'
      ),
      'check_command' => array(
        'title' => 'příkaz testu',
        'required' => true,
        'refdata' => array(
          'table' => 'command',
          'captioncolumn' => 'command_name',
          'idcolumn' => 'command_id',
          'condition' => array('command_type' => 'check')
        )
      ),
      'check_command-params' => array(),
      'initial_state' => array(
        'title' => 'výchozí stav',
        'o' => 'Ok',
        'w' => 'Warning',
        'u' => 'Up',
        'c' => 'Critical'),
      'max_check_attempts' => array(
        'title' => 'maximální počet pokusů o test',
        'required' => true
      ),
      'check_interval' => array('title' => 'interval testu', 'required' => true),
      'retry_interval' => array('title' => 'interval opakování testu', 'required' => true),
      'active_checks_enabled' => array('title' => ''),
      'passive_checks_enabled' => array('title' => ''),
      'check_period' => array('title' => 'perioda provádění testu', 'required' => true,
        'refdata' => array(
          'table' => 'timeperiods',
          'captioncolumn' => 'timeperiod_name',
          'idcolumn' => 'timeperiod_id')
      ),
      'parallelize_check' => array('value' => '1', 'title' => ''),
      'obsess_over_service' => array('title' => ''),
      'check_freshness' => array('title' => ''),
      'freshness_threshold' => array('title' => ''),
      'event_handler' => array('title' => 'príkaz ošetření události',
        'refdata' => array(
          'table' => 'command',
          'captioncolumn' => 'command_name',
          'idcolumn' => 'command_id',
          'condition' => array('command_type' => 'handler')
        )
      ),
      'event_handler_enabled' => array('title' => 'povolit ošetření události'),
      'low_flap_threshold' => array('title' => ''),
      'high_flap_threshold' => array('title' => ''),
      'flap_detection_enabled' => array('title' => ''),
      'flap_detection_options' => array('title' => ''),
      'failure_prediction_enabled' => array('title' => ''),
      'process_perf_data' => array('title' => ''),
      'retain_status_information' => array('title' => ''),
      'retain_nonstatus_information' => array('title' => ''),
      'notification_interval' => array('title' => 'notifikační interval'),
      'first_notification_delay' => array('title' => ''),
      'notification_period' => array('title' => 'notifikační perioda',
        'refdata' => array(
          'table' => 'timeperiods',
          'captioncolumn' => 'timeperiod_name',
          'idcolumn' => 'timeperiod_id')
      ),
      'notification_options' => array('title' => 'možnosti oznamování',
        'w' => 'send notifications on a WARNING state',
        'u' => 'send notifications on an UNKNOWN state',
        'c' => 'send notifications on a CRITICAL state',
        'r' => 'send notifications on recoveries OK',
        'f' => 'send notifications when the service starts and stops flapping',
        's' => 'send notifications when scheduled downtime starts and ends',
      ),
      'notifications_enabled' => array('title' => 'povolit oznamování'),
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
      'stalking_options' => array('title' => '',
        'o' => 'stalk on OK states',
        'w' => 'stalk on WARNING states',
        'u' => 'stalk on UNKNOWN states',
        'c' => 'stalk on CRITICAL states'
      ),
      'notes' => array('title' => 'poznámka'),
      'check_command-remote' => array(),
      'notes_url' => array('title' => 'url dodatečných poznámek'),
      'action_url' => array('title' => 'url dodatečné akce'),
      'icon_image' => array('title' => 'ikona služby'),
      'icon_image_alt' => array('title' => 'alternativní ikona služby'),
      'configurator' => array('title' => 'Plugin pro konfiguraci služby'),
      'platform' => array('title' => 'Platforma', 'mandatory' => true)
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
                    $contactUserID = $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'contact WHERE contact_id=' . $ContactID);
                    if ($userID != $contactUserID) {
                        unset($allData[$adKey]['contacts'][$ContactID]);
                    }
                }
            }

            if (is_array($ad['host_name']) && count($ad['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($ad['host_name'] as $hostID => $HostName) {
                    $hostUserID = $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'hosts WHERE host_id=' . $hostID);
                    if ($userID != $hostUserID) {
                        unset($allData[$adKey]['host_name'][$hostID]);
                    }
                }
            }

            if (!$this->isTemplate($allData[$adKey])) {
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
                    $this->addStatusMessage(sprintf(_('Předloha služby %s není použita. Negeneruji do konfigurace'), $ad['name']), 'info');
                    unset($allData[$adKey]);
                    continue;
                }
            } else { //záznam
                $allData[$adKey][$this->nameColumn] = $allData[$adKey][$this->nameColumn] . '-' . $user->getUserLogin();

                if (is_array($ad['contacts']) && count($ad['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($ad['contacts'] as $ContactID => $ContactName) {
                        $ContactUserID = $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'contact WHERE contact_id=' . $ContactID);
                        if ($userID != $ContactUserID) {
                            unset($allData[$adKey]['contacts'][$ContactID]);
                        }
                    }
                }

                if (is_array($ad['host_name']) && count($ad['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($ad['host_name'] as $HostID => $HostName) {
                        $hostUserID = (int) $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'hosts WHERE host_id=' . $HostID);
                        if ($userID != $hostUserID) {
                            unset($allData[$adKey]['host_name'][$HostID]);
                        };
                    }
                }

                if (is_null($allData[$adKey]['host_name']) || !count($allData[$adKey]['host_name'])) {
                    if ($ad[$this->userColumn] == $userID) {
                        $this->addStatusMessage(sprintf(_('Služba %s není použita. Negeneruji do konfigurace'), $ad[$this->nameColumn]), 'info');
                    }
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

}

/*
  UPDATE `iciedit`.`iciedit_services` SET `max_check_attempts` = NULL, `check_interval` = NULL, `retry_interval` = NULL, `active_checks_enabled` = NULL, `passive_checks_enabled` = NULL, `check_period` = NULL, `parallelize_check` = NULL, `retry_check_interval` = NULL, `obsess_over_service` = NULL, `check_freshness` = NULL, `freshness_threshold` = NULL, `event_handler` = NULL, `event_handler_enabled` = NULL, `low_flap_threshold` = NULL, `high_flap_threshold` = NULL, `flap_detection_enabled` = NULL, `flap_detection_options` = NULL, `failure_prediction_enabled` = NULL, `process_perf_data` = NULL, `retain_status_information` = NULL, `retain_nonstatus_information` = NULL, `notification_interval` = NULL, `first_notification_delay` = NULL, `notification_period` = NULL, `notification_options` = NULL, `notifications_enabled` = NULL WHERE `iciedit_services`.`service_id` = 15;
 */
