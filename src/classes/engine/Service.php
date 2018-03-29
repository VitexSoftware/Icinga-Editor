<?php
/**
 * Icinga Editor - Service configuration
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

/**
 * Služby
 */
class Service extends Configurator
{
    public $myTable    = 'service';
    public $keyColumn  = 'service_id';
    public $keyword    = 'service';
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

    /**
     * Keywords info
     * @var array
     */
    public $useKeywords = [
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
        'autocfg' => 'BOOL',
        'configurator' => 'VARCHAR(64)',
        'platform' => "PLATFORM"
    ];

    /**
     * Keywords info
     * @var array
     */
    public $keywordsInfo = [];

    public function __construct($itemID = null)
    {
        $this->keywordsInfo = [
            'host_name' => [
                'severity' => 'mandatory',
                'title' => _('service hosts'),
                'required' => true,
                'refdata' => [
                    'table' => 'host',
                    'captioncolumn' => 'host_name',
                    'idcolumn' => 'host_id',
                    'condition' => ['register' => 1]
                ]
            ],
            'hostgroup_name' => [
                'severity' => 'optional',
                'title' => _('service hostgroups'),
                'refdata' => [
                    'table' => 'hostgroup',
                    'captioncolumn' => 'hostgroup_name',
                    'idcolumn' => 'hostgroup_id']
            ],
            'service_description' => [
                'severity' => 'mandatory',
                'title' => _('service description'), 'required' => true],
            'display_name' => [
                'severity' => 'basic',
                'title' => _('display name')],
            'tcp_port' => [
                'severity' => 'advanced',
                'title' => _('tcp service port')],
            'servicegroups' => [
                'severity' => 'optional',
                'title' => _('service group'),
                'refdata' => [
                    'table' => 'servicegroup',
                    'captioncolumn' => 'servicegroup_name',
                    'idcolumn' => 'servicegroup_id']
            ],
            'is_volatile' => [
                'severity' => 'advanced',
                'title' => _('volatile'),
                '0' => 'service is not volatile',
                '1' => 'service is volatile',
                '2' => 'service is volatile but will respect the re-notification interval for notifications'
            ],
            'check_command' => [
                'severity' => 'mandatory',
                'title' => _('check command'),
                'required' => true,
                'refdata' => [
                    'table' => 'command',
                    'captioncolumn' => 'command_name',
                    'idcolumn' => 'command_id',
                    'condition' => ['command_type' => 'check']
                ]
            ],
            'check_command-remote' => [
                'severity' => 'basic',
                'title' => _('remote check command')],
            'check_command-params' => [
                'severity' => 'basic',
                'title' => _('check command parrams')],
            'initial_state' => [
                'severity' => 'advanced',
                'title' => _('initial state'),
                'o' => 'Ok',
                'w' => 'Warning',
                'u' => 'Up',
                'c' => 'Critical'],
            'max_check_attempts' => [
                'severity' => 'advanced',
                'title' => _('max check attempts'),
                'required' => true
            ],
            'check_interval' => [
                'severity' => 'mandatory',
                'title' => _('check interval'), 'required' => true],
            'retry_interval' => [
                'severity' => 'optional',
                'title' => _('retry interval'), 'required' => true],
            'active_checks_enabled' => [
                'severity' => 'mandatory',
                'title' => _('Active mode')],
            'passive_checks_enabled' => [
                'severity' => 'mandatory',
                'title' => _('Passive mode')],
            'check_period' => [
                'severity' => 'optional',
                'title' => _('check period'), 'required' => true,
                'refdata' => [
                    'table' => 'timeperiod',
                    'captioncolumn' => 'timeperiod_name',
                    'idcolumn' => 'timeperiod_id']
            ],
            'parallelize_check' => [
                'severity' => 'advanced',
                'value' => '1', 'title' => _('paraelize check')],
            'obsess_over_service' => [
                'severity' => 'advanced',
                'title' => _('obsess over service')],
            'check_freshness' => [
                'severity' => 'advanced',
                'title' => _('check freshnes')],
            'freshness_threshold' => [
                'severity' => 'advanced',
                'title' => _('freshness treshold')],
            'event_handler' => [
                'severity' => 'advanced',
                'title' => _('event handler'),
                'refdata' => [
                    'table' => 'command',
                    'captioncolumn' => 'command_name',
                    'idcolumn' => 'command_id',
                    'condition' => ['command_type' => 'handler']
                ]
            ],
            'event_handler_enabled' => [
                'severity' => 'advanced',
                'title' => _('event handler enabled')],
            'low_flap_threshold' => [
                'severity' => 'advanced',
                'title' => _('low flap treshold')],
            'high_flap_threshold' => [
                'severity' => 'advanced',
                'title' => _('high flap treshold')],
            'flap_detection_enabled' => [
                'severity' => 'advanced',
                'title' => _('flap detection enabled')],
            'flap_detection_options' => [
                'severity' => 'advanced',
                'title' => _('flap detection enabled')],
            'failure_prediction_enabled' => [
                'severity' => 'advanced',
                'title' => _('failure prediction enabled')],
            'process_perf_data' => [
                'severity' => 'advanced',
                'title' => _('process perf data')],
            'retain_status_information' => [
                'severity' => 'advanced',
                'title' => _('retain status information')],
            'retain_nonstatus_information' => [
                'severity' => 'advanced',
                'title' => _('retain nonstatus information')],
            'notification_interval' => [
                'severity' => 'optional',
                'title' => _('notification interval')],
            'first_notification_delay' => [
                'severity' => 'advanced',
                'title' => _('first notification delay')],
            'notification_period' => [
                'severity' => 'optional',
                'title' => _('notification period'),
                'refdata' => [
                    'table' => 'timeperiod',
                    'captioncolumn' => 'timeperiod_name',
                    'idcolumn' => 'timeperiod_id']
            ],
            'notification_options' => [
                'severity' => 'advanced',
                'title' => _('notification options'),
                'w' => 'send notifications on a WARNING state',
                'u' => 'send notifications on an UNKNOWN state',
                'c' => 'send notifications on a CRITICAL state',
                'r' => 'send notifications on recoveries OK',
                'f' => 'send notifications when the service starts and stops flapping',
                's' => 'send notifications when scheduled downtime starts and ends',
            ],
            'notifications_enabled' => [
                'severity' => 'basic',
                'title' => _('notifications enabled')],
            'contacts' => [
                'severity' => 'basic',
                'title' => _('contacts'),
                'refdata' => [
                    'table' => 'contact',
                    'captioncolumn' => 'contact_name',
                    'idcolumn' => 'contact_id']],
            'contact_groups' => [
                'severity' => 'optional',
                'title' => _('contactgroups'),
                'refdata' => [
                    'table' => 'contactgroup',
                    'captioncolumn' => 'contactgroup_name',
                    'idcolumn' => 'contactgroup_id']
            ],
            'stalking_options' => [
                'severity' => 'advanced',
                'title' => _('stalking options'),
                'o' => 'stalk on OK states',
                'w' => 'stalk on WARNING states',
                'u' => 'stalk on UNKNOWN states',
                'c' => 'stalk on CRITICAL states'
            ],
            'notes' => [
                'severity' => 'basic',
                'title' => _('notes')],
            'notes_url' => [
                'severity' => 'advanced',
                'title' => _('notes url')],
            'action_url' => [
                'severity' => 'advanced',
                'title' => _('action url')],
            'icon_image' => [
                'severity' => 'advanced',
                'title' => _('icon image ')],
            'icon_image_alt' => [
                'severity' => 'advanced',
                'title' => _('icon image alt text')],
            'autocfg' => [
                'severity' => 'advanced',
                'title' => _('require additional configuration ?')],
            'configurator' => [
                'severity' => 'advanced',
                'title' => _('Configuration plugin')],
            'platform' => [
                'severity' => 'basic',
                'title' => _('Platform'), 'mandatory' => true]
        ];
        parent::__construct($itemID);
    }
    /**
     * Object documentation URL
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
        $user    = \Ease\Shared::user();
        $userID  = $user->getUserID();
        $allData = parent::getAllUserData();
        foreach ($allData as $adKey => $ad) {
            if ($allData[$adKey]['check_command-remote']) {
                $params = ' '.$allData[$adKey]['check_command-remote'].'!'.
                    $allData[$adKey]['check_command-params'];
            } else {
                $params = ' '.$allData[$adKey]['check_command-params'];
            }
            unset($allData[$adKey]['check_command-remote']);
            unset($allData[$adKey]['check_command-params']);
            $allData[$adKey]['check_command'] .= $params;
            unset($allData[$adKey]['tcp_port']);

            if (is_array($ad['contacts']) && count($ad['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($ad['contacts'] as $ContactID => $ContactName) {
                    $contactUserID = $this->dblink->QueryToValue('SELECT `user_id` FROM '.'contact WHERE contact_id='.$ContactID);
                    if ($userID != $contactUserID) {
                        unset($allData[$adKey]['contacts'][$ContactID]);
                    }
                }
            }

            if (is_array($ad['host_name']) && count($ad['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                foreach ($ad['host_name'] as $hostID => $HostName) {
                    $hostUserID = $this->dblink->QueryToValue('SELECT `user_id` FROM host WHERE host_id='.$hostID);
                    if ($userID != $hostUserID) {
                        unset($allData[$adKey]['host_name'][$hostID]);
                    }
                }
            }

            if (!$this->isTemplate($allData[$adKey])) {
                if (!strlen($allData[$adKey]['display_name'])) {
                    $allData[$adKey]['display_name'] = $allData[$adKey][$this->nameColumn];
                }
                $allData[$adKey][$this->nameColumn] = $allData[$adKey][$this->nameColumn].'-'.
                    \Ease\Shared::user()->getUserLogin(); //Přejmenovat službu podle uživatele
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
                    $allData[$adKey]['check_command'] .= '!'.$allData[$adKey]['check_command-remote'].'!'.$params;
                } else {
                    $allData[$adKey]['check_command'] .= '!'.$allData[$adKey]['check_command-remote'];
                }
            } else {
                if (strlen($params)) {
                    $allData[$adKey]['check_command'] .= '!'.$params;
                }
            }
            unset($allData[$adKey]['check_command-remote']);
            unset($allData[$adKey]['check_command-params']);
            unset($allData[$adKey]['tcp_port']);
            unset($allData[$adKey]['autocfg']);
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
        $user   = \Ease\Shared::user();
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
                if (!(int) $this->dblink->QueryToValue(
                        'SELECT COUNT(*) FROM '.$this->myTable.
                        ' WHERE '
                        .'`use` LIKE \''.$ad['name'].',%\' OR '
                        .'`use` LIKE \'%,'.$ad['name'].'\' OR '
                        .'`use` LIKE \'%,'.$ad['name'].',%\' OR '
                        .'`use` LIKE \''.$ad['name'].'\''
                    )
                ) {
                    //$this->addStatusMessage(sprintf(_('Předloha služby %s není použita. Negeneruji do konfigurace'), $ad['name']), 'info');
                    unset($allData[$adKey]);
                    continue;
                }
            } else { //záznam
                $allData[$adKey][$this->nameColumn] = str_replace(' ', '_',
                        $allData[$adKey][$this->nameColumn]).'-'.$user->getUserLogin();

                if (is_array($ad['contacts']) && count($ad['contacts'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($ad['contacts'] as $ContactID => $ContactName) {
                        $ContactUserID = $this->dblink->QueryToValue('SELECT `user_id` FROM `contact` WHERE contact_id='.$ContactID);
                        if ($userID != $ContactUserID) {
                            unset($allData[$adKey]['contacts'][$ContactID]);
                        }
                    }
                }

                if (is_array($ad['host_name']) && count($ad['host_name'])) { //Projít kontakty, vyhodit nevlastněné uživatelem
                    foreach ($ad['host_name'] as $HostID => $HostName) {
                        $hostUserID = (int) $this->dblink->QueryToValue('SELECT `user_id` FROM `host` WHERE host_id='.$HostID);
                        if ($userID != $hostUserID) {
                            unset($allData[$adKey]['host_name'][$HostID]);
                        }
                    }
                }

                if (is_null($allData[$adKey]['host_name']) || !count($allData[$adKey]['host_name'])) {
//                    if ($ad[$this->userColumn] == $userID) {
//                        //$service_link = 'service.php?' . $this->keyColumn . '=' . $ad[$this->keyColumn];
//                        //$this->addStatusMessage(sprintf(_('Service <a href="%s">%s</a> is not used. Do not generate into configuration'), $service_link, $ad[$this->nameColumn]), 'info');
//                    }
                    unset($allData[$adKey]);
                    continue;
                }

                if ($allData[$adKey]['max_check_attempts'] == 0) {
                    $allData[$adKey]['max_check_attempts'] = null;
                }
                if ($allData[$adKey]['check_interval'] == 0) {
                    $allData[$adKey]['check_interval'] = null;
                }
                if ($allData[$adKey]['retry_interval'] == 0) {
                    $allData[$adKey]['retry_interval'] = null;
                }
                if ($allData[$adKey]['notification_interval'] == 0) {
                    $allData[$adKey]['notification_interval'] = null;
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

        if ($this->saveToSQL() && $renameAll) {
            return true;
        }

        return false;
    }

    /**
     * Připraví podřízenu službu
     *
     * @param  Host $host
     * @param  int    $ownerId
     * @return int    ID nově vytvořené služby
     */
    public function fork($host, $ownerId = null)
    {
        if (is_null($ownerId)) {
            $ownerId = \Ease\Shared::user()->getUserID();
        }
        $this->delMember('host_name', $host->getId(), $host->getName());
        $this->saveToSQL();

        $this->setDataValue('parent_id', $this->getId());
        $this->unsetDataValue($this->getKeyColumn());
        $this->setDataValue('public', 0);
        $this->unsetDataValue('tcp_port');
        $this->unsetDataValue('DatSave');
        $this->unsetDataValue('DatCreate');
        $this->setDataValue('action_url', $_SERVER['REQUEST_URI']);
        $this->setDataValue($this->userColumn, $ownerId);
        if (is_object($host->owner)) {
            $this->setDataValue('contacts', $host->owner->getFirstContact());
        }
        $newname = $this->getName().' '.$host->getName();

        $servcount = $this->dblink->queryToCount('SELECT '.$this->getKeyColumn().' FROM '.$this->myTable.' WHERE '.$this->nameColumn.' LIKE \''.$newname.'%\' ');

        if ($servcount) {
            $newname .= ' '.($servcount + 1);
        }

        $this->setDataValue($this->nameColumn, $newname);
        $this->setDataValue('host_name', []);
        $this->addMember('host_name', $host->getId(), $host->getName());

        return $this->saveToSQL();
    }

