<?php

/**
 * Správce konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'Ease/EaseBrick.php';
require_once 'classes/EaseTWBSwitch.php';

/**
 * Description of IEHosts
 *
 * @author vitex
 */
class IEcfg extends EaseBrick
{

    /**
     * Tabulka do níž objekt ukládá svá data
     * @var string
     */
    public $myTable = NULL;

    /**
     * Klíčové slovo objektu
     * @var String
     */
    public $keyword = NULL;

    /**
     * Objektem používané položky
     * @var array
     */
    public $useKeywords = array();

    /**
     * Rozšířené informace o položkách záznamu
     * @var array
     */
    public $keywordsInfo = array();

    /**
     * Sloupeček s ID vlastníka/autora
     * @var string
     */
    public $userColumn = 'user_id';

    /**
     * Sloupeček obsahující datum vložení záznamu
     * @var string
     */
    public $myCreateColumn = 'DatCreate';

    /**
     * Sloupeček obsahující datum modifikace záznamu
     * @var string
     */
    public $myLastModifiedColumn = 'DatSave';

    /**
     * Sloupeček se jménem objektu
     * @var string
     */
    public $nameColumn = null;

    /**
     * Přidat položky register a use ?
     * @var boolean
     */
    public $allowTemplating = false;

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $publicRecords = true;

    /**
     * Sloupeček s linkem na editor
     * @var string
     */
    public $webLinkColumn = null;

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $documentationLink = '';

    /**
     * Základní nezbytně nutné položky pro běžného uživatele
     * @var array
     */
    public $basicControls = array();

    /**
     * Objekt vlastníka objektu
     * @var IEUser
     */
    public $owner = null;

