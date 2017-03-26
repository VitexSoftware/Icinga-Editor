<?php

/**
 * Icinga Host Class
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

/**
 * Host representation
 *
 * @author vitex
 */
class Host extends Configurator
{

    public $myTable     = 'host';
    public $keyword     = 'host';
    public $nameColumn  = 'host_name';
    public $myKeyColumn = 'host_id';

    /**
     * Weblink
     * @var string
     */
    public $webLinkColumn = 'action_url';

    /**
     * Add register and use use fields ?
     * @var boolean
     */
    public $allowTemplating = true;

    /**
     * Is this records public ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * Record columns
     * @var array
     */
    public $useKeywords  = [
      'host_name'                    => 'VARCHAR(255)',
      'alias'                        => 'VARCHAR(64)',
      'display_name'                 => 'VARCHAR(64)',
      'address'                      => 'VARCHAR(64)',
      'address6'                     => 'VARCHAR(128)',
      'parents'                      => 'IDLIST',
      'hostgroups'                   => 'IDLIST',
      'check_command'                => 'SELECT',
      'initial_state'                => "RADIO('o','d','u')",
      'max_check_attempts'           => 'SLIDER',
      'check_interval'               => 'INT',
      'retry_interval'               => 'INT',
      'active_checks_enabled'        => 'BOOL',
      'passive_checks_enabled'       => 'BOOL',
      'check_period'                 => 'SELECT',
      'obsess_over_host'             => 'BOOL',
      'check_freshness'              => 'BOOL',
      'freshness_threshold'          => 'INT',
      'event_handler'                => 'SELECT',
      'event_handler_enabled'        => 'BOOL',
      'low_flap_threshold'           => 'INT',
      'high_flap_threshold'          => 'INT',
      'flap_detection_enabled'       => 'BOOL',
      'flap_detection_options'       => "FLAGS('o','d','u')",
      'failure_prediction_enabled'   => 'BOOL',
      'process_perf_data'            => 'BOOL',
      'retain_status_information'    => 'BOOL',
      'retain_nonstatus_information' => 'BOOL',
      'contacts'                     => 'IDLIST',
      'contact_groups'               => 'IDLIST',
      'notification_interval'        => 'INT',
      'first_notification_delay'     => 'INT',
      'notification_period'          => 'SELECT',
      'notification_options'         => "FLAGS('d','u','r','f','s')",
      'notifications_enabled'        => 'BOOL',
      'stalking_options'             => "FLAGS('o','d','u')",
      'notes'                        => 'TEXT',
      'notes_url'                    => 'VARCHAR(128)',
      'action_url'                   => 'VARCHAR(128)',
      'icon_image'                   => 'VARCHAR(64)',
      'icon_image_alt'               => 'VARCHAR(64)',
      'statusmap_image'              => 'VARCHAR(64)',
      '2d_coords'                    => 'VARCHAR(32)',
      'platform'                     => "PLATFORM",
      'host_is_server'               => 'BOOL'
    ];
    public $keywordsInfo = [
      'host_name'                    => [
        'severity' => 'mandatory',
        'title'    => 'Host Name', 'required' => true],
      'alias'                        => [
        'severity' => 'optional',
        'title'    => 'Host Alias', 'required' => true],
      'display_name'                 => [
        'severity' => 'optional',
        'title'    => 'Display Name'],
      'address'                      => [
        'severity'  => 'optional',
        'title'     => 'IPv4 address ', 'mandatory' => true],
      'address6'                     => [
        'severity'  => 'optional',
        'title'     => 'IPv6 address', 'mandatory' => true],
      'parents'                      => [
        'severity' => 'optional',
        'title'    => 'Parents',
        'refdata'  => [
          'table'         => 'host',
          'captioncolumn' => 'host_name',
          'idcolumn'      => 'host_id',
          'public'        => true,
          'condition'     => ['register' => 1]]],
      'hostgroups'                   => [
        'severity' => 'optional',
        'title'    => 'Host Groups',
        'refdata'  => [
          'table'         => 'hostgroup',
          'captioncolumn' => 'hostgroup_name',
          'idcolumn'      => 'hostgroup_id']
      ],
      'check_command'                => [
        'severity' => 'advanced',
        'title'    => 'Check command',
        'severity' => 'optional',
        'refdata'  => [
          'table'         => 'command',
          'captioncolumn' => 'command_name',
          'idcolumn'      => 'command_id',
          'public'        => true,
          'condition'     => ['command_type' => 'check']
        ]
      ],
      'initial_state'                => ['title'    => 'Initial State',
        'severity' => 'advanced',
        'o'        => 'UP',
        'd'        => 'DOWN',
        'u'        => 'UNREACHABLE',
      ],
      'max_check_attempts'           => [
        'title'    => 'maximál check attempts',
        'severity' => 'advanced',
      ],
      'check_interval'               => ['title'    => 'Check interval',
        'severity' => 'advanced',
      ],
      'retry_interval'               => [
        'severity' => 'optional',
        'title'    => 'Retry interval'
      ],
      'active_checks_enabled'        => [
        'severity' => 'advanced',
        'title'    => 'Active Checks enabled'],
      'passive_checks_enabled'       => [
        'severity' => 'advanced',
        'title'    => 'Passive Checks enabled'],
      'check_period'                 => [
        'severity' => 'optional',
        'title'    => 'Check period',
        'refdata'  => [
          'table'         => 'timeperiod',
          'captioncolumn' => 'timeperiod_name',
          'public'        => true,
          'idcolumn'      => 'timeperiod_id']
      ],
      'obsess_over_host'             => [
        'severity' => 'advanced',
        'title'    => 'Obsess over host'],
      'check_freshness'              => [
        'severity' => 'advanced',
        'title'    => 'Check freshness'],
      'freshness_threshold'          => [
        'severity' => 'advanced',
        'title'    => 'Freshness threshold'],
      'event_handler'                => ['title'    => 'Event handler',
        'severity' => 'advanced',
        'refdata'  => [
          'table'         => 'command',
          'captioncolumn' => 'command_name',
          'idcolumn'      => 'command_id',
          'public'        => true,
          'condition'     => ['command_type' => 'handler']
        ]
      ],
      'event_handler_enabled'        => [
        'severity' => 'advanced',
        'title'    => 'Event handler enabled'],
      'low_flap_threshold'           => [
        'severity' => 'advanced',
        'title'    => 'Low flap treshold'],
      'high_flap_threshold'          => [
        'severity' => 'advanced',
        'title'    => 'High flap threshold'],
      'flap_detection_enabled'       => [
        'severity' => 'advanced',
        'title'    => 'Flap detection enabled'],
      'flap_detection_options'       => [
        'severity' => 'advanced',
        'title'    => 'Flap detection options',
        'o'        => 'Up',
        'd'        => 'Down',
        'u'        => 'Unreachable',
      ],
      'failure_prediction_enabled'   => [
        'severity' => 'advanced',
        'title'    => 'Failure prediction enabled'],
      'process_perf_data'            => [
        'severity' => 'advanced',
        'title'    => 'Process perf data'],
      'retain_status_information'    => [
        'severity' => 'advanced',
        'title'    => 'retain_status_information'],
      'retain_nonstatus_information' => [
        'severity' => 'advanced',
        'title'    => 'Retain nonstatus information'],
      'contacts'                     => [
        'severity' => 'optional',
        'title'    => 'Contacts',
        'refdata'  => [
          'table'         => 'contact',
          'captioncolumn' => 'contact_name',
          'idcolumn'      => 'contact_id']],
      'contact_groups'               => [
        'severity' => 'optional',
        'title'    => 'Contact Groups',
        'refdata'  => [
          'table'         => 'contactgroup',
          'captioncolumn' => 'contactgroup_name',
          'idcolumn'      => 'contactgroup_id']
      ],
      'notification_interval'        => [
        'severity' => 'optional',
        'title'    => 'Notification interval'],
      'first_notification_delay'     => [
        'severity' => 'advanced',
        'title'    => 'First notification delay'],
      'notification_period'          => [
        'severity' => 'optional',
        'title'    => 'Notification period',
        'required' => true,
        'refdata'  => [
          'table'         => 'timeperiod',
          'captioncolumn' => 'timeperiod_name',
          'public'        => true,
          'idcolumn'      => 'timeperiod_id']
      ],
      'notification_options'         => [
        'severity' => 'advanced',
        'title'    => 'notification options',
        'd'        => 'DOWN',
        'u'        => 'UNREACHABLE',
        'r'        => 'RECOVERY',
        'f'        => 'FLAPPING',
        's'        => 'SCHEDULED DOWNTIME'
      ],
      'notifications_enabled'        => [
        'severity' => 'optional',
        'title'    => 'Notifications enabled'],
      'stalking_options'             => [
        'severity' => 'advanced',
        'title'    => 'Stalking options',
        'o'        => 'UP',
        'd'        => 'DOWN',
        'u'        => 'UNREACHABLE'],
      'notes'                        => [
        'severity'  => 'basic',
        'title'     => 'Notes', 'mandatory' => true],
      'notes_url'                    => [
        'severity' => 'advanced',
        'title'    => 'Notes url'],
      'action_url'                   => [
        'severity' => 'advanced',
        'title'    => 'Action url'],
      'icon_image'                   => [
        'severity'  => 'advanced',
        'title'     => 'Icon Image', 'mandatory' => true],
      'icon_image_alt'               => [
        'severity' => 'advanced',
        'title'    => 'Icon image title'],
      'statusmap_image'              => [
        'severity' => 'advanced',
        'title'    => 'Statusmap image'],
      '2d_coords'                    => [
        'severity' => 'advanced',
        'title'    => 'Statusmap coordinates'],
      'platform'                     => [
        'severity'  => 'basic',
        'title'     => 'Platform', 'mandatory' => true],
      'host_is_server'               => [
        'severity' => 'advanced',
        'title'    => 'Host Is Server'],
    ];

