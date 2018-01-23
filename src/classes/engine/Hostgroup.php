<?php
/**
 * Hostgroup
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Hostgroup extends Configurator
{
    public $myTable     = 'hostgroup';
    public $KeyColumn = 'hostgroup_id';
    public $keyword     = 'hostgroup';
    public $nameColumn  = 'hostgroup_name';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords   = [
        'hostgroup_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'members' => 'IDLIST',
        'hostgroup_members' => 'IDLIST',
        'notes' => 'TEXT',
        'notes_url' => 'VARCHAR(255)',
        'action_url' => 'VARCHAR(255)',
    ];
    public $keywordsInfo  = [
        'hostgroup_name' => [
            'severity' => 'requied',
            'title' => 'název skupiny', 'required' => true],
        'alias' => [
            'severity' => 'optional',
            'title' => 'alias skupiny', 'required' => true],
        'members' => [
            'severity' => 'basic',
            'title' => 'členské hosty',
            'mandatory' => true,
            'refdata' => [
                'table' => 'host',
                'captioncolumn' => 'host_name',
                'idcolumn' => 'host_id',
                'condition' => ['register' => 1]]
        ],
        'hostgroup_members' => [
            'severity' => 'optional',
            'title' => 'členské skupiny hostů',
            'refdata' => [
                'table' => 'hostgroup',
                'captioncolumn' => 'hostgroup_name',
                'idcolumn' => 'hostgroup_id']
        ],
        'notes' => [
            'severity' => 'basic',
            'title' => 'Poznámka'],
        'notes_url' => [
            'severity' => 'advanced',
            'title' => 'URL externích poznámek'],
        'action_url' => [
            'severity' => 'advanced',
            'title' => 'adresa doplnujících akcí'],
    ];

    public function __construct($itemID = null)
    {

        parent::__construct($itemID);
    }
    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-hostgroup';

    /**
     * Smaže hosta ze všech skupin, již je členem.
     * @param string $hostname
     */
    public function deleteHost($hostname)
    {
        $memberOf = \Ease\Shared::db()->queryToArray('SELECT '.$this->getKeyColumn().','.$this->nameColumn.' FROM '.$this->myTable.' WHERE members LIKE \'%"'.$hostname.'"%\' ',
            $this->getKeyColumn());
        foreach ($memberOf as $groupID => $group) {
            $found = false;
            $this->loadFromSQL($groupID);
            foreach ($this->data['members'] as $ID => $member) {
                if ($member == $hostname) {
                    $found = true;
                    unset($this->data['members'][$ID]);
                    $this->addStatusMessage(sprintf(_('%s was removed from group %s'),
                            $hostname, $group[$this->nameColumn]));
                }
            }
            if ($found) {
                $this->saveToSQL();
            }
        }
    }

    /**
     * Delete button
     *
     * @param  string                     $name
     * @param  string                     $urlAdd URL to add
     *
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('skupinu hostů'), $addUrl);
    }

    public function loadDefault()
    {
        $groupID = \Ease\Shared::db()->queryToValue('SELECT '.$this->getKeyColumn().' FROM '.$this->myTable.' WHERE '.$this->userColumn.'= '.\Ease\Shared::user()->getUserID().' ORDER BY '.$this->getKeyColumn().' DESC LIMIT 1');
        if ($groupID) {
            $this->loadFromSQL((int) $groupID);

            return true;
        }

        return false;
    }

    /**
     * Přejmenuje hosta
     *
     * @param string $oldname
     * @param string $newname
     */
    public function renameHost($oldname, $newname)
    {
        $memberOf = \Ease\Shared::db()->queryToArray('SELECT '.$this->getKeyColumn().','.$this->nameColumn.' FROM '.$this->myTable.' WHERE members LIKE \'%"'.$oldname.'"%\' ',
            $this->getKeyColumn());
        foreach ($memberOf as $groupID => $group) {
            $found = false;
            $this->loadFromSQL($groupID);
            foreach ($this->data['members'] as $id => $member) {
                if ($member == $oldname) {
                    $found                      = true;
                    $this->data['members'][$id] = $newname;
                    $this->addStatusMessage(sprintf(_(' %s was renamed to %s in group %s '),
                            $oldname, $newname, $group[$this->nameColumn]));
                }
            }
            if ($found) {
                $this->saveToSQL();
            }
        }
    }

    /**
     * Vrací pole členů skupiny
     *
     * @return array
     */
    public function getMembers()
    {
        return $this->getDataValue('members');
    }

    /**
     * Smaže hostgrupu i její použití v hostech
     *
     * @param int $id
     * 
     * @return boolean
     */
    function delete($id = null)
    {
        if (isset($id) && ($this->getId() != $id )) {
            $this->loadFromSQL($id);
        } else {
            $id = $this->getId();
        }
        $host  = new Host;
        $hosts = $host->getColumnsFromSQL(
            [$host->keyColumn],
            [
            'hostgroups' => '%'.$this->getName().'%'
            ]
        );
        foreach ($hosts as $hostInfo) {
            $hostId         = intval(current($hostInfo));
            $host->loadFromSQL($hostId);
            $hostgroupNames = $host->getDataValue('hostgroups');
            if ($hostgroupNames) {
                foreach ($hostgroupNames as $hostgroupId => $hostgroupName) {
                    if ($hostgroupId == $this->getId()) {
                        if ($host->delMember('hostgroups', $hostgroupId,
                                $hostgroupName)) {
                            $this->addStatusMessage(sprintf(_('host %s was removed from group %s'),
                                    $host->getName(), $hostgroupName), 'success');
                        } else {
                            $this->addStatusMessage(sprintf(_('host %s was not removed from group %s'),
                                    $host->getName(), $hostgroupName), 'error');
                        }
                    }
                }
            }
        }


        $subgroup  = new Hostgroup;
        $subgroups = $subgroup->getColumnsFromSQL(
            [$subgroup->keyColumn],
            [
            'hostgroup_members' => '%'.$this->getName().'%'
            ]
        );
        foreach ($subgroups as $subgroupInfo) {
            $subgroupId         = intval(current($subgroupInfo));
            $subgroup->loadFromSQL($subgroupId);
            $subgroupgroupNames = $subgroup->getDataValue('hostgroup_members');
            if ($subgroupgroupNames) {
                foreach ($subgroupgroupNames as $subgroupgroupId => $subgroupgroupName) {
                    if ($subgroupgroupId == $this->getId()) {
                        if ($subgroup->delMember('hostgroup_members',
                                $subgroupgroupId, $subgroupgroupName)) {
                            $this->addStatusMessage(sprintf(_('subgroup %s was removed from group %s'),
                                    $subgroup->getName(), $subgroupgroupName),
                                'success');
                        } else {
                            $this->addStatusMessage(sprintf(_('subgroup %s was not removed from group %s'),
                                    $subgroup->getName(), $subgroupgroupName),
                                'error');
                        }
                    }
                }
            }
        }


        return parent::delete($id);
    }

}
