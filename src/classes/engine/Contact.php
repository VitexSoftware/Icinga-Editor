<?php
/**
 * Contact
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2015 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Contact extends Configurator
{
    public $myTable = 'contact';

    /**
     * Key Column
     * @var string
     */
    public $keyColumn  = 'contact_id';
    public $nameColumn = 'contact_name';
    public $keyword    = 'contact';

    /**
     * Add register and use columns ?
     * @var boolean
     */
    public $allowTemplating = true;

    /**
     * Can bee this records public ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords   = [
        'contact_name' => 'VARCHAR(128)',
        'alias' => 'VARCHAR()',
        'contactgroups' => 'VARCHAR(255)',
        'host_notifications_enabled' => 'BOOL',
        'service_notifications_enabled' => 'BOOL',
        'host_notification_period' => 'SELECT',
        'service_notification_period' => 'SELECT',
        'host_notification_options' => "FLAGS('d','u','r','f','s','n')",
        'service_notification_options' => "FLAGS('w','u','c','r','f','s','n')",
        'host_notification_commands' => 'IDLIST',
        'service_notification_commands' => 'IDLIST',
        'email' => 'VARCHAR(128)',
        'pager' => 'VARCHAR(64)',
        'address1' => 'VARCHAR(255)',
        'address2' => 'VARCHAR(255)',
        'can_submit_commands' => 'BOOL',
        'retain_status_information' => 'BOOL',
        'retain_nonstatus_information' => 'BOOL'
    ];
    public $keywordsInfo  = [
        'contact_name' => [
            'title' => 'název kontaktu',
            'severity' => 'mandatory',
            'required' => true],
        'alias' => [
            'title' => 'alias',
            'severity' => 'mandatory'
        ],
        'contactgroups' => [
            'severity' => 'optional',
            'title' => 'kontaktní skupiny',
            'hidden' => true
        ],
        'host_notifications_enabled' => [
            'severity' => 'basic',
            'title' => 'oznamovat zprávy hostů',
        ],
        'service_notifications_enabled' => [
            'severity' => 'basic',
            'title' => 'oznamovat zprávy služeb',
        ],
        'host_notification_period' => [
            'severity' => 'optional',
            'title' => 'notifikační perioda hostů',
            'required' => true,
            'refdata' => [
                'table' => 'timeperiod',
                'captioncolumn' => 'timeperiod_name',
                'public' => true,
                'idcolumn' => 'timeperiod_id']
        ],
        'service_notification_period' => [
            'severity' => 'optional',
            'title' => 'notifikační perioda služeb',
            'required' => true,
            'refdata' => [
                'table' => 'timeperiod',
                'captioncolumn' => 'timeperiod_name',
                'public' => true,
                'idcolumn' => 'timeperiod_id']
        ],
        'host_notification_options' => [
            'severity' => 'advanced',
            'title' => 'možnosti oznamování hostů',
            'required' => true,
            'd' => 'notify on DOWN host states',
            'u' => 'notify on UNREACHABLE host states',
            'r' => 'notify on host recoveries (UP states)',
            'f' => 'notify when the host starts and stops flapping',
            's' => 'send notifications when host or service scheduled downtime starts and ends',
            'n' => 'nic neoznamovat'
        ],
        'service_notification_options' => [
            'severity' => 'advanced',
            'title' => 'možnosti oznamování služeb',
            'required' => true,
            'w' => 'notify on WARNING service states',
            'u' => 'notify on UNKNOWN service states',
            'c' => 'notify on CRITICAL service states',
            'r' => 'notify on service recoveries (OK states)',
            'f' => 'notify when the service starts and stops flapping',
            's' => 'send notifications when host or service scheduled downtime starts and ends',
            'n' => 'nic neoznamovat'
        ],
        'host_notification_commands' => [
            'severity' => 'advanced',
            'title' => 'způsob oznamování událostí hosta',
            'required' => true,
            'refdata' => [
                'table' => 'command',
                'captioncolumn' => 'command_name',
                'idcolumn' => 'command_id',
                'public' => true,
                'condition' => ['command_type' => 'notify']
            ]
        ],
        'service_notification_commands' => [
            'severity' => 'advanced',
            'title' => 'způsob oznamování událostí služby',
            'required' => true,
            'refdata' => [
                'table' => 'command',
                'captioncolumn' => 'command_name',
                'idcolumn' => 'command_id',
                'public' => true,
                'condition' => ['command_type' => 'notify']
            ]
        ],
        'email' => [
            'severity' => 'optional',
            'title' => 'mailová adresa',
            'mandatory' => true
        ],
        'pager' => [
            'severity' => 'optional',
            'title' => 'číslo pro příjem SMS',
            'mandatory' => true
        ],
        'address1' => [
            'severity' => 'optional',
            'title' => 'jabberová adresa',
            'mandatory' => true
        ],
        'address2' => [
            'severity' => 'optional',
            'title' => 'Redmine',
            'mandatory' => true
        ],
        'can_submit_commands' => [
            'severity' => 'advanced',
            'title' => 'právo zasílat externí příkazy ?'
        ],
        'retain_status_information' => [
            'severity' => 'advanced',
            'title' => 'uchovávat stavové informace'
        ],
        'retain_nonstatus_information' => [
            'severity' => 'advanced',
            'title' => 'uchovávat nestavové informace'
        ]
    ];

    public function __construct($itemID = null)
    {
        $this->keywordsInfo['contact_name']['title'] = _('Contact Name');

        $this->keywordsInfo['contactgroups']['title']                 = _('Contact Groups');
        $this->keywordsInfo['host_notifications_enabled']['title']    = _('Notify hosts events');
        $this->keywordsInfo['service_notifications_enabled']['title'] = _('Notify service events');
        $this->keywordsInfo['host_notification_period']['title']      = _('Host notification period');
        $this->keywordsInfo['service_notification_period']['title']   = _('Service notification period');
        $this->keywordsInfo['host_notification_options']['title']     = _('Host notification options');
        $this->keywordsInfo['service_notification_options']['title']  = _('Service notification options');
        $this->keywordsInfo['host_notification_commands']['title']    = _('Host notification commands');
        $this->keywordsInfo['service_notification_commands']['title'] = _('Service notification commands');
        $this->keywordsInfo['email']['title']                         = _('Email Address');
        $this->keywordsInfo['pager']['title']                         = _('SMS Number');
        $this->keywordsInfo['address1']['title']                      = _('Jabber Address');
        $this->keywordsInfo['address2']['title']                      = _('Redmine');
        $this->keywordsInfo['can_submit_commands']['title']           = _('Can submit commands');
        $this->keywordsInfo['retain_status_information']['title']     = _('Retain status information');
        $this->keywordsInfo['retain_nonstatus_information']['title']  = _('Retain nostatus information');
        parent::__construct($itemID);
    }
    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-contact';

    /**
     * Delete button
     *
     * @param  string                     $name
     * @param  string                     $urlAdd URL to add
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('Contacts'), $addUrl);
    }

    /**
     * Initial contact setup
     *
     * @return array Pole nastavení
     */
    public static function ownContactData()
    {
        $oUser = \Ease\Shared::user();

        return [
            'use' => 'generic-contact',
            'contact_name' => $oUser->getUserLogin(),
            'alias' => $oUser->getUserName(),
            'email' => $oUser->getUserEmail(),
            'host_notification_commands' => ['notify-host-by-email'],
            'service_notification_commands' => ['notify-service-by-email'],
            'generate' => TRUE,
            'user_id' => $oUser->getUserID()
        ];
    }

    /**
     * Create derived contact
     *
     * @param  array $changes
     *
     * @return int SQL record ID
     */
    public function fork($changes)
    {
        $chType = key($changes);
        $chVal  = current($changes);

        switch ($chType) {
            case 'redmine':
                $change = [
                    'address2' => $chVal, 
                    'host_notification_commands' => 'host-notify-by-redmine',
                    'service_notification_commands' => 'service-notify-by-redmine'
                    ];
                break;
            case 'jabber':
                if (!filter_var($chVal, FILTER_VALIDATE_EMAIL)) {
                    $this->addStatusMessage(_('Invalid Jabber Address'),
                        'warning');

                    return false;
                }
                $change = ['address1' => $chVal];
                break;
            case 'email':
                if (!filter_var($chVal, FILTER_VALIDATE_EMAIL)) {
                    $this->addStatusMessage(_('Invalid email address'),
                        'warning');

                    return false;
                }
                $change = $changes;
                break;
            case 'sms':
                if (!preg_match("/^(\+420)? ?\d{3} ?\d{3} ?\d{3}$/i", $chVal)) {
                    $this->addStatusMessage(_('Invalid phone number'), 'warning');

                    return false;
                }
                $change = ['pager' => $chVal];
                break;
            default :
                $change = $changes;
                break;
        }

        $ownerId = \Ease\Shared::user()->getUserID();

        $this->setDataValue('alias', $chType);
        $this->setDataValue('parent_id', $this->getId());
        $this->unsetDataValue($this->getKeyColumn());
        $this->setDataValue('public', 0);
        $this->unsetDataValue('DatSave');
        $this->unsetDataValue('DatCreate');

        $this->setDataValue($this->userColumn, $ownerId);
        $this->setData($change);

        $newname = $this->getName().' '.$chType;

        $servcount = $this->dblink->queryToCount('SELECT '.$this->getKeyColumn().' FROM '.$this->myTable.' WHERE '.$this->nameColumn.' LIKE \''.$newname.'%\' ');

        if ($servcount) {
            $newname .= ' '.($servcount + 1);
        }

        $this->setDataValue($this->nameColumn, $newname);

        return $this->saveToSQL();
    }

    /**
     * Vrací seznam podřízených kontaktů a jejich typů
     * @return array pole ID=>typ
     */
    public function getChilds()
    {
        $subchilds = [];
        $childs    = $this->dblink->queryToArray('SELECT `alias`,`'.$this->keyColumn.'`,`'.$this->nameColumn.'`,`email`,`pager`,`address1`,`address2`  FROM `'.$this->myTable.'` WHERE `parent_id` = '.$this->getId(),
            $this->keyColumn);
        foreach ($childs as $childID => $childInfo) {
            $subchilds[$childID]['type']    = $childInfo['alias'];
            $subchilds[$childID]['contact'] = $childInfo['email'].$childInfo['pager'].$childInfo['address1'].$childInfo['address2'];
        }

        return $subchilds;
    }

    /**
     * Smaže kontakt i jeho subkontakty
     *
     * @return boolean
     */
    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        } else {
            if ($id != $this->getId()) {
                $this->loadFromSQL($id);
            }
        }

        $childs = $this->getChilds();
        if ($childs) {
            $parent = $id;
            foreach ($childs as $child_id => $child) {
                $this->delete($child_id);
            }
            $this->loadFromSQL($parent);
            $id = $parent;
        }

        $contactgroup  = new Contactgroup();
        $contactgroups = $this->dblink->queryTo2DArray('SELECT '.$contactgroup->getKeyColumn().' FROM '.$contactgroup->myTable.' WHERE members LIKE \'%'.$this->getName().'%\'');
        if (count($contactgroups)) {
            foreach ($contactgroups as $contactgroupID) {
                $contactgroup->loadFromSQL((int) $contactgroupID);
                if ($contactgroup->delMember('members', null, $this->getName())) {
                    if ($contactgroup->saveToSQL()) {
                        $this->addStatusMessage(sprintf(_('Contact %s was removed from group %s'),
                                $this->getName(), $contactgroup->getName()),
                            'success');
                    }
                } else {
                    $this->addStatusMessage(sprintf(_('Contact %s was not removed from group %s'),
                            $this->getName(), $contactgroup->getName()),
                        'warning');
                }
            }
        }


        $service = new Service();

        $services = $this->dblink->queryTo2DArray('SELECT '.$service->getKeyColumn().' FROM '.$service->myTable.' WHERE contacts LIKE \'%'.$this->getName().'%\'');
        if (count($services)) {
            foreach ($services as $serviceID) {
                $service->loadFromSQL((int) $serviceID);
                if ($service->delMember('contacts', $id)) {
                    if ($service->saveToSQL()) {
                        $this->addStatusMessage(sprintf(_('Contact %s was removed from service %s'),
                                $this->getName(), $service->getName()),
                            'success');
                    }
                } else {
                    $this->addStatusMessage(sprintf(_('Contact %s was not removed from service %s'),
                            $this->getName(), $service->getName()), 'warning');
                }
            }
        }

        $host = new Host();

        $hosts = $this->dblink->queryTo2DArray('SELECT '.$host->getKeyColumn().' FROM '.$host->myTable.' WHERE contacts LIKE \'%'.$this->getName().'%\'');
        if (count($hosts)) {
            foreach ($hosts as $hostID) {
                $host->loadFromSQL((int) $hostID);
                if ($host->delMember('contacts', $id)) {
                    if ($host->saveToSQL()) {
                        $this->addStatusMessage(sprintf(_('Contact %s was removed from host %s'),
                                $this->getName(), $host->getName()), 'success');
                    }
                } else {
                    $this->addStatusMessage(sprintf(_('Contact %s was not removed from host %s'),
                            $this->getName(), $host->getName()), 'warning');
                }
            }
        }

        $this->dblink->exeQuery('DELETE FROM `'.$this->myTable.'` WHERE `parent_id`='.$id);

        return parent::delete($id);
    }

    /**
     * Remove contact and its subcontacts
     *
     * @return boolean
     */
    public function rename($newname)
    {
        $oldname = $this->getName();
        $this->setDataValue($this->nameColumn, $newname);

        if ($this->saveToSQL()) {
            $childs     = $this->getChilds();
            $subcontact = new Contact();
            $service    = new Service();
            foreach ($childs as $childID => $childInfo) {
                $subcontact->loadFromSQL($childID);
                $type     = $subcontact->getDataValue('alias');
                $subcontact->setDataValue($subcontact->nameColumn,
                    $newname.' '.$type);
                $services = $this->dblink->queryTo2DArray('SELECT '.$service->getKeyColumn().' FROM '.$service->myTable.' WHERE contacts LIKE \'%'.$oldname.' '.$type.'%\'');
                if (count($services)) {
                    foreach ($services as $serviceID) {
                        $service->loadFromSQL((int) $serviceID);
                        if ($service->delMember('contacts', $id)) {
                            $service->addMember('contacts', $id,
                                $newname.' '.$type);
                            $service->saveToSQL();
                        }
                    }
                }
                $subcontact->saveToSQL();
            }
        } else {
            $this->addStatusMessage(_('Cannot rename contact'), 'warning');
        }
    }
}