    /**
     * Column with icon image
     * @var string 
     */
    public $iconImageColumn = 'icon_image';

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
            $this->loadFromSQL($id);
        }

        $hostGroup = new Hostgroup();
        $hostGroup->deleteHost($this->getName());

        $delAll           = true;
        $service          = new Service();
        $servicesAssigned = $service->dblink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ' FROM ' . $service->myTable . ' WHERE ' . 'host_name' . ' LIKE \'%"' . $this->getName() . '"%\'', $service->myKeyColumn);
        foreach ($servicesAssigned as $serviceID => $serviceInfo) {
            $service->loadFromSQL($serviceID);
            $service->delHostName($this->getId(), $this->getName());
            if (!$service->saveToSQL()) {
                $this->addStatusMessage(sprintf(_('Unregister %s from service %s error'), $this->getName(), $service->getName()), 'Error');
                $delAll = false;
            }
        }

        $childsOfMe = $this->dblink->queryToArray('SELECT ' . $this->myKeyColumn . ',' . $this->nameColumn . ' FROM ' . $this->myTable . ' WHERE parents ' .
            ' LIKE \'%' . $this->getName() . '%\'', $this->myKeyColumn);

        foreach ($childsOfMe as $chid_id => $child_info) {
            $child = new Host($chid_id);

            if ($child->delMember('parents', $this->getId(), $this->getName()) && $child->saveToSQL()) {
                $this->addStatusMessage(sprintf(_('%s not an parent of %s'), $this->getName(), $child->getName()), 'success');
            } else {
                $this->addStatusMessage(sprintf(_('%s is still parent of %s'), $this->getName(), $child->getName()), 'warning');
            }
        }


        if ($delAll) {
            return parent::delete();
        }

        return false;
    }

    /**
     * Check all columns
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
            unset($allData[$hostID]['host_is_server']);
            if (!intval($hostInfo['register'])) {
                continue;
            }
            if (intval($hostInfo['user_id'])) {
                if (is_object($this->owner)) {
                    if ($this->owner->getUserID() != $hostInfo['user_id']) {
                        $this->owner->loadFromSQL((int) $hostInfo['user_id']);
                    }
                } else {
                    $this->owner = new \Icinga\Editor\User((int) $hostInfo['user_id']);
                }

                $hostOwnerLogin = $this->owner->getUserLogin();
                /* Every host must  have contact to login to see it */
                if (is_array($hostInfo['contacts'])) {
                    if (array_search($hostOwnerLogin, $hostInfo['contacts']) === false) {
                        $allData[$hostID]['contacts'][] = $hostOwnerLogin;
                    }
                } else {
                    $allData[$hostID]['contacts'] = [$hostOwnerLogin];
                }
            } else {
                $this->addStatusMessage(_('Host without owner') . ': #' . $hostInfo[$this->myKeyColumn] . ': ' . $hostInfo[$this->nameColumn], 'warning');
            }
        }

        return $allData;
    }

    /**
     * Scan & assifgn TCP services
     * @return int počet sledovaných
     */
    public function autoPopulateServices()
    {
        $scanner = new \Icinga\Editor\PortScanner($this);

        return $scanner->assignServices();
    }

    /**
     * Rename host and dependencies
     * @param string $newname
     */
    public function rename($newname)
    {
        $oldname = $this->getName();
        $this->setDataValue($this->nameColumn, $newname);

        $hostGroup = new Hostgroup();
        $hostGroup->renameHost($oldname, $newname);

        $renameAll        = true;
        $service          = new Service();
        $servicesAssigned = $service->dblink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ' FROM ' . $service->myTable . ' WHERE ' . 'host_name' . ' LIKE \'%"' . $oldname . '"%\'', $service->myKeyColumn);
        foreach ($servicesAssigned as $serviceID => $serviceInfo) {
            $service->loadFromSQL($serviceID);
            $service->renameHostName($this->getId(), $newname);
            if (!$service->saveToSQL()) {
                $this->addStatusMessage(sprintf(_('Error renaming %s within service %s'), $this->getName(), $service->getName()), $Type);
                $renameAll = false;
            }
        }

        $childsAssigned = $this->dblink->queryToArray('SELECT ' . $this->myKeyColumn . ',' . $this->nameColumn . ' FROM ' . $this->myTable . ' WHERE ' . 'parents' . ' LIKE \'%"' . $oldname . '"%\'', $this->myKeyColumn);
        foreach ($childsAssigned as $chid_id => $child_info) {
            $child = new Host($chid_id);
            $child->delMember('parents', $this->getId(), $oldname);
            $child->addMember('parents', $this->getId(), $newname);
            $child->updateToSQL();
        }

        if ($this->saveToSQL() && $renameAll) {
            return true;
        }

        return false;
    }

    /**
     * Check icon, download, convert an use as host icon
     */
    public function favToIcon()
    {
        $icoUrl    = false;
        $baseUrl   = 'http://' . $this->getDataValue('host_name') . '/';
        $indexpage = @file_get_contents($baseUrl);
        $icoUrls   = [];
        if (strlen($indexpage)) {
            $dom   = new \DOMDocument();
            @$dom->loadHTML($indexpage);
            $links = $dom->getElementsByTagName('link');
            foreach ($links as $link) {
                $urlLink = false;
                if (isset($link->attributes)) {
                    foreach ($link->attributes as $atribut) {
                        if (isset($atribut->name)) {
                            if (($atribut->name == 'rel') && stristr($atribut->value, 'icon')) {
                                $urlLink = true;
                                $rel     = $atribut->value;
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



        $tmpfilename = sys_get_temp_dir() . '/' . \Ease\Sand::randomString();


        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $icoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $downloaded = curl_exec($ch);
        curl_close($ch);

        file_put_contents($tmpfilename, $downloaded);



        if (\Icinga\Editor\UI\IconSelector::imageTypeOK($tmpfilename)) {

            \Ease\Shared::webPage()->addStatusMessage(sprintf(_('Nalezena ikona %s'), $icoUrl), 'success');

            $newicon = \Icinga\Editor\UI\IconSelector::saveIcon($tmpfilename, $this);

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

    /**
     * Draw host html representation
     */
    function draw()
    {
        echo new \Icinga\Editor\UI\HostIcon($this);
    }

    /**
     * Obtain services that checks this host
     *
     * @return array Service name listing
     */
    public function getServices()
    {
        $services = [];

        $service          = new Service();
        $servicesAssigned = $service->dblink->queryToArray('SELECT ' . $service->myKeyColumn . ',' . $service->nameColumn . ' FROM ' . $service->myTable . ' WHERE host_name LIKE \'%"' . $this->getName() . '"%\'', $service->myKeyColumn);
        if ($servicesAssigned) {
            foreach ($servicesAssigned as $service_id => $service_info) {
                $services[$service_id] = $service_info[$service->nameColumn];
            }
        }
        return $services;
    }

    /**
     * Info Block
     * 
     * @return \Ease\Html\Div
     */
    public function getInfoBlock()
    {
        $block = parent::getInfoBlock();
        $block->addDef(_('Alias'), [new \Icinga\Editor\UI\HostIcon($this), $this->getDataValue('alias')]);
        $block->addDef(_('Platform'),
            new \Icinga\Editor\UI\PlatformIcon($this->getDataValue('platform')));

        $parents = $this->getDataValue('parents');
        if ($parents) {
            foreach ($parents as $pId => $pName) {
                $parents[$pId] = '<a href="host.php?host_id=' . $pId . '">' . $pName . '</a>';
            }
            $block->addDef(_('Parent'), implode(',', $parents));
        }

        $block->addDef(_('Sensor Configuration'), $this->sensorStatusLabel());

        return $block;
    }

    /**
     * Obtain label with status of sensor deployment
     *
     * @param int $status_code Kód nasazení senzoru 2: aktuální 1: zastaralý 0:nenasazeno
     * @return \\Ease\TWB\Label
     */
    function sensorStatusLabel($status_code = null)
    {

        $status = null;
        if (is_null($status_code)) {
            $status_code = $this->getSensorStatus();
        }

        switch ($status_code) {
            case 2:
                $status = new \Ease\TWB\Label('success', _('Actual'));
                break;
            case 1:
                $status = new \Ease\TWB\Label('warning', _('Obsolete'));
                break;
            case 0:
            default :
                $status = new \Ease\TWB\Label('danger', _('Not installed'));
                break;
        }
        return $status;
    }

    /**
     * Obtain sensor deploy status
     *
     * @return int 2: Actual 1: Obsolete 0: Not installed
     */
    function getSensorStatus()
    {
        $status = null;
        $hash   = $this->getDataValue('config_hash');
        if ($hash) {
            if ($this->getConfigHash() == $hash) {
                $status = 2; //All OK
            } else {
                $status = 1; //Obsolete configuration
            }
        } else {
            $status = 0; //Not installed
        }
        return $status;
    }

    /**
     * Obtain actual host/services configuration hash
     */
    function getConfigHash()
    {
        $configuration    = [];
        $service          = new Service;
        $servicesAssigned = $service->dblink->queryToArray('SELECT `' . $service->getmyKeyColumn() . '` FROM ' . $service->myTable . ' WHERE host_name LIKE \'%"' . $this->getName() . '"%\'', $service->myKeyColumn);
        foreach ($servicesAssigned as $serviceAssigned) {
            $service->loadFromSQL((int) $serviceAssigned[$service->myKeyColumn]);
            $service->unsetDataValue('display_name'); //Položky které se mohou měnit bez nutnosti aktualizovat senzor
            $service->unsetDataValue('service_description');
            $service->unsetDataValue('host_name');
            $service->unsetDataValue('hostgroup_name');
            $service->unsetDataValue('notes');
            $service->unsetDataValue('notes_url');
            $service->unsetDataValue('action_url');
            $service->unsetDataValue('icon_image');
            $service->unsetDataValue('icon_image_alt');
            $service->unsetDataValue('public');
            $service->unsetDataValue('user_id');
            $service->unsetDataValue($service->myLastModifiedColumn);
            $configuration[] = $service->getEffectiveCfg();
        }
        return hash('md5', $this->getName() . serialize($configuration));
    }

    /**
     * Add host to service
     *
     * @param string $column     column name
     * @param int    $memberID   host ID
     * @param string $memberName host Name
     */
    function addMember($column, $memberID, $memberName)
    {
        if ($column == 'parents') {
            if ($memberName == $this->getName()) {
                $this->addStatusMessage(_('Host can not by its own parent'), 'warning');
                return null;
            }
        }
        return parent::addMember($column, $memberID, $memberName);
    }

    /**
     * Create new Host member of user's default hostgroup
     *
     * @param array $data Host data
     *
     * @return int|null ID of new record or null in case of error
     */
    public function insertToSQL($data = null)
    {
        if (!is_null($data)) {
            $this->takeData($data);
        }
        $hostgroup = new Hostgroup(\Ease\Shared::user()->getUserLogin());
        $this->addMember('hostgroups', $hostgroup->getId(), $hostgroup->getName());

        $this->setDataValue('hostgroups', serialize($this->getDataValue('hostgroups')));
        return parent::insertToSQL();
    }

    /**
     * Take data to current object add process checkgroups
     * Set default platform to generic if not set
     *
     * @param array  $data       asociativní pole dat
     * @param string $dataPrefix prefix datové skupiny
     *
     * @return int
     */
    function takeData($data, $dataPrefix = null)
    {
        if (!isset($data['platform'])) {
            $data['platform'] = 'generic';
        }
        return parent::takeData($data, $dataPrefix);
    }

}
