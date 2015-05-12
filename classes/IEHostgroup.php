<?php

/**
 * Konfigurace Skupin hostů
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

class IEHostgroup extends IECfg
{

    public $myTable = 'hostgroup';
    public $myKeyColumn = 'hostgroup_id';
    public $keyword = 'hostgroup';
    public $nameColumn = 'hostgroup_name';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = false;
    public $useKeywords = array(
      'hostgroup_name' => 'VARCHAR(64)',
      'alias' => 'VARCHAR(64)',
      'members' => 'IDLIST',
      'hostgroup_members' => 'IDLIST',
      'notes' => 'TEXT',
      'notes_url' => 'VARCHAR(255)',
      'action_url' => 'VARCHAR(255)',
      'bgimages' => 'IDLIST'
    );
    public $keywordsInfo = array(
      'hostgroup_name' => array(
        'severity' => 'requied',
        'title' => 'název skupiny', 'required' => true),
      'alias' => array(
        'severity' => 'optional',
        'title' => 'alias skupiny', 'required' => true),
      'members' => array(
        'severity' => 'basic',
        'title' => 'členské hosty',
        'mandatory' => true,
        'refdata' => array(
          'table' => 'host',
          'captioncolumn' => 'host_name',
          'idcolumn' => 'host_id',
          'condition' => array('register' => 1))
      ),
      'hostgroup_members' => array(
        'severity' => 'optional',
        'title' => 'členské skupiny hostů',
        'refdata' => array(
          'table' => 'hostgroup',
          'captioncolumn' => 'hostgroup_name',
          'idcolumn' => 'hostgroup_id')
      ),
      'notes' => array(
        'severity' => 'basic',
        'title' => 'Poznámka'),
      'notes_url' => array(
        'severity' => 'advanced',
        'title' => 'URL externích poznámek'),
      'action_url' => array(
        'severity' => 'advanced',
        'title' => 'adresa doplnujících akcí')
    );

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
        $MemberOf = EaseShared::myDbLink()->queryToArray('SELECT ' . $this->getmyKeyColumn() . ',' . $this->nameColumn . ' FROM ' . $this->myTable . ' WHERE members LIKE \'%"' . $hostname . '"%\' ', $this->getmyKeyColumn());
        foreach ($MemberOf as $GroupID => $Group) {
            $Found = false;
            $this->loadFromMySQL($GroupID);
            foreach ($this->data['members'] as $ID => $Member) {
                if ($Member == $hostname) {
                    $Found = true;
                    unset($this->data['members'][$ID]);
                    $this->addStatusMessage(sprintf(_(' %s byl odstraněn ze skupiny %s '), $hostname, $Group[$this->nameColumn]));
                }
            }
            if ($Found) {
                $this->saveToMySQL();
            }
        }
    }

    /**
     * Vrací mazací tlačítko
     *
     * @param  string                     $name
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('skupinu hostů'), $addUrl);
    }

    public function loadDefault()
    {
        $groupID = EaseShared::myDbLink()->queryToValue('SELECT ' . $this->getmyKeyColumn() . ' FROM ' . $this->myTable . ' WHERE ' . $this->userColumn . '= ' . EaseShared::user()->getUserID() . ' ORDER BY ' . $this->getmyKeyColumn() . ' DESC LIMIT 1');
        if ($groupID) {
            $this->loadFromMySQL((int) $groupID);

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
        $memberOf = EaseShared::myDbLink()->queryToArray('SELECT ' . $this->getmyKeyColumn() . ',' . $this->nameColumn . ' FROM ' . $this->myTable . ' WHERE members LIKE \'%"' . $oldname . '"%\' ', $this->getmyKeyColumn());
        foreach ($memberOf as $groupID => $group) {
            $found = false;
            $this->loadFromMySQL($groupID);
            foreach ($this->data['members'] as $id => $member) {
                if ($member == $oldname) {
                    $found = true;
                    $this->data['members'][$id] = $newname;
                    $this->addStatusMessage(sprintf(_(' %s byl přejmenován na %s ve skupině %s '), $oldname, $newname, $group[$this->nameColumn]));
                }
            }
            if ($found) {
                $this->saveToMySQL();
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
     * Vloží obrázek pozadí vrstvy hostgrupy do databáze
     *
     * @param string $tmpfilename
     * @param int    $level
     */
    public function saveBackground($tmpfilename, $level)
    {
        $finfo = new finfo(FILEINFO_MIME);
        list($type, $encoding) = explode(';', $finfo->file($tmpfilename));
        $bgs = $this->getDataValue('bgimages');
        if (!is_array($bgs)) {
            $bgs = array();
        }
        $bgs[$level] = 'data:' . $type . ';base64,' . base64_encode(file_get_contents($tmpfilename));
        return $this->setDataValue('bgimages', $bgs);
    }

}
