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

    public $myTable = 'timeperiods';
    public $myKeyColumn = 'timeperiod_id';
    public $Keyword = 'timeperiod';
    public $nameColumn = 'timeperiod_name';
    public $useKeywords = array(
        'timeperiod_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'periods' => 'SERIAL'
    );
    public $keywordsInfo = array(
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
    public $publicRecords = true;

    /**
     * URL dokumentace objektu
     * @var string 
     */
    public $DocumentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-timeperiod';

    /**
     * Převezme data
     * 
     * @param type $data
     * @param type $DataPrefix
     * @return type 
     */
    function takeData($data, $DataPrefix = null)
    {
        $this->Timeperiods = array();
        if (isset($data['NewKey']) && strlen(trim($data['NewKey'])) && isset($data['NewTimes']) && strlen(trim($data['NewTimes']))) {
            $this->addTime($data['NewKey'], $data['NewTimes']);
        }
        unset($data['NewKey']);
        unset($data['NewTimes']);
        unset($data['del']);
        foreach ($data as $Key => $value) {
            if (($Key == $this->myKeyColumn) || array_key_exists($Key, $this->useKeywords) || $Key == $this->userColumn) {
                $this->setDataValue($Key, $value);
            } else {
                $this->addTime($Key, $value);
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
     * @param array $data
     * @param bool $SearchForID
     * @return int 
     */
    function saveToMySQL($data = null, $SearchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (count($this->Timeperiods)) {
            $data['periods'] = serialize($this->Timeperiods);
        }
        $this->setData($data);
        return parent::saveToMySQL($data, $SearchForID);
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
                    $this->useKeywords[$TimeName] = true;
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
                    $this->useKeywords[$TimeName] = true;
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
     * @param string $name
     * @return \EaseJQConfirmedLinkButton 
     */
    function deleteButton($name = null)
    {
        return parent::deleteButton(_('Časovou periodu'));
    }
}

?>