    /**
     * Přehodí
     * @param type $swapToID
     * @return boolean
     */
    public function swapTo($swapToID)
    {
        $newService    = new Service($swapToID);
        $thisName      = $this->getName();
        $hostsOK       = [];
        $hostsErr      = [];
        $hostsAssigned = [];
        $host          = new Host();

        if (\Ease\Shared::user()->getSettingValue('admin')) {
            $allHosts = $host->getAllFromSQL(NULL,
                [$host->keyColumn, $host->nameColumn, 'platform', 'register'],
                null, $host->nameColumn, $host->keyColumn);
        } else {
            $allHosts = $host->getListing(null, true, ['platform', 'register']);
        }
        $hosts = $this->getDataValue('host_name');
        foreach ($hosts as $hostId => $hostName) {
            if (isset($allHosts[$hostId])) {
                $hostsAssigned[$hostId] = $allHosts[$hostId];
            }
        }

        foreach ($hostsAssigned as $host_id => $hostAssigned) {
            if ($this->delMember('host_name', $host_id,
                    $hostAssigned['host_name']) && $newService->addMember('host_name',
                    $host_id, $hostAssigned['host_name'])) {
                $hostsOK[] = $hostAssigned['host_name'];
            } else {
                $hostsErr[] = $hostAssigned['host_name'];
            }
        }
        if ($this->saveToSQL() && $newService->saveToSQL() && count($hostsOK)) {
            $this->addStatusMessage(sprintf(_('%s was moved from %s/%s to %s'),
                    implode(',', $hostsOK), $this->keyword, $this->getName(),
                    $newService->getName()), 'success');
            return true;
        } else {
            $this->addStatusMessage(sprintf(_(' %s was not moved from %s/%s to %s'),
                    implode(',', $hostsErr), $this->keyword, $this->getName(),
                    $newService->getName()), 'warning');
            return false;
        }
    }

