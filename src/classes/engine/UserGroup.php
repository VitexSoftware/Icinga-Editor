<?php

namespace Icinga\Editor\Engine;

/**
 * Uživatel Icinga Editoru
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
        'usergroup_name' => ['title' => 'název skupiny', 'required' => true],
        'members' => ['title' => 'členové']
    ];

    /**
     * členové skupiny
     * @var array
     */
    public $members = [];

    /**
     * Skupina uživatelů
     *
     * @param string|id $id ID nebo jméno skupiny
     */
    public function __construct($id = null)
    {
        parent::__construct();

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
     * Načte členy skupiny z DB
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
     * Volba členů skupiny
     *
     * @return \\Ease\TWB\Panel
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
                    \Ease\TWB\Part::GlyphIcon('minus').' '._('Odebrat ze skupiny')),
                new \Ease\Html\ATag('userinfo.php?user_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace'))
            ]);
        }

        foreach ($unassigned as $userId => $login) {
            $users[$userId] = new \Ease\TWB\ButtonDropdown(
                $login, 'inverse', 'xs',
                [
                new \Ease\Html\ATag('?action=addmember&usergroup_id='.$this->getMyKey().'&member_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('plus').' '._('Přidat do skupiny')),
                new \Ease\Html\ATag('userinfo.php?user_id='.$userId,
                    \Ease\TWB\Part::GlyphIcon('wrench').' '._('Editace'))
            ]);
        }


        return new \Ease\TWB\Panel(_('členové skupiny'), 'default', $users);
    }

    /**
     * Přidá člena do aktuální uživatelské skupiny
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
     * Odebere člena z aktuální uživatelské skupiny
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

    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }
        if (parent::delete($id)) {
            $this->dblink->exeQuery('DELETE FROM user_to_group WHERE group_id='.$id);
        }
    }

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
     * Smaže uživatele ze skupin
     *
     * @param int $id UserID
     */
    public function delUser($id)
    {
        $this->dblink->exeQuery('DELETE FROM user_to_group WHERE user_id='.$id);
    }

}
