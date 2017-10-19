<?php

namespace Icinga\Editor\Engine;

/**
 * Icinga UserGroup Configurator
 */
class UserGroup extends Configurator
{
    public $keyword     = 'usergroup';
    public $myTable     = 'user_group';
    public $nameColumn  = 'usergroup_name';
    public $myKeyColumn = 'usergroup_id';
    public $userColumn  = 'group_boss';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords   = [
        'usergroup_name' => 'VARCHAR(64)',
        'members' => ''
    ];
    public $keywordsInfo  = [
        'usergroup_name' => ['required' => true]
    ];

    /**
     * členové skupiny
     * @var array
     */
    public $members = [];

    /**
     * Skupina uživatelů
     *
     * @param string|id $id ID or group name
     */
    public function __construct($id = null)
    {
        parent::__construct();

        $this->keywordsInfo['usergroup_name']['title'] = _('Group Name');
        $this->keywordsInfo['members']['title']        = _('Members');

        unset($this->useKeywords['generate']);
        unset($this->keywordsInfo['generate']);

        if (is_int($id)) {
            $this->loadFromSQL($id);
        } else {
            if (strlen($id)) {
                $this->setmyKeyColumn($this->nameColumn);
                $this->loadFromSQL($id);
                $this->restoreObjectIdentity();
            }
        }
        $id = $this->getMyKey();
        if ($id) {
            $this->loadMembers();
        }
    }

    function loadMembers($id = null)
    {
        if (is_null($id)) {
            $id = $this->getMyKey();
        }
        $this->members = $this->getMembers($id);
    }

    /**
     * Read group members from database
     *
     * @param int $id ID skupiny
     * @return array
     */
    public function getMembers($id = null)
    {
        if (is_null($id)) {
            $id = $this->getMyKey();
        }
        $members = [];
        $mmbrs   = $this->dblink->queryToArray('SELECT id,login FROM user WHERE id IN ( SELECT user_id FROM user_to_group WHERE group_id='.$id.' )',
            'id');
        foreach ($mmbrs as $mId => $userInfo) {
            $members[$mId] = $userInfo['login'];
        }
        return $members;
    }

    /**
     * Group Member select
     *
     * @return \Ease\TWB\Panel
     */
    public function memberSelector()
    {
        $users      = [];
        $unassigned = [];
        if ($this->getMyKey()) {
            $ua = $this->dblink->queryToArray('SELECT id,login FROM user WHERE id NOT IN ( SELECT user_id FROM user_to_group WHERE group_id='.$this->getMyKey().' )',
                'id');
            foreach ($ua as $id => $userInfo) {
                $unassigned[$id] = $userInfo['login'];
            }
        }

        foreach ($this->members as $userId => $login) {
            $users[$userId] = new \Ease\TWB\ButtonDropdown(
                $login, 'success', 'xs',
                [
                new \Ease\Html\ATag('?action=delmember&usergroup_id='.$this->getMyKey().'&member_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('minus').' '._('Remove from group')),
                new \Ease\Html\ATag('userinfo.php?user_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('wrench').' '._('Edit'))
            ]);
        }

        foreach ($unassigned as $userId => $login) {
            $users[$userId] = new \Ease\TWB\ButtonDropdown(
                $login, 'inverse', 'xs',
                [
                new \Ease\Html\ATag('?action=addmember&usergroup_id='.$this->getMyKey().'&member_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('plus').' '._('Add to group')),
                new \Ease\Html\ATag('userinfo.php?user_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('wrench').' '._('Edit'))
            ]);
        }


        return new \Ease\TWB\Panel(_('UserGroup Members'), 'default', $users);
    }

    /**
     * Add Member to current UserGroup
     *
     * @param int $memberID
     * @return boolean
     */
    public function addMember($column, $memberID, $memberName = null)
    {
        $this->dblink->exeQuery('INSERT INTO user_to_group VALUES('.$memberID.','.$this->getMyKey().')');
        $added = $this->dblink->numRows;
        if ($added) {
            $this->loadMembers();
            return $added;
        } else {
            return false;
        }
    }

    /**
     * Remove Member from current UserGroup
     *
     * @param int $memberID
     * @return boolean
     */
    public function delMember($column, $memberID = null, $memberName = null)
    {
        if ($memberID) {
            $this->dblink->exeQuery('DELETE FROM user_to_group WHERE user_id='.$memberID.' AND group_id='.$this->getMyKey());
            $removed = $this->dblink->numRows;

            if ($removed) {
                $this->loadMembers();
                return $removed;
            } else {
                return false;
            }
        }
    }

    /**
     * Delete usergroup by id
     *
     * @param int $id
     */
    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }
        if (parent::delete($id)) {
            $this->dblink->exeQuery('DELETE FROM user_to_group WHERE group_id='.$id);
        }
    }

    /**
     * Prepare raw data for datagrid
     *
     * @param array $row
     * @return array
     */
    function htmlizeRow($row)
    {
        $row   = parent::htmlizeRow($row);
        $mmbrs = $this->getMembers($row['usergroup_id']);
        if (count($mmbrs)) {
            foreach ($mmbrs as $mId => $mLogin) {
                $mmbrs[$mId] = '<a href="userinfo.php?user_id='.$mId.'">'.$mLogin.'</a>';
            }
            $row['members'] = implode(',', $mmbrs);
        }
        return $row;
    }

    /**
     * Remove user from all UserGroups
     *
     * @param int $id UserID
     */
    public function delUser($id)
    {
        $this->dblink->exeQuery('DELETE FROM user_to_group WHERE user_id='.$id);
    }

}
