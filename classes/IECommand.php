<?php

/**
 * Konfigurace Kontaktů
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

/**
 * Spráce kontaktů 
 */
class IECommand extends IECfg
{

    public $myTable = 'command';
    public $MyKeyColumn = 'command_id';
    public $NameColumn = 'command_name';
    public $Keyword = 'command';

    /**
     * Přidat položky register a use ?
     * @var boolean 
     */
    public $AllowTemplating = false;

    /**
     * Položky
     * @var array
     */
    public $UseKeywords = array(
        'command_name' => 'VARCHAR(128)',
        'command_line' => 'TEXT',
        'command_type' => "ENUM('check','notify','handler')",
        'command_local' => 'BOOL',
        'command_remote' => 'BOOL'
    );
    public $KeywordsInfo = array(
        'command_name' => array('title' => 'název příkazu', 'required' => true),
        'command_line' => array('title' => 'příkaz', 'required' => true),
        'command_type' => array('title' => 'druh příkazu', 'required' => true),
        'command_local' => array('title' => 'lokální příkaz'),
        'command_remote' => array('title' => 'vzdálený příkaz NRPE/Nsc++')
    );
    /**
     * URL dokumentace objektu
     * @var string 
     */
    public $DocumentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-command';

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean 
     */
    public $PublicRecords = true;

    /**
     * Vrací všechna data uživatele
     * 
     * @return array 
     */
    function getAllUserData()
    {
        $AllData = parent::getAllUserData();
        foreach ($AllData as $ADkey => $AD) {
            unset($AllData[$ADkey]['command_type']);
            unset($AllData[$ADkey]['command_local']);
            unset($AllData[$ADkey]['command_remote']);
        }
        return $AllData;
    }

    /**
     * Vrací všechna data
     * 
     * @return array 
     */
    function getAllData()
    {
        $AllData = parent::getAllData();
        foreach ($AllData as $ADkey => $AD) {
            unset($AllData[$ADkey]['command_local']);
            unset($AllData[$ADkey]['command_remote']);
            unset($AllData[$ADkey]['command_type']);
        }
        return $AllData;
    }
    /**
     * Vrací mazací tlačítko
     * 
     * @param string $Name
     * @return \EaseJQConfirmedLinkButton 
     */
    function deleteButton($Name = null)
    {
        return parent::deleteButton(_('příkaz'));
    }

    /**
     * Načte data do objektu
     * 
     * @param array  $Data
     * @param string $DataPrefix
     * @return int počet převzatých řádek
     */
    function takeData($Data, $DataPrefix = null)
    {
        if(!isset($Data['command_type'])){
            if(strstr($Data[$this->NameColumn], 'notify')){
                $Data['command_type'] = 'notify';
            } else {
                $Data['command_type'] = 'check';
            }
        }
        return parent::takeData($Data, $DataPrefix);
    }
    
}

?>
