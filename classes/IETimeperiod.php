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

    public $myTable = 'timeperiod';
    public $myKeyColumn = 'timeperiod_id';
    public $keyword = 'timeperiod';
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
    public $timeperiods = array();

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = 'http://docs.icinga.org/latest/en/objectdefinitions.html#objectdefinitions-timeperiod';

    /**
     * Převezme data
     *
     * @param  type $data
     * @param  type $dataPrefix
     * @return type
     */
    public function takeData($data, $dataPrefix = null)
    {
        $this->timeperiods = array();
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

        return parent::takeData($this->getData(), $dataPrefix);
    }

    /**
     * Načte časovou periodu z databáze
     *
     * @param  int    $itemID
     * @param  string $dataPrefix
     * @param  bool   $multiplete
     * @return array
     */
    public function loadFromMySQL($itemID = null, $dataPrefix = null, $multiplete = false)
    {
        $restult = parent::loadFromMySQL($itemID, $dataPrefix, $multiplete);
        $members = $this->getDataValue('periods');
        if (strlen($members)) {
            $this->timeperiods = unserialize($members);
        }

        return $restult;
    }

    /**
     * Uloží časovou periodu do databáze
     *
     * @param  array $data
     * @param  bool  $searchForID
     * @return int
     */
    public function saveToMySQL($data = null, $searchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (count($this->timeperiods)) {
            $data['periods'] = serialize($this->timeperiods);
        }
        $this->setData($data);

        return parent::saveToMySQL($data, $searchForID);
    }

    /**
     * Přidá čas do periody
     *
     * @param string $MemberID
     * @param string $MemberName
     */
    public function addTime($timeName, $timeInterval)
    {
        if (strlen($timeName) && strlen($timeInterval)) {
            $this->timeperiods[trim($timeName)] = trim($timeInterval);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Odebere čas z periody
     *
     * @param  string  $MemberName
     * @return boolean
     */
    public function delTime($memberID)
    {
        if (isset($this->timeperiods[$memberID])) {
            unset($this->timeperiods[$memberID]);

            return true;
        }
    }

    public function getAllUserData()
    {
        $allData = parent::getAllUserData();
        foreach ($allData as $key => $dataRow) {
            $periods = $dataRow['periods'];
            if (is_array($periods) && count($periods)) {
                foreach ($periods as $TimeName => $TimeInterval) {
                    $this->useKeywords[$TimeName] = true;
                }
                unset($allData[$key]['periods']);
                if (count($periods)) {
                    $allData[$key] = array_merge($allData[$key], $periods);
                }
            }
        }

        return $allData;
    }

    public function getAllData()
    {
        $allData = parent::getAllData();
        foreach ($allData as $key => $dataRow) {
            $periods = $dataRow['periods'];
            if (count($periods)) {
                foreach ($periods as $timeName => $timeInterval) {
                    $this->useKeywords[$timeName] = true;
                }
                $allData[$key] = array_merge($allData[$key], $periods);
            }
            unset($allData[$key]['periods']);
        }

        return $allData;
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
        return parent::deleteButton(_('Časovou periodu'), $addUrl);
    }

}