    /**
     * Objekt konfigurace
     *
     * @param int|null $itemID
     */
    public function __construct($itemID = null)
    {
        parent::__construct();
        $this->user = EaseShared::user();
//       foreach ($this->useKeywords as $KeyWord => $ColumnType) {
//            switch ($ColumnType) {
//                case 'IDLIST':
//                    $this->Listings[$KeyWord] = array();
//                    break;
//                default:
//                    break;
//            }
//        }

        if (!is_null($itemID)) {
            if (is_string($itemID) && $this->nameColumn) {
                $this->setmyKeyColumn($this->nameColumn);
                $this->loadFromMySQL($itemID);
                $this->resetObjectIdentity();
            } else {
                $this->loadFromMySQL($itemID);
            }
        }

        if ($this->allowTemplating) {
            $this->useKeywords['name'] = 'VARCHAR(64)';
            $this->keywordsInfo['name'] = array(
              'title' => _('Uložit jako předlohu pod jménem')
            );
            $this->useKeywords['register'] = 'BOOL';
            $this->useKeywords['use'] = 'SELECT';
            $this->keywordsInfo['register'] = array(
              'title' => _('Není předloha')
            );
            $this->keywordsInfo['use'] = array(
              'title' => 'použít předlohu - template',
              'mandatory' => true,
              'refdata' => array(
                'table' => $this->myTable,
                'captioncolumn' => 'name',
                'idcolumn' => $this->myKeyColumn,
                'condition' => array('register' => 0)
              )
            );
        }

        if ($this->publicRecords) {
            $this->useKeywords['public'] = 'BOOL';
            $this->keywordsInfo['public'] = array(
              'title' => 'Veřejně k dispozici ostatním',
              'mandatory' => true
            );
            $this->keywordsInfo['use']['refdata']['public'] = true;
        }
        $this->useKeywords['generate'] = 'BOOL';
        $this->keywordsInfo['generate'] = array(
          'title' => 'Generovat do konfigurace',
          'mandatory' => true
        );
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID a použije je v objektu
     *
     * @param int     $itemID     klíč záznamu
     * @param array   $dataPrefix název datové skupiny
     * @param boolean $multiplete nevarovat v případě více výsledků
     *
     * @return array Results
     */
    public function loadFromMySQL($itemID = null, $dataPrefix = null, $multiplete = false)
    {
        $result = parent::loadFromMySQL($itemID, $dataPrefix, $multiplete);
        $ownerid = $this->getDataValue($this->userColumn);
        if ($ownerid) {
            $this->owner = new IEUser((int) $ownerid);
        }

        return $result;
    }

    /**
     * Načte data z předlohy
     *
     * @param int|string $template identifikátor záznamu k načtení
     */
    public function loadTemplate($template)
    {
        if (is_numeric($template)) {
            $TemplateData = $this->getDataFromMySQL((int) $template);
        } else {
            $this->setmyKeyColumn('name');
            $TemplateData = $this->getDataFromMySQL($template);
            if (count($TemplateData)) {
                $TemplateData = $TemplateData[0];
            } else {
                $this->addStatusMessage(sprintf(_('předloha %s nebyla načtena'), $TemplateData[$this->nameColumn]), 'error');

                return false;
            }
            $this->restoreObjectIdentity();
        }
        $this->addStatusMessage(sprintf(_('předloha %s byla načtena'), $TemplateData[$this->nameColumn]));
        unset($TemplateData[$this->myKeyColumn]);
        unset($TemplateData[$this->nameColumn]);
        $this->setData($TemplateData);

        return true;
    }

    /**
     * Zapíše konfigurační soubor nagiosu
     *
     * @param string $filename
     * @param array  $columns
     */
    public function writeConf($filename, $columns)
    {
        $cfg = fopen(constant('CFG_GENERATED') . '/' . $filename, 'a+');
        if ($cfg) {
            $cmdlen = 0;
            unset($columns['public']);
            unset($columns['platform']);
            foreach ($columns as $columnName => $columnValue) {
                if ($columnValue == 'NULL') {
                    unset($columns[$columnName]);
                }
                if (strlen($columnName) > $cmdlen) {
                    $cmdlen = strlen($columnName);
                }
            }
            ksort($columns);
            fputs($cfg, "define " . $this->keyword . " { #" . $columns[$this->myKeyColumn] . "@" . $this->myTable . " \n");
            foreach ($columns as $columnName => $columnValue) {

                if (array_key_exists($columnName, $this->useKeywords)) {
                    if ($this->useKeywords[$columnName] === 'IDLIST') {
                        if (is_array($columnValue)) {
                            $columnValue = join(',', $columnValue);
                        }
                    }

                    if (strstr($this->useKeywords[$columnName], 'FLAGS')) {
                        $columnValue = join(',', str_split(str_replace(',', '', $columnValue)));
                    }

                    if (!strlen(trim($columnValue))) {
                        continue;
                    }

                    fputs($cfg, "\t$columnName" . str_repeat(' ', ($cmdlen - strlen($columnName) + 1)) . str_replace("\n", '\n', $columnValue) . "\n");
                }
            }
            fputs($cfg, "}\n\n");
            fclose($cfg);
        }
    }

    /**
     * Vytvoří SQL tabulku pro ukládání dat objektu
     *
     * @return type
     */
    public function createSqlStructure()
    {
        if ($this->getmyKeyColumn()) {
            $myStruct = array_merge(array($this->getmyKeyColumn() => 'INT'), $this->useKeywords);
        } else {
            $myStruct = $this->useKeywords;
        }

        if (!is_null($this->userColumn)) {
            $myStruct = array_merge($myStruct, array($this->userColumn => 'INT'));
        }

        if (!is_null($this->myCreateColumn)) {
            $myStruct = array_merge($myStruct, array($this->myCreateColumn => 'DATETIME'));
        }

        if (!is_null($this->myLastModifiedColumn)) {
            $myStruct = array_merge($myStruct, array($this->myLastModifiedColumn => 'DATETIME'));
        }

        $sqlStruct = array();
        foreach ($myStruct as $columnName => $columnType) {

            if (strstr($columnType, 'FLAGS')) {
                $columnType = 'VARCHAR(' . count(explode(',', $columnType)) . ')';
            }

            if (strstr($columnType, 'RADIO')) {
                $options = explode(',', $columnType);
                $maxlen = 0;
                foreach ($options as $option) {
                    $len = strlen($option);
                    if ($len > $maxlen) {
                        $maxlen = $len;
                    }
                }
                $columnType = 'VARCHAR(' . $maxlen . ')';
            }

            if ($columnType == 'VARCHAR()') {
                $columnType = 'VARCHAR(255)';
            }

            if ($columnType == 'SERIAL') {
                $columnType = 'TEXT';
            }

            if ($columnType == 'SLIDER') {
                $columnType = 'TINYINT(3)';
            }

            if ($columnType == 'IDLIST') {
                $columnType = 'TEXT';
            }

            if ($columnType == 'SELECT') {
                $columnType = 'VARCHAR(64)';
            }

            if ($columnType == 'SELECT+PARAMS') {
                $columnType = 'VARCHAR(64)';
            }

            $sqlStruct[$columnName]['type'] = $columnType;
            if ($columnName == $this->getmyKeyColumn()) {
                $sqlStruct[$columnName]['key'] = 'primary';
                $sqlStruct[$columnName]['ai'] = true;
                $sqlStruct[$columnName]['unsigned'] = true;
            }
            if ($columnName == $this->userColumn) {
                $sqlStruct[$columnName]['key'] = true;
                $sqlStruct[$columnName]['unsigned'] = true;
            }
        }

        $this->mySqlUp();

        return $this->myDbLink->createTable($sqlStruct);
    }

    /**
     * Vrací počet položek v db daného uživatele
     *
     * @param  int $thisID
     * @return int
     */
    public function getMyRecordsCount($thisID = null, $withShared = false)
    {
        return count($this->getListing($thisID, $withShared));
    }

    /**
     * Převezme data do aktuálního pole dat a zpracuje checkboxgrupy
     *
     * @param array  $data       asociativní pole dat
     * @param string $dataPrefix prefix datové skupiny
     *
     * @return int
     */
    public function takeData($data, $dataPrefix = null)
    {
        unset($data['add']);
        unset($data['del']);
        unset($data['Save']);
        unset($data['CheckBoxGroups']);
        foreach ($data as $key => $value) {
            if ($value === 'NULL') {
                $data[$key] = null;
            }
            if (strstr($key, '#')) {
                list($column, $state) = explode('#', $key);
                if ($value == 'on') {
                    if (isset($data[$column])) {
                        $data[$column] .= $state;
                    } else {
                        $data[$column] = $state;
                    }
                }
                unset($data[$key]);
            }
        }

        foreach ($this->useKeywords as $fieldName => $fieldType) {

            switch ($fieldType) {
                case 'BOOL':
                    if (isset($data[$fieldName]) && ($data[$fieldName] !== null)) {
                        if (($data[$fieldName] != '0') || ($data[$fieldName] == true )) {
                            $data[$fieldName] = (bool) 1;
                        } else {
                            $data[$fieldName] = (bool) 0;
                        }
                    } else {
                        $data[$fieldName] = (bool) 0;
                    }

                    break;
                case 'IDLIST':
                    if (isset($data[$fieldName]) && !is_array($data[$fieldName])) {
                        $data[$fieldName] = serialize(explode(',', $data[$fieldName]));
                    }
                    break;
                default:
                    break;
            }
        }

        if (isset($this->userColumn) && !isset($data[$this->userColumn]) || !strlen($data[$this->userColumn])) {
            $data[$this->userColumn] = EaseShared::user()->getUserID();
        }

        return parent::takeData($data, $dataPrefix);
    }

    /**
     * Smaže a znovu vytvoří SQL tabulku objektu
     */
    public function dbInit()
    {
        if ($this->myDbLink->tableExist($this->myTable)) {
            $this->myDbLink->exeQuery('DROP TABLE ' . $this->myTable);
            $this->addStatusMessage(sprintf(_('Tabulka %s byla smazána'), $this->myTable), 'info');
        }
        if ($this->createSqlStructure()) {
            $this->addStatusMessage(sprintf(_('Tabulka %s byla vytvořena'), $this->myTable), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('Tabulka %s nebyla vytvořena'), $this->myTable), 'error');
        }
    }

