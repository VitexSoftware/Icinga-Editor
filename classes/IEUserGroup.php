<?php

require_once 'classes/IEUser.php';

/**
 * Uživatel Icinga Editoru
 */
class IEUserGroup extends IEcfg
{

    public $keyword = 'usergroup';
    public $myTable = 'user_group';
    public $nameColumn = 'usergroup_name';
    public $myKeyColumn = 'usergroup_id';
    public $userColumn = 'group_boss';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords = array(
      'usergroup_name' => 'VARCHAR(64)',
      'members' => ''
    );
    public $keywordsInfo = array(
      'usergroup_name' => array('title' => 'název skupiny', 'required' => true),
      'members' => array('title' => 'členové')
    );

    /**
     * členové skupiny
     * @var array
     */
    public $members = array();

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
        $members = array();
        $mmbrs = $this->myDbLink->queryToArray('SELECT id,login FROM user WHERE id IN ( SELECT user_id FROM user_to_group WHERE group_id=' . $id . ' )', 'id');
        foreach ($mmbrs as $mId => $userInfo) {
            $members[$mId] = $userInfo['login'];
        }
        return $members;
    }

    /**
     * Volba členů skupiny
     *
     * @return \EaseTWBPanel
     */
    public function memberSelector()
    {
        $users = array();
        $unassigned = array();
        if ($this->getMyKey()) {
            $ua = $this->myDbLink->queryToArray('SELECT id,login FROM user WHERE id NOT IN ( SELECT user_id FROM user_to_group WHERE group_id=' . $this->getMyKey() . ' )', 'id');
            foreach ($ua as $id => $userInfo) {
                $unassigned[$id] = $userInfo['login'];
            }
        }

        foreach ($this->members as $userId => $login) {
            $users[$userId] = new EaseTWBButtonDropdown(
                $login, 'success', 'xs', array(
              new EaseHtmlATag('?action=delmember&usergroup_id=' . $this->getMyKey() . '&member_id=' . $userId, EaseTWBPart::GlyphIcon('minus') . ' ' . _('Odebrat ze skupiny')),
              new EaseHtmlATag('userinfo.php?user_id=' . $userId, EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace'))
            ));
        }

        foreach ($unassigned as $userId => $login) {
            $users[$userId] = new EaseTWBButtonDropdown(
                $login, 'inverse', 'xs', array(
              new EaseHtmlATag('?action=addmember&usergroup_id=' . $this->getMyKey() . '&member_id=' . $userId, EaseTWBPart::GlyphIcon('plus') . ' ' . _('Přidat do skupiny')),
              new EaseHtmlATag('userinfo.php?user_id=' . $userId, EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace'))
            ));
        }


        return new EaseTWBPanel(_('členové skupiny'), 'default', $users);
    }

    /**
     * Přidá člena do aktuální uživatelské skupiny
     *
     * @param int $memberID
     * @return boolean
     */
    public function addMember($column, $memberID, $memberName = null)
    {
        $this->myDbLink->exeQuery('INSERT INTO user_to_group VALUES(' . $memberID . ',' . $this->getMyKey() . ')');
        $added = $this->myDbLink->numRows;
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
    public function delMember($column, $memberID, $memberName = null)
    {
        $this->myDbLink->exeQuery('DELETE FROM user_to_group WHERE user_id=' . $memberID . ' AND group_id=' . $this->getMyKey());
        $removed = $this->myDbLink->numRows;
        if ($removed) {
            $this->loadMembers();
            return $removed;
        } else {
            return false;
        }
    }

    public function delete($id = null)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }
        if (parent::delete($id)) {
            $this->myDbLink->exeQuery('DELETE FROM user_to_group WHERE group_id=' . $id);
        }
    }

    function htmlizeRow($row)
    {
        $row = parent::htmlizeRow($row);
        $mmbrs = $this->getMembers($row['usergroup_id']);
        if (count($mmbrs)) {
            foreach ($mmbrs as $mId => $mLogin) {
                $mmbrs[$mId] = '<a href="userinfo.php?user_id=' . $mId . '">' . $mLogin . '</a>';
            }
            $row['members'] = implode(',', $mmbrs);
        }
        return $row;
    }

}