    /**
     * Vrací seznam dostupných položek
     *
     * @param int     $thisID       id jiného než přihlášeného uživatele
     * @param varchar $platform     Testy pro zvolenou platformu
     * @param boolean $withShared   Vracet i nasdílené položky
     * @param array   $extraColumns další vracené položky
     *
     * @return array
     */
    public function getPlatformListing($thisID = null, $platform = 'generic',
                                       $withShared = true, $extraColumns = null)
    {
        if (is_null($thisID)) {
            $thisID = \Ease\Shared::user()->getUserID();
        }
        $columnsToGet = [$this->getKeyColumn(), $this->nameColumn, 'generate',
            $this->myLastModifiedColumn, $this->userColumn];
        if ($this->allowTemplating) {
            $columnsToGet[] = 'register';
            $columnsToGet[] = 'name';
        }

        if (!is_null($extraColumns)) {
            $columnsToGet = array_merge($columnsToGet, $extraColumns);
        }

        if ($this->publicRecords && $withShared) {
            $columnsToGet[] = 'public';

            $data = $this->getColumnsFromSQL($columnsToGet,
                $this->userColumn.'='.$thisID.' OR '.$this->userColumn.' IS NULL OR public=1 '.$this->platformCondition($platform),
                $this->nameColumn, $this->getKeyColumn());
        } else {
            $data = $this->getColumnsFromSQL($columnsToGet,
                $this->ownershipCondition($thisID).$this->platformCondition($platform),
                $this->nameColumn, $this->getKeyColumn());
        }

        return $this->unserializeArrays($data);
    }

    /**
     * SQL Fragment pro volbu platformy sluzby
     * 
     * @param string $platform
     */
    public function platformCondition($platform)
    {
        $sql = '';
        if (!is_null($platform)) {
            $sql = " AND ((`platform` =  '".$platform."') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";
        }
        return $sql;
    }
}
