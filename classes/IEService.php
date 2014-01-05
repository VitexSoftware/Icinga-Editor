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
        'normal_check_interval' => 'INT',
        'retry_check_interval' => 'INT',
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
        'icon_image_alt' => 'VARCHAR(64)'
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
        'normal_check_interval' => array('value' => 5, 'title' => ''),
        'retry_check_interval' => array('value' => 1, 'title' => ''),
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
    function getAllUserData()
    {
        $User = EaseShared::user();
        $UserID = $User->getUserID();
        $AllData = parent::getAllUserData();
        foreach ($AllData as $ADkey => $AD) {
            if ($AllData[$ADkey]['check_command-remote']) {
                $Params = ' ' . $AllData[$ADkey]['check_command-remote'] . '!' . $AllData[$ADkey]['check_command-params'];
            } else {
                $Params = ' ' . $AllData[$ADkey]['check_command-params'];
            }
            unset($AllData[$ADkey]['check_command-remote']);
            unset($AllData[$ADkey]['check_command-params']);
            $AllData[$ADkey]['check_command'].= $Params;
            unset($AllData[$ADkey]['tcp_port']);
            
            if (is_array($AD['contacts']) && count($AD['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($AD['contacts'] as $ContactID => $ContactName) {
                    $ContactUserID = $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'contact WHERE contact_id=' . $ContactID);
                    if ($UserID != $ContactUserID) {
                        unset($AllData[$ADkey]['contacts'][$ContactID]);
                    }
                }
            }

            if (is_array($AD['host_name']) && count($AD['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($AD['host_name'] as $HostID => $HostName) {
                    $HostUserID = $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'hosts WHERE host_id=' . $HostID);
                    if ($UserID != $HostUserID) {
                        unset($AllData[$ADkey]['host_name'][$HostID]);
                    }
                }
            }


            if (!$this->isTemplate($AllData[$ADkey])) {
                $AllData[$ADkey][$this->nameColumn] = $AllData[$ADkey][$this->nameColumn] . '-' . EaseShared::user()->getUserLogin(); //Přejmenovat službu podle uživatele
                if (!count($AllData[$ADkey]['host_name'])) { //Negenerovat nepoužité služby
                    unset($AllData[$ADkey]);
                }
            }
        }



        return $AllData;
    }

    /**
     * Vrací všechna data
     * 
     * @return array 
     */
    function getAllData()
    {
        $allData = parent::getAllData();
        foreach ($allData as $ADkey => $AD) {
            $Params = $allData[$ADkey]['check_command-params'];


            if (strlen($allData[$ADkey]['check_command-remote'])) {
                if (strlen($Params)) {
                    $allData[$ADkey]['check_command'].= '!'.$allData[$ADkey]['check_command-remote'] . '!' . $Params;
                } else {
                    $allData[$ADkey]['check_command'].= '!'.$allData[$ADkey]['check_command-remote'];
                }
            } else {
                if (strlen($Params)) {
                    $allData[$ADkey]['check_command'].= '!' . $Params;
                }
            }
            unset($allData[$ADkey]['check_command-remote']);
            unset($allData[$ADkey]['check_command-params']);
            unset($allData[$ADkey]['tcp_port']);
        }
        return $allData;
    }

    /**
     * Zkontroluje všechny položky
     * 
     * @param array $AllData
     * @return array
     */
    function controlAllData($AllData)
    {
        $User = EaseShared::user();
        $UserID = $User->getUserID();
        foreach ($AllData as $ADkey => $AD) {

            if ($AD[$this->nameColumn] == 'FTP') {
                echo '';
            }

            if ($this->isTemplate($AD)) { //Předloha
                if ($UserID != (int) $AD[$this->userColumn]) {
                    unset($AllData[$ADkey]);
                    continue;
                }
                if (!(int) $this->myDbLink->QueryToValue('SELECT COUNT(*) FROM ' . $this->myTable . ' WHERE `use`=\'' . $AD['name'] . '\'')) {
                    $this->addStatusMessage(sprintf(_('Předloha služby %s není použita. Negeneruji do konfigurace'), $AD['name']), 'info');
                    unset($AllData[$ADkey]);
                    continue;
                }
            } else { //záznam
                $AllData[$ADkey][$this->nameColumn] = $AllData[$ADkey][$this->nameColumn] . '-' . $User->getUserLogin();

                if (is_array($AD['contacts']) && count($AD['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($AD['contacts'] as $ContactID => $ContactName) {
                        $ContactUserID = $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'contact WHERE contact_id=' . $ContactID);
                        if ($UserID != $ContactUserID) {
                            unset($AllData[$ADkey]['contacts'][$ContactID]);
                        }
                    }
                }

                if (is_array($AD['host_name']) && count($AD['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($AD['host_name'] as $HostID => $HostName) {
                        $HostUserID = (int) $this->myDbLink->QueryToValue('SELECT user_id FROM ' . DB_PREFIX . 'hosts WHERE host_id=' . $HostID);
                        if ($UserID != $HostUserID) {
                            unset($AllData[$ADkey]['host_name'][$HostID]);
                        };
                    }
                }

                if (is_null($AllData[$ADkey]['host_name']) || !count($AllData[$ADkey]['host_name'])) {
                    if ($AD[$this->userColumn] == $UserID) {
                        $this->addStatusMessage(sprintf(_('Služba %s není použita. Negeneruji do konfigurace'), $AD[$this->nameColumn]), 'info');
                    }
                    unset($AllData[$ADkey]);
                    continue;
                }

                if ($AllData[$ADkey]['max_check_attempts'] == 0) {
                    unset($AllData[$ADkey]['max_check_attempts']);
                }
                if ($AllData[$ADkey]['check_interval'] == 0) {
                    unset($AllData[$ADkey]['check_interval']);
                }
                if ($AllData[$ADkey]['retry_interval'] == 0) {
                    $AllData[$ADkey]['retry_interval'] == 60;
                }
                if ($AllData[$ADkey]['notification_interval'] == 0) {
                    unset($AllData[$ADkey]['notification_interval']);
                }
            }
        }
        return $AllData;
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

    public function renameHostName($hostid, $newname)
    {
        return $this->renameMember('host_name', $hostid, $newname);
    }

   

}

/*
  UPDATE `iciedit`.`iciedit_services` SET `max_check_attempts` = NULL, `check_interval` = NULL, `retry_interval` = NULL, `active_checks_enabled` = NULL, `passive_checks_enabled` = NULL, `check_period` = NULL, `parallelize_check` = NULL, `normal_check_interval` = NULL, `retry_check_interval` = NULL, `obsess_over_service` = NULL, `check_freshness` = NULL, `freshness_threshold` = NULL, `event_handler` = NULL, `event_handler_enabled` = NULL, `low_flap_threshold` = NULL, `high_flap_threshold` = NULL, `flap_detection_enabled` = NULL, `flap_detection_options` = NULL, `failure_prediction_enabled` = NULL, `process_perf_data` = NULL, `retain_status_information` = NULL, `retain_nonstatus_information` = NULL, `notification_interval` = NULL, `first_notification_delay` = NULL, `notification_period` = NULL, `notification_options` = NULL, `notifications_enabled` = NULL WHERE `iciedit_services`.`service_id` = 15;
 */
?>
