<?php
/**
 * Třída kontaktu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2015 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Contact extends Configurator
{
    public $myTable = 'contact';

    /**
     * Klíčový sloupeček
     * @var string
     */
    public $myKeyColumn = 'contact_id';
    public $nameColumn  = 'contact_name';
    public $keyword     = 'contact';

    /**
     * Přidat položky register a use ?
     * @var boolean
     */
    public $allowTemplating = true;

    /**
     * Dát tyto položky k dispozici i ostatním ?
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
            'title' => '@Twitter',
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

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-contact';

    /**
     * Vrací mazací tlačítko
     *
     * @param  string                     $name
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('Kontakt'), $addUrl);
    }

    /**
     * vrací nastavení pro výchozí kontakt uživatele
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

    public function checkEmailAddress($email)
    {
        if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",
                $email)) return true;
        return false;
    }

    /**
     * Vytovří odvozený kontakt
     * @param  array $changes
     * @return type
     */
    public function fork($changes)
    {
        $chType = key($changes);
        $chVal  = current($changes);

        switch ($chType) {
            case 'twitter':
                $change = ['address2' => $chVal];
                break;
            case 'jabber':
                if (!$this->checkEmailAddress($chVal)) {
                    $this->addStatusMessage(_('Toto není platná jabberová adresa'),
                        'warning');

                    return false;
                }
                $change = ['address1' => $chVal];
                break;
            case 'email':
                if (!$this->checkEmailAddress($chVal)) {
                    $this->addStatusMessage(_('Toto není platná mailová adresa'),
                        'warning');

                    return false;
                }
                $change = $changes;
                break;
            case 'sms':
                if (!preg_match("/^(\+420)? ?\d{3} ?\d{3} ?\d{3}$/i", $chVal)) {
                    $this->addStatusMessage(_('Toto není platné telefoní číslo'),
                        'warning');

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
        $this->unsetDataValue($this->getmyKeyColumn());
        $this->setDataValue('public', 0);
        $this->unsetDataValue('DatSave');
        $this->unsetDataValue('DatCreate');

        $this->setDataValue($this->userColumn, $ownerId);
        $this->setData($change);

        $newname = $this->getName().' '.$chType;

        $servcount = $this->dblink->queryToCount('SELECT '.$this->getmyKeyColumn().' FROM '.$this->myTable.' WHERE '.$this->nameColumn.' LIKE \''.$newname.'%\' ');

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
        $childs    = $this->dblink->queryToArray('SELECT `alias`,`'.$this->myKeyColumn.'`,`'.$this->nameColumn.'`,`email`,`pager`,`address1`,`address2`  FROM `'.$this->myTable.'` WHERE `parent_id` = '.$this->getId(),
            $this->myKeyColumn);
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

        $contactgroup  = new Engine\IEContactgroup();
        $contactgroups = $this->dblink->queryTo2DArray('SELECT '.$contactgroup->getmyKeyColumn().' FROM '.$contactgroup->myTable.' WHERE members LIKE \'%'.$this->getName().'%\'');
        if (count($contactgroups)) {
            foreach ($contactgroups as $contactgroupID) {
                $contactgroup->loadFromSQL((int) $contactgroupID);
                if ($contactgroup->delMember('members', null, $this->getName())) {
                    if ($contactgroup->saveToSQL()) {
                        $this->addStatusMessage(sprintf(_('Kontakt <strong>%s</strong> byl odebrán ze skupiny <strong>%s</strong>'),
                                $this->getName(), $contactgroup->getName()),
                            'success');
                    }
                } else {
                    $this->addStatusMessage(sprintf(_('Kontakt <strong>%s</strong> nebyl odebrán ze skupiny <strong>%s</strong>'),
                            $this->getName(), $contactgroup->getName()),
                        'warning');
                }
            }
        }


        $service = new Engine\IEService();

        $services = $this->dblink->queryTo2DArray('SELECT '.$service->getmyKeyColumn().' FROM '.$service->myTable.' WHERE contacts LIKE \'%'.$this->getName().'%\'');
        if (count($services)) {
            foreach ($services as $serviceID) {
                $service->loadFromSQL((int) $serviceID);
                if ($service->delMember('contacts', $id)) {
                    if ($service->saveToSQL()) {
                        $this->addStatusMessage(sprintf(_('Kontakt <strong>%s</strong> byl odebrán ze služby <strong>%s</strong>'),
                                $this->getName(), $service->getName()),
                            'success');
                    }
                } else {
                    $this->addStatusMessage(sprintf(_('Kontakt <strong>%s</strong> nebyl odebrán ze služby <strong>%s</strong>'),
                            $this->getName(), $service->getName()), 'warning');
                }
            }
        }

        $host = new Engine\IEHost();

        $hosts = $this->dblink->queryTo2DArray('SELECT '.$host->getmyKeyColumn().' FROM '.$host->myTable.' WHERE contacts LIKE \'%'.$this->getName().'%\'');
        if (count($hosts)) {
            foreach ($hosts as $hostID) {
                $host->loadFromSQL((int) $hostID);
                if ($host->delMember('contacts', $id)) {
                    if ($host->saveToSQL()) {
                        $this->addStatusMessage(sprintf(_('Kontakt <strong>%s</strong> byl odebrán z hosta <strong>%s</strong>'),
                                $this->getName(), $host->getName()), 'success');
                    }
                } else {
                    $this->addStatusMessage(sprintf(_('Kontakt <strong>%s</strong> nebyl odebrán z hosta <strong>%s</strong>'),
                            $this->getName(), $host->getName()), 'warning');
                }
            }
        }

        $this->dblink->exeQuery('DELETE FROM `'.$this->myTable.'` WHERE `parent_id`='.$id);

        return parent::delete($id);
    }

    /**
     * Smazaže kontakt i jeho subkontakty
     *
     * @return boolean
     */
    public function rename($newname)
    {
        $oldname = $this->getName();
        $this->setDataValue($this->nameColumn, $newname);

        if ($this->saveToSQL()) {
            $childs     = $this->getChilds();
            $subcontact = new Engine\IEContact();
            $service    = new Engine\IEService();
            foreach ($childs as $childID => $childInfo) {
                $subcontact->loadFromSQL($childID);
                $type     = $subcontact->getDataValue('alias');
                $subcontact->setDataValue($subcontact->nameColumn,
                    $newname.' '.$type);
                $services = $this->dblink->queryTo2DArray('SELECT '.$service->getmyKeyColumn().' FROM '.$service->myTable.' WHERE contacts LIKE \'%'.$oldname.' '.$type.'%\'');
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
            $this->addStatusMessage(_('Kontakt nelze přejmenovat'), 'warning');
        }
    }
}