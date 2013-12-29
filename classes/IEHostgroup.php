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
    public $MyKeyColumn = 'hostgroup_id';
    public $Keyword = 'hostgroup';
    public $NameColumn = 'hostgroup_name';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean 
     */
    public $PublicRecords = false;
    public $UseKeywords = array(
        'hostgroup_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'members' => 'IDLIST',
        'hostgroup_members' => 'IDLIST',
        'notes' => 'TEXT',
        'notes_url' => 'VARCHAR(255)',
        'action_url' => 'VARCHAR(255)'
    );
    public $KeywordsInfo = array(
        'hostgroup_name' => array('title' => 'název skupiny', 'required' => true),
        'alias' => array('title' => 'alias skupiny', 'required' => true),
        'members' => array(
            'title' => 'členské hosty',
            'mandatory' => true,
            'refdata' => array(
                'table' => 'hosts',
                'captioncolumn' => 'host_name',
                'idcolumn' => 'host_id',
                'condition' => array('register' => 1))
        ),
        'hostgroup_members' => array(
            'title' => 'členské skupiny hostů',
            'refdata' => array(
                'table' => 'hostgroup',
                'captioncolumn' => 'hostgroup_name',
                'idcolumn' => 'hostgroup_id')
        ),
        'notes' => array('title' => 'Poznámka'),
        'notes_url' => array('title' => 'URL externích poznámek'),
        'action_url' => array('title' => 'adresa doplnujícich akci')
    );
    /**
     * URL dokumentace objektu
     * @var string 
     */
    public $DocumentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-hostgroup';

    /**
     * Smaže hosta ze všech skupin, již je členem.
     * @param string $hostname
     */
    function deleteHost($hostname)
    {
        $MemberOf = EaseShared::myDbLink()->queryToArray('SELECT '.$this->getMyKeyColumn().','.$this->NameColumn.' FROM '. $this->myTable.' WHERE members LIKE \'%"'.$hostname.'"%\' ',$this->getMyKeyColumn() );
        foreach ($MemberOf as $GroupID => $Group){
            $Found = false;
            $this->loadFromMySQL($GroupID);
            foreach ($this->Data['members'] as $ID=>$Member){
                if($Member == $hostname){
                    $Found = true;
                    unset($this->Data['members'][$ID]);
                    $this->addStatusMessage(sprintf(_(' %s byl odstraněn ze skupiny %s '),$hostname,$Group[$this->NameColumn]));
                }
            }
            if($Found){
                $this->saveToMySQL();
            }
        }
    }

    /**
     * Vrací mazací tlačítko
     * 
     * @param string $Name
     * @return \EaseJQConfirmedLinkButton 
     */
    function deleteButton($Name = null)
    {
        return parent::deleteButton(_('Skupinu hostů'));
    }

    function loadDefault(){
        $GroupID = EaseShared::myDbLink()->queryToValue('SELECT '.$this->getMyKeyColumn().' FROM '. $this->myTable.' WHERE '.$this->UserColumn.'= ' . EaseShared::user()->getUserID().' ORDER BY '.$this->getMyKeyColumn().' DESC LIMIT 1');
        if($GroupID){
            $this->loadFromMySQL((int)$GroupID);
            return true;
        }
        return false;
    }

    public function renameHost($oldname, $newname)
    {
        $memberOf = EaseShared::myDbLink()->queryToArray('SELECT '.$this->getMyKeyColumn().','.$this->NameColumn.' FROM '. $this->myTable.' WHERE members LIKE \'%"'.$oldname.'"%\' ',$this->getMyKeyColumn() );
        foreach ($memberOf as $groupID => $group){
            $found = false;
            $this->loadFromMySQL($groupID);
            foreach ($this->Data['members'] as $ID=>$Member){
                if($Member == $hostname){
                    $found = true;
                    $this->Data['members'][$ID]= $newname;
                    $this->addStatusMessage(sprintf(_(' %s byl odstraněn ze skupiny %s '),$hostname,$group[$this->NameColumn]));
                }
            }
            if($found){
                $this->saveToMySQL();
            }
        }
        
    }
    
}

?>
