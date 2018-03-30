<?php
/**
 * Icinga editor - Timeperiod
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

class Timeperiod extends Configurator
{
    public $myTable      = 'timeperiod';
    public $keyColumn  = 'timeperiod_id';
    public $keyword      = 'timeperiod';
    public $nameColumn   = 'timeperiod_name';
    public $useKeywords  = [
        'timeperiod_name' => 'VARCHAR(64)',
        'alias' => 'VARCHAR(64)',
        'periods' => 'SERIAL'
    ];
    public $keywordsInfo = [
        'timeperiod_name' => ['title' => 'název periody', 'required' => true],
        'alias' => ['title' => 'alias periody', 'required' => true],
        'periods' => ['hidden' => true]
    ];

    /**
     * Pole časových period
     * @var array
     */
    public $timeperiods = [];

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
        $this->keywordsInfo['timeperiod_name']['title'] = _('Timeperiod Name');
        $this->keywordsInfo['alias']['title']           = _('Period Alias');

        $this->timeperiods = [];
        if (isset($data['NewKey']) && strlen(trim($data['NewKey'])) && isset($data['NewTimes'])
            && strlen(trim($data['NewTimes']))) {
            $this->addTime($data['NewKey'], $data['NewTimes']);
        }
        unset($data['NewKey']);
        unset($data['NewTimes']);
        unset($data['del']);
        foreach ($data as $key => $value) {
            if (($key == $this->keyColumn) || array_key_exists($key,
                    $this->useKeywords) || $key == $this->userColumn) {
                $this->setDataValue($key, $value);
            } else {
                $this->addTime($key, $value);
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
    public function loadFromSQL($itemID = null, $dataPrefix = null,
                                $multiplete = false)
    {
        $restult = parent::loadFromSQL($itemID, $dataPrefix, $multiplete);
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
    public function saveToSQL($data = null, $searchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (count($this->timeperiods)) {
            $data['periods'] = serialize($this->timeperiods);
        }
        $this->setData($data);

        return parent::saveToSQL($data, $searchForID);
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
            if (is_array($periods)) {
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
     * Return delete button
     *
     * @param  string                     $name
     * @param  string                     $urlAdd part URL
     * 
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $addUrl = '')
    {
        return parent::deleteButton(_('Timeperiod'), $addUrl);
    }

}