    /**
     * Načte všechny záznamy uživatele a vygeneruje z nich konfigurační soubory
     *
     * @param  string  $fileName Soubor do kterého se bude generovat konfigirace
     * @return boolean
     */
    public function writeConfig($fileName)
    {
        $allData = $this->getAllData();
        foreach ($allData as $cfgID => $columns) {
            if (intval($columns['generate'])) {
                unset($columns['generate']);
                if (isset($columns['register']) && (int) $columns['register']) {
                    unset($columns['register']);
                }
                $this->writeConf($fileName, $columns);
            }
        }

        return true;
    }

    /**
     * Zkontroluje zdali záznam obsahuje všechna vyžadovaná data
     *
     * @param array $data
     */
    public function controlRequied($data)
    {
        $errors = 0;
        foreach ($this->keywordsInfo as $keyword => $kwInfo) {
            if (isset($kwInfo['required']) && ($kwInfo['required'] == true)) {

                if ($this->allowTemplating) {
                    if ($this->isTemplate($data)) {
                        if (!strlen($data['name'])) {
                            $this->addStatusMessage($this->keyword . ': ' . sprintf(_('Předloha %s není pojmenována'), $data[$this->nameColumn]), 'error');
                            $errors++;
                        }
                    }
                }
                if (!isset($data[$keyword]) || !$data[$keyword] || ($data[$keyword] == 'a:0:{}')) {
                    $this->addStatusMessage($this->keyword . ': ' . sprintf(_('Chybí hodnota pro požadovanou položku %s pro %s'), $keyword, $this->getName($data)), 'warning');
                    $errors++;
                }
            }
        }

        return $errors;
    }

    /**
     * Zkontroluje všechny záznamy a přeskočí cizí záznamy
     *
     * @param  array $allData všechna vstupní data
     * @return array
     */
    public function controlAllData($allData)
    {
        $allDataOK = array();
        $userID = EaseShared::user()->getUserID();
        foreach ($allData as $adKey => $data) {
            if ($data[$this->userColumn] == $userID) {
                $allDataOK[$adKey] = $data;
            }
        }

        return $allDataOK;
    }

