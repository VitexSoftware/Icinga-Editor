<?php

/**
 * Konfigurace Period
 * 
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'IEcfg.php';

class IETimeperiod extends IECfg
{

    public $MyTable = 'timeperiods';
    public $MyKeyColumn = 'timeperiod_id';
    public $Keyword = 'timeperiod';
    public $NameColumn = 'timeperiod_name';
    public $UseKeywords = array(
        'timeperiod_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'periods' => 'SERIAL'
    );
    public $KeywordsInfo = array(
        'timeperiod_name' => array('title' => 'název periody', 'required' => true),
        'alias' => array('title' => 'alias periody', 'required' => true),
        'periods' => array('hidden' => true)
    );

    /**
     * Pole časových period
     * @var array 
     */
    public $Timeperiods = array();

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean 
     */
    public $PublicRecords = true;

    /**
     * URL dokumentace objektu
     * @var string 
     */
    public $DocumentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-timeperiod';

    /**
     * Převezme data
     * 
     * @param type $Data
     * @param type $DataPrefix
     * @return type 
     */
    function takeData($Data, $DataPrefix = null)
    {
        $this->Timeperiods = array();
        if (isset($Data['NewKey']) && strlen(trim($Data['NewKey'])) && isset($Data['NewTimes']) && strlen(trim($Data['NewTimes']))) {
            $this->addTime($Data['NewKey'], $Data['NewTimes']);
        }
        unset($Data['NewKey']);
        unset($Data['NewTimes']);
        unset($Data['del']);
        foreach ($Data as $Key => $Value) {
            if (($Key == $this->MyKeyColumn) || array_key_exists($Key, $this->UseKeywords) || $Key == $this->UserColumn) {
                $this->setDataValue($Key, $Value);
            } else {
                $this->addTime($Key, $Value);
            }
        }
        return parent::takeData($this->getData(), $DataPrefix);
    }

    /**
     * Načte časovou periodu z databáze
     * 
     * @param int $ItemID
     * @param string $DataPrefix
     * @param bool $Multiplete
     * @return array 
     */
    function loadFromMySQL($ItemID = null, $DataPrefix = null, $Multiplete = false)
    {
        $Restult = parent::loadFromMySQL($ItemID, $DataPrefix, $Multiplete);
        $Members = $this->getDataValue('periods');
        if (strlen($Members)) {
            $this->Timeperiods = unserialize($Members);
        }
        return $Restult;
    }

    /**
     * Uloží časovou periodu do databáze
     * 
     * @param array $Data
     * @param bool $SearchForID
     * @return int 
     */
    function saveToMySQL($Data = null, $SearchForID = false)
    {
        if (is_null($Data)) {
            $Data = $this->getData();
        }
        if (count($this->Timeperiods)) {
            $Data['periods'] = serialize($this->Timeperiods);
        }
        $this->setData($Data);
        return parent::saveToMySQL($Data, $SearchForID);
    }

    /**
     * Přidá čas do periody
     * 
     * @param string $MemberID
     * @param string $MemberName 
     */
    function addTime($TimeName, $TimeInterval)
    {
        if (strlen($TimeName) && strlen($TimeInterval)) {
            $this->Timeperiods[trim($TimeName)] = trim($TimeInterval);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Odebere čas z periody
     * 
     * @param string $MemberName
     * @return boolean 
     */
    function delTime($MemberID)
    {
        if (isset($this->Timeperiods[$MemberID])) {
            unset($this->Timeperiods[$MemberID]);
            return true;
        }
    }

    function getAllUserData()
    {
        $AllData = parent::getAllUserData();
        foreach ($AllData as $Key => $DataRow) {
            $Periods = $DataRow['periods'];
            if (is_array($Periods) && count($Periods)) {
                foreach ($Periods as $TimeName => $TimeInterval) {
                    $this->UseKeywords[$TimeName] = true;
                }
                unset($AllData[$Key]['periods']);
                if (count($Periods)) {
                    $AllData[$Key] = array_merge($AllData[$Key], $Periods);
                }
            }
        }
        return $AllData;
    }

    function getAllData()
    {
        $AllData = parent::getAllData();
        foreach ($AllData as $Key => $DataRow) {
            $Periods = $DataRow['periods'];
            if (count($Periods)) {
                foreach ($Periods as $TimeName => $TimeInterval) {
                    $this->UseKeywords[$TimeName] = true;
                }
                $AllData[$Key] = array_merge($AllData[$Key], $Periods);
            }
            unset($AllData[$Key]['periods']);
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
        return parent::deleteButton(_('Časovou periodu'));
    }
}

?>