    /**
     * Vrací všechna data uživatele
     *
     * @return array
     */
    public function getAllUserData()
    {
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromMySQL('*', array($this->userColumn => EaseShared::user()->getUserID()))));
    }

    /**
     * Vrací všechna data
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromMySQL('*')));
    }

    /**
     * Uloží pole dat do MySQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  myKeyColumn
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToMySQL($data = null, $searchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        foreach ($this->useKeywords as $keyWord => $columnType) {
            if (isset($data[$keyWord]) && !is_null($data[$keyWord]) && !is_array($data[$keyWord]) && !strlen($data[$keyWord])) {
                $data[$keyWord] = null;
            }
            switch ($columnType) {
                case 'IDLIST':
                    if (isset($data[$keyWord]) && is_array($data[$keyWord])) {
                        $data[$keyWord] = serialize($data[$keyWord]);
                    }
                    break;
                default:
                    break;
            }
        }

        if ($this->allowTemplating && $this->isTemplate()) {
            if (isset($data[$this->getmyKeyColumn()]) && (int) $data[$this->getmyKeyColumn()]) {
                $dbId = $this->myDbLink->queryToValue('SELECT `' . $this->myKeyColumn . '` FROM ' . $this->myTable . ' WHERE `name`' . " = '" . $data['name'] . "' AND " . $this->myKeyColumn . ' != ' . $data[$this->getmyKeyColumn()]);
            } else {
                $dbId = $this->myDbLink->queryToValue('SELECT `' . $this->myKeyColumn . '` FROM ' . $this->myTable . ' WHERE `name`' . " = '" . $data['name'] . "'");
            }
        } else {
            if (isset($data[$this->getmyKeyColumn()]) && (int) $data[$this->getmyKeyColumn()]) {
                $dbId = $this->myDbLink->queryToValue('SELECT `' . $this->myKeyColumn . '` FROM ' . $this->myTable . ' WHERE ' . $this->nameColumn . " = '" . $data[$this->nameColumn] . "' AND " . $this->myKeyColumn . ' != ' . $data[$this->getmyKeyColumn()]);
            } else {
                $dbId = $this->myDbLink->queryToValue('SELECT `' . $this->myKeyColumn . '` FROM ' . $this->myTable . ' WHERE ' . $this->nameColumn . " = '" . $data[$this->nameColumn] . "'");
            }
        }
        if (!is_null($dbId) && ($dbId != $this->getMyKey($data) )) {
            if ($this->allowTemplating && $this->isTemplate()) {
                $this->addStatusMessage(sprintf(_('Předloha %s je již definována. Zvolte prosím jiný název.'), $data['name']), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('%s %s je již definováno. Zvolte prosím jiné.'), $this->nameColumn, $data[$this->nameColumn]), 'warning');
            }

            return null;
        } else {
            $result = parent::saveToMySQL($data, $searchForID);
            if (!is_null($result) && (get_class($this->user) == 'IEUser')) {
                $this->user->setSettingValue('unsaved', true);
            }
        }
        $this->setMyKey($result);

        return $result;
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID
     *
     * @param int $itemID klíč záznamu
     *
     * @return array Results
     */
    public function getDataFromMySQL($itemID = null)
    {
        if (is_string($itemID)) {
            $this->setmyKeyColumn($this->nameColumn);
            $data = parent::getDataFromMySQL($itemID);
            $this->restoreObjectIdentity();
        } else {
            $data = parent::getDataFromMySQL($itemID);
        }
        foreach ($data as $recordID => $record) {
            foreach ($this->useKeywords as $keyWord => $columnType) {
                switch ($columnType) {
                    case 'IDLIST':
                        if (isset($data[$recordID][$keyWord]) && (substr($data[$recordID][$keyWord], 0, 2) == 'a:')) {
                            $data[$recordID][$keyWord] = unserialize($data[$recordID][$keyWord]);
                        } else {
                            $data[$recordID][$keyWord] = array();
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Vrací seznam dostupných položek
     *
     * @param int     $thisID       id jiného než přihlášeného uživatele
     * @param boolean $withShared   Vracet i nasdílené položky
     * @param array   $extraColumns další vracené položky
     *
     * @return array
     */
    public function getListing($thisID = null, $withShared = true, $extraColumns = null)
    {
        if (is_null($thisID)) {
            $thisID = EaseShared::user()->getUserID();
        }
        $columnsToGet = array($this->getmyKeyColumn(), $this->nameColumn, 'generate', $this->myLastModifiedColumn, $this->userColumn);
        if ($this->allowTemplating) {
            $columnsToGet[] = 'register';
            $columnsToGet[] = 'name';
        }

        if (!is_null($extraColumns)) {
            $columnsToGet = array_merge($columnsToGet, $extraColumns);
        }

        if ($this->publicRecords && $withShared) {
            $columnsToGet[] = 'public';

            $data = $this->getColumnsFromMySQL($columnsToGet, $this->userColumn . '=' . $thisID . ' OR ' . $this->userColumn . ' IS NULL OR public=1 ', $this->nameColumn, $this->getmyKeyColumn());
        } else {
            $data = $this->getColumnsFromMySQL($columnsToGet, $this->ownershipCondition($thisID), $this->nameColumn, $this->getmyKeyColumn());
        }

        return $this->unserializeArrays($data);
    }

    public function ownershipCondition($thisID)
    {
        if (is_null($thisID)) {
            $thisID = EaseShared::user()->getUserID();
        }

        return $this->userColumn . '=' . $thisID . ' OR ' . $this->userColumn . ' IN (SELECT DISTINCT user_id FROM user_to_group WHERE group_id IN (SELECT group_id FROM user_to_group WHERE user_id = ' . $thisID . '))';
    }

    /**
     * Vrací jméno aktuální položky
     *
     * @return string
     */
    public function getName($data = null)
    {
        if (is_null($data)) {
            if ($this->allowTemplating) {
                if ($this->isTemplate()) {
                    return $this->getDataValue('name');
                }
            }

            return $this->getDataValue($this->nameColumn);
        } else {
            if ($this->allowTemplating) {
                if ($this->isTemplate($data)) {
                    return $data['name'];
                }
            }

            return $data[$this->nameColumn];
        }
    }

    /**
     * Vrací ID aktuálního záznamu
     * @return int
     */
    public function getId()
    {
        return (int) $this->getMyKey();
    }

    /**
     * Vrací ID vlastníka
     * @return type
     */
    public function getOwnerID()
    {
        return (int) $this->getDataValue($this->userColumn);
    }

    /**
     * Vrací mazací tlačítko
     *
     * @param  string                     $name   jméno objektu
     * @param  string                     $urlAdd Předávaná část URL
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($name = null, $urlAdd = '')
    {
        if (($this->getOwnerID() == EaseShared::user()->getUserID()) || EaseShared::user()->getSettingValue('admin')) {

            if ($this->allowTemplating && $this->isTemplate()) {
                $columnsList = array($this->getmyKeyColumn(), $this->nameColumn, $this->userColumn);
                if ($this->publicRecords) {
                    $columnsList[] = 'public';
                }
                $used = $this->getColumnsFromMySQL($columnsList, array('use' => $this->getDataValue('name')), $this->nameColumn, $this->getmyKeyColumn());
                if (count($used)) {
                    $usedFrame = new EaseHtmlFieldSet(_('je předlohou pro'));
                    foreach ($used as $usId => $usInfo) {
                        if ($this->publicRecords && ($usInfo['public'] != true) && ($usInfo[$this->userColumn] != EaseShared::user()->getUserID() )) {
                            $usedFrame->addItem(new EaseHtmlSpanTag(null, $usInfo[$this->nameColumn], array('class' => 'jellybean gray')));
                        } else {
                            $usedFrame->addItem(new EaseHtmlSpanTag(null, new EaseHtmlATag('?' . $this->getmyKeyColumn() . '=' . $usId . '&' . $urlAdd, $usInfo[$this->nameColumn]), array('class' => 'jellybean')));
                        }
                    }

                    return $usedFrame;
                }
            }

            return new EaseJQConfirmedLinkButton('?' . $this->getmyKeyColumn() . '=' . $this->getID() . '&delete=true' . '&' . $urlAdd, _('Smazat ') . $name . ' ' . EaseTWBPart::GlyphIcon('remove-sign'));
        } else {
            return '';
        }
    }

    public function isTemplate($data = null)
    {
        if (is_null($data)) {
            return (!(int) $this->getDataValue('register') && strlen($this->getDataValue('name')));
        } else {
            return (!(int) $data['register'] && strlen($data['name']));
        }
    }

    /**
     * Zobrazí tlačítko s ikonou a odkazem na stránku s informacemi o vlastníku
     * @return \EaseTWBLinkButton
     */
    public function ownerLinkButton()
    {
        $ownerID = $this->getOwnerID();
        if ($ownerID) {
            $owner = new EaseUser($ownerID);

            return new EaseTWBLinkButton('userinfo.php?user_id=' . $ownerID, array($owner, '&nbsp;' . $owner->getUserLogin()));
        } else {
            return new EaseTWBLinkButton('overview.php', array('<img class="avatar" src="img/vsmonitoring.png">', '&nbsp;' . _('Bez vlastníka')));
        }
    }

    /**
     * Smaže záznam
     *
     * @param  int     $id má li být smazán jiný než aktuální záznam
     * @return boolean smazal se záznam ?
     */
    public function delete($id = null)
    {

        if (is_null($id)) {
            $id = $this->getId();
        }

        if (isset($this->data)) {
            foreach ($this->data as $columnName => $value) {
                if (is_array($value)) {
                    $this->unsetDataValue($columnName);
                }
            }
        }
        if ($this->deleteFromMySQL($id)) {
            $this->addStatusMessage(sprintf(_(' %s %s byl smazán '), $this->keyword, $this->getName()), 'success');
            $this->dataReset();
            EaseShared::user()->setSettingValue('unsaved', true);

            return true;
        } else {
            $this->addStatusMessage(sprintf(_(' %s %s nebyl smazán '), $this->keyword, $this->getName()), 'warning');

            return false;
        }
    }

    /**
     * Je záznam vlastněn uživatelem ?
     *
     * @param  type $thisID
     * @return type
     */
    public function isOwnedBy($thisID = null)
    {
        if (is_null($thisID)) {
            $thisID = EaseShared::user()->getUserID();
        }

        return ($this->getOwnerID() == $thisID);
    }

    /**
     *
     * @param  type $fileName
     * @param  type $commonValues
     * @return type
     */
    public function importFile($fileName, $commonValues)
    {
        return $this->importArray($this->readRawConfigFile($fileName), $commonValues);
    }

    /**
     *
     * @param  text  $cfgText
     * @param  array $commonValues
     * @return type
     */
    public function importText($cfgText, $commonValues)
    {
        return $this->importArray(array_map('trim', preg_split('/\r\n|\n|\r/', $cfgText)), $commonValues);
    }

    /**
     * Načte konfiguraci ze souboru
     *
     * @param array $cfg
     * @param array $commonValues Hodnoty vkládané ke každému záznamu
     */
    public function importArray($cfg, $commonValues = null)
    {
        $success = 0;
        $buffer = null;
        if (!count($cfg)) {
            return null;
        }
        foreach ($cfg as $cfgLine) {
            if (str_replace(' ', '', $cfgLine) == 'define' . $this->keyword . '{') {
                $buffer = array();
                continue;
            }
            if (is_array($buffer)) {
                if (preg_match("/^([a-zA-Z_]*)[\s|\t]*(.*)$/", $cfgLine, $matches)) {
                    if ($matches[2] != '}') {
                        $buffer[$matches[1]] = $matches[2];
                    }
                }
            }
            if (is_array($buffer) && str_replace(' ', '', $cfgLine) == '}') {
                if (!is_null($commonValues)) {
                    if (!$this->allowTemplating) {
                        unset($commonValues['register']);
                    }
                    if (!$this->publicRecords) {
                        unset($commonValues['public']);
                    }
                    $buffer = array_merge($commonValues, $buffer);
                }

                $this->dataReset();

                $this->takeData($buffer);
                if ($this->saveToMySQL()) {

                    if ($this->isTemplate()) {
                        $this->addStatusMessage(_('předloha') . ' ' . $this->keyword . ' <strong>' . $buffer['name'] . '</strong>' . _(' byl naimportován'), 'success');
                    } else {
                        if (!is_null($this->webLinkColumn) && !isset($buffer[$this->webLinkColumn])) {
                            $this->updateToMySQL(
                                array($this->getmyKeyColumn() => $this->getMyKey(),
                                  $this->webLinkColumn =>
                                  (str_replace(basename(EaseWebPage::getUri()), '', EaseWebPage::phpSelf(true))) .
                                  $this->keyword . '.php?' .
                                  $this->getmyKeyColumn() . '=' .
                                  $this->getMyKey()));
                        }
                        $this->addStatusMessage($this->keyword . ' <strong>' . $buffer[$this->nameColumn] . '</strong>' . _(' byl naimportován'), 'success');
                    }
                    $success++;
                } else {
                    if ($this->isTemplate()) {
                        $this->addStatusMessage($this->keyword . ' <strong>' . $buffer['name'] . '</strong>' . _(' nebyl naimportován'), 'error');
                    } else {
                        $this->addStatusMessage($this->keyword . ' <strong>' . $buffer[$this->nameColumn] . '</strong>' . _(' nebyl naimportován'), 'error');
                    }
                }
                $buffer = null;
            }
        }

//            $this->addStatusMessage(_('nebyl rozpoznán konfigurační soubor nagiosu pro').' '.$this->keyword);
        return $success;
    }

    /**
     * Načte konfigurační soubor do pole
     *
     * @param  type $cfgFile
     * @return type
     */
    public static function readRawConfigFile($cfgFile)
    {
        if (!is_file($cfgFile)) {
            EaseShared::user()->addStatusMessage(_('Očekávám název souboru'), 'warning');

            return null;
        }
        $rawCfg = file($cfgFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cfg = array();
        foreach ($rawCfg as $rawCfgLine) {
            $rawCfgLine = trim($rawCfgLine);
            if (!strlen($rawCfgLine)) {
                continue;
            }
            if ($rawCfgLine[0] != '#') {
                if (preg_match('@(cfg_file=)(.*)@', $rawCfgLine, $regs)) {
                    foreach (self::readRawConfigFile($regs[2]) as $line) {
                        $cfg[] = $line;
                    }
                } elseif (preg_match('@(cfg_dir=)(.*)@', $rawCfgLine, $regs)) {
                    foreach (self::readRawConfigDir($regs[2]) as $line) {
                        $cfg[] = $line;
                    }
                } else {
                    if (strstr($rawCfgLine, ';')) { //Odstraní komentáře za otazníkem
                        $rawCfgLine = trim(current(explode(';', $rawCfgLine)));
                    }
                    $cfg[] = $rawCfgLine;
                }
            }
        }

        return $cfg;
    }

    /**
     * Načte všechny konfiguráky v adresáři
     *
     * @param  string $dirName
     * @return array  pole řádků načtené konfigurace
     */
    public static function readRawConfigDir($dirName)
    {
        $cfg = array();
        if (is_dir($dirName)) {
            $d = dir($dirName);
            while (false !== ($entry = $d->read())) {
                if (substr($entry, -4) == '.cfg') {
                    foreach (self::readRawConfigFile($dirName . '/' . $entry) as $Line) {
                        $cfg[] = $Line;
                    }
                }
            }
            $d->close();
        }

        return $cfg;
    }

    /**
     * Upraví
     * @param  type $rawData
     * @return type
     */
    public function rawToData($rawData)
    {
        $data = $rawData;

        return $data;
    }

    /**
     * Přidá hosta služby
     *
     * @param string $column     název sloupce
     * @param int    $memberID
     * @param string $memberName
     */
    public function addMember($column, $memberID, $memberName)
    {
        $this->data[$column][$memberID] = $memberName;
        return true;
    }

    /**
     * Odebere notifikační příkaz skupiny
     *
     * @param  string  $column     název sloupečku
     * @param  int     $memberID
     * @param  string  $memberName
     * @return boolean
     */
    public function delMember($column, $memberID = null, $memberName = null)
    {
        if (is_null($memberID)) {
            $found = array_search($memberName, $this->data[$column]);
            if ($found !== false) {
                unset($this->data[$column][$found]);
                return true;
            }
        } else {
            if (isset($this->data[$column][$memberID])) {

                if (!is_null($memberName)) {
                    if ($this->data[$column][$memberID] == $memberName) {
                        unset($this->data[$column][$memberID]);

                        return true;
                    }
                } else {
                    unset($this->data[$column][$memberID]);

                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Odebere notifikační příkaz skupiny
     *
     * @param  string  $column        název sloupečku
     * @param  int     $memberID
     * @param  string  $memberNewName
     * @return boolean
     */
    public function renameMember($column, $memberID, $memberNewName)
    {
        $this->data[$column][$memberID] = $memberNewName;

        return true;
    }

    /**
     * Uloží položky sloupečku ?name=
     */
    public function saveMembers()
    {
        $webPage = EaseShared::webPage();
        $addColumn = $webPage->getGetValue('add');
        $name = $webPage->getGetValue('name');
        if ($addColumn) {
            $this->addMember($addColumn, $webPage->getRequestValue('member', 'int'), $name);
            $thisID = $this->saveToMySQL();
            if (is_null($thisID)) {
                $this->addStatusMessage(sprintf(_('položka %s nebyla přidána do %s/%s/%s'), $name, $this->keyword, $this->getName(), $addColumn), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('položka %s byla přidána do %s/%s/%s'), $name, $this->keyword, $this->getName(), $addColumn), 'success');
            }
        }
        $delColumn = $webPage->getGetValue('del');
        if (!is_null($delColumn)) {
            $thisID = null;
            $del = $this->delMember($delColumn, $webPage->getRequestValue('member', 'int'), $webPage->getGetValue('name'));
            if ($del) {
                $thisID = $this->saveToMySQL();
            }
            if (is_null($thisID) && !$del) {
                $this->addStatusMessage(sprintf(_('položka %s nebyla odebrána z %s/%s/%s'), $name, $this->keyword, $this->getName(), $delColumn), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('položka %s byla odebrána z %s/%s/%s'), $name, $this->keyword, $this->getName(), $delColumn), 'success');
            }
        }
    }

    /**
     * Rekurzivně deserializuje pole z řetězců v datech
     *
     * @param  array $allData
     * @return array
     */
    public static function unserializeArrays($allData)
    {
        foreach ($allData as $keyWord => $keyData) {
            if (is_array($keyData)) {
                $allData[$keyWord] = self::unserializeArrays($keyData);
            } else {
                if (strlen($keyData) && (substr($keyData, 0, 2) == 'a:')) {
                    $allData[$keyWord] = unserialize($keyData);
                }
            }
        }

        return $allData;
    }

    /**
     * Reloadne icingu
     */
    public static function reloadIcinga()
    {
        $testing = popen("sudo /etc/init.d/icinga reload", 'r');
        if ($testing) {
            while (!feof($testing)) {
                $line = fgets($testing);
                EaseShared::user()->addStatusMessage('Reload: ' . $line);
            }
            fclose($testing);
        }

        return TRUE;
    }

    public function cloneButton()
    {
        return new \EaseTWBLinkButton('?action=clone&' . $this->getmyKeyColumn() . '=' . $this->getId(), _('Klonovat'));
    }

    public function draw()
    {
        echo $this->getName();
    }

    /**
     * Vyhledavani v záznamech objektu
     *
     * @param string $what hledaný výraz
     * @return array pole výsledků
     */
    public function searchString($what)
    {
        $results = array();
        $conds = array();
        $columns[] = $this->myKeyColumn;
        foreach ($this->useKeywords as $keyword => $keywordInfo) {
            if (strstr($keywordInfo, 'VARCHAR')) {
                $conds[] = " `$keyword` LIKE '%" . $what . "%'";
                $columns[] = "`$keyword`";
            }
        }

        $res = EaseShared::db()->queryToArray("SELECT " . implode(',', $columns) . "," . $this->nameColumn . " FROM " . $this->myTable . " WHERE " . implode(' OR ', $conds) . ' ORDER BY ' . $this->nameColumn, $this->myKeyColumn);
        foreach ($res as $result) {
            $occurences = '';
            foreach ($result as $key => $value) {
                if (strstr($value, $what)) {
                    $occurences .= '(' . $key . ': ' . $value . ') ';
                }
            }
            $results[$result[$this->myKeyColumn]] = array($this->nameColumn => $result[$this->nameColumn], 'what' => $occurences);
        }
        return $results;
    }

    public function getCsv($queryRaw)
    {
        $transactions = self::getListing($queryRaw);
        $this->getCSVFile($transactions);
    }

    /**
     * Vypíše výsledek SQL dotazu v požadovaném tvaru
     *
     * @param type $queryRaw
     */
    public function output($queryRaw)
    {
        switch (EaseShared::webPage()->getRequestValue('export')) {
            case 'csv':
                $this->getCsv($queryRaw);
                break;
            case 'pdf':
                $this->getPdf($queryRaw);
                break;

            default:
// header("Content-type: application/json");

                echo $this->getJson($queryRaw);
                break;
        }
    }

    /**
     * Připaví data na export jak CSV
     *
     * @param array $data
     * @return array
     */
    public function csvizeData($data)
    {
        if (is_array($data) && count($data)) {
            foreach ($data as $rowId => $row) {
                foreach ($row as $column => $value) {
                    if (strstr($value, ':{')) {
                        $value = unserialize($value);
                        if (is_array($value)) {
                            $data[$rowId][$column] = implode('|', $value);
                        }
                    }
                }
            }
        }
        return $data;
    }

    public function htmlizeData($data)
    {
        if (is_array($data) && count($data)) {
            $usedCache = array();
            foreach ($data as $rowId => $row) {

                if ($this->allowTemplating && isset($row['use'])) {
                    $use = $row['use'];
                    if (!isset($usedCache[$use])) {
                        $used = clone $this;
                        $used->nameColumn = 'name';
                        if ($used->loadFromMySQL($use)) {
                            $used->resetObjectIdentity();
                            $usedCache[$use] = $used->htmlizeData($used->getData());
                        }
                    }

                    if (isset($usedCache[$use])) {
                        foreach ($usedCache[$use] as $templateKey => $templateValue) {
                            if (!is_null($templateValue)) {
                                if (is_array($templateValue)) {
                                    $templateValue = implode(',', $templateValue);
                                }
                                $data[$rowId][$templateKey] = '<span class="inherited" title="' . _('Předloha') . ': ' . $usedCache[$use]['name'] . '">' . $templateValue . '</span>';
                            }
                        }
                    }
                }

                $htmlized = $this->htmlizeRow($row);

                if (is_array($htmlized)) {
                    foreach ($htmlized as $key => $value) {
                        if (!is_null($value)) {
                            $data[$rowId][$key] = $value;
                        } else {
                            if (!isset($data[$rowId][$key])) {
                                $data[$rowId][$key] = $value;
                            }
                        }
                    }
                    if (isset($row['register']) && ($row['register'] == 1)) {
                        $data[$rowId]['name'] = '';
                    }
                }
            }
        }
        return $data;
    }

    public function htmlizeRow($row)
    {
        if (is_array($row) && count($row)) {
            foreach ($row as $key => $value) {
                if ($key == $this->myKeyColumn) {
                    continue;
                }
                if (!isset($this->useKeywords[$key])) {
                    continue;
                }
                $fieldType = $this->useKeywords[$key];
                $fType = preg_replace('/\(.*\)/', '', $fieldType);
                switch ($fType) {
                    case 'PLATFORM':
                        switch ($value) {
                            case 'windows':
                                $icon = 'logos/base/win40.gif';
                                break;
                            case 'linux':
                                $icon = 'logos/base/linux40.gif';
                                break;
                            default:
                                $icon = 'logos/unknown.gif';
                                break;
                        }
                        $row[$key] = '<img class="gridimg" src="' . $icon . '"> ' . $value;
                        break;
                    case 'BOOL':
                        if (is_null($value) || !strlen($value)) {
                            $row[$key] = '<em>NULL</em>';
                        } else {
                            if ($value === '0') {
                                $row[$key] = EaseTWBPart::glyphIcon('unchecked');
                            } else {
                                if ($value === '1') {
                                    $row[$key] = EaseTWBPart::glyphIcon('check');
                                }
                            }
                        }
                        break;
                    case 'IDLIST':
                        if (strlen($value)) {
                            $values = unserialize($value);
                            if (isset($this->keywordsInfo[$key]['refdata'])) {
                                $idcolumn = $this->keywordsInfo[$key]['refdata']['idcolumn'];
                                $table = $this->keywordsInfo[$key]['refdata']['table'];
                                $searchColumn = $this->keywordsInfo[$key]['refdata']['captioncolumn'];
                                $target = str_replace('_id', '.php', $idcolumn);
                                foreach ($values as $id => $name) {
                                    if ($id) {
                                        $values[$id] = '<a title="' . $table . '" href="' . $target . '?' . $idcolumn . '=' . $id . '">' . $name . '</a>';
                                    } else {
                                        $values[$id] = '<a title="' . $table . '" href="search.php?search=' . $name . '&table=' . $table . '&column=' . $searchColumn . '">' . $name . '</a> ' . EaseTWBPart::glyphIcon('search');
                                    }
                                }
                            }
                            $value = implode(',', $values);
                            $row[$key] = $value;
                        }
                        break;
                    default :
                        if (isset($this->keywordsInfo[$key]['refdata']) && strlen(trim($value))) {
                            $table = $this->keywordsInfo[$key]['refdata']['table'];
                            $searchColumn = $this->keywordsInfo[$key]['refdata']['captioncolumn'];
                            $row[$key] = '<a title="' . $table . '" href="search.php?search=' . $value . '&table=' . $table . '&column=' . $searchColumn . '">' . $value . '</a> ' . EaseTWBPart::glyphIcon('search');
                        }
                        if (strstr($key, 'image') && strlen(trim($value))) {
                            $row[$key] = '<img title="' . $value . '" src="logos/' . $value . '" class="gridimg">';
                        }
                        if (strstr($key, 'url')) {
                            $row[$key] = '<a href="' . $value . '">' . $value . '</a>';
                        }

                        break;
                }
            }
        }
        return $row;
    }

    /**
     * Přenese data objektu do jiné instance Icinga Editoru
     */
    public function transfer($target)
    {
        if (is_null($target) || !strlen(trim($target))) {
            $this->addStatusMessage(_('Není zadána URL adresa cíle exportu'), 'warning');
        } else {
            if ($this->user->getSettingValue('exporturl') != $target) {
                $this->user->setSettingValue('exporturl', $target);
                $this->user->saveToSQL();
            }

            $data = $this->getData();
            if (!count($data)) {
                $this->addStatusMessage(sprintf(_('Transfer %s / %s se nezdařil'), get_class($this), $this->getName()), 'error');
                return false;
            }

            $options = array(
              'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
              ),
            );
            $context = stream_context_create($options);
            $result = file_get_contents($target . '/importer.php?class=' . $this->keyword, false, $context);

            if (!$result || trim($result) == 'false') {
                $this->addStatusMessage(_('Transfer se nezdařil'), 'warning');
                return true;
            } else {
                $this->addStatusMessage($result, 'success');
                return false;
            }
        }
    }

    /**
     * Formulář pro přenos konfigurace
     *
     * @return \EaseTWBForm
     */
    public function &transferForm()
    {
        $exportForm = new EaseTWBForm('Export', $this->keyword . '.php');
        $exportForm->addItem(new EaseHtmlInputHiddenTag('action', 'export'));
        $exportForm->addItem(new EaseHtmlInputHiddenTag($this->myKeyColumn, $this->getId()));
        $exportForm->addInput(new EaseHtmlInputTextTag('destination', $this->user->getSettingValue('exporturl')), _('Cíl exportu'));

        $exportForm->addItem(new EaseHtmlH4Tag(_('Rekurzivní import')));

        foreach ($this->keywordsInfo as $columnName => $columnInfo) {
            if (isset($columnInfo['refdata']['table'])) {
                $exportForm->addInput(new EaseTWBSwitch('rels[' . $columnName . ']'), $columnInfo['title']);
            }
        }

        $exportForm->addInput(new EaseTWSubmitButton(_('Exportovat'), 'warning'));
        return $exportForm;
    }

    public function importData($data)
    {
        foreach ($data as $rowId => $dataRow) {
            $this->importDataRow($dataRow);
        }
    }

    public function importDataRow($dataRow)
    {
        foreach ($dataRow as $column => $value) {
            $columnType = 'unknown';
            if (isset($this->useKeywords[$column])) {
                $columnType = $this->useKeywords[$column];
                $columnInfo = $this->keywordsInfo[$column];
            }

            switch ($columnType) {
                case 'IDLIST':
                    if (!is_array($value) && strstr($value, ':{')) {
                        $value = unserialize($value);
                    }
                    if (is_array($value)) {
                        $fixedValue = array();
                        foreach ($value as $item) {
                            $localId = $this->myDbLink->queryToValue('SELECT ' . $columnInfo['refdata']['idcolumn'] . ' FROM ' . $columnInfo['refdata']['table'] . ' WHERE ' . $columnInfo['refdata']['captioncolumn'] . " = '$item'");
                            if ($localId) {
                                $fixedValue[$localId] = $item;
                            } else {
                                $this->addStatusMessage(sprintf(_('Neznámá položka %s sloupec %s při importu'), $item, $column));
                            }
                        }
                        $dataRow[$column] = $fixedValue;
                    }


                    break;
                case 'unknown':
                    unset($dataRow[$column]);
                    $this->addStatusMessage(sprintf(_('Neznámý sloupec %s při importu'), $column));
                    break;
                default:
            }
        }
        return $this->takeData($dataRow);
    }

    /**
     * Vyexportuje objekt včetně závistlostí
     *
     * @param strig $target URL cílové instalace Icinga editoru
     * @return bool
     */
    public function transferDeps($target, $rels = null)
    {
        foreach ($this->keywordsInfo as $columnName => $columnInfo) {
            if (isset($columnInfo['refdata']['table'])) {
                if (is_array($rels) && isset($rels[$columnName])) {
                    $className = 'IE' . ucfirst($columnInfo['refdata']['table']);
                    require_once 'classes/' . $className . '.php';
                    $transfer = new $className($this->getDataValue($columnName));
                    $transfer->transfer($target);
                }
            }
        }
        return $this->transfer($target);
    }

}
