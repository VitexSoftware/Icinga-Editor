<?php

/**
 * Správce konfigurace
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'Ease/EaseBase.php';

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
    public $MyTable = NULL;

    /**
     * Klíčové slovo objektu
     * @var String
     */
    public $Keyword = NULL;

    /**
     * Objektem používané položky
     * @var array
     */
    public $UseKeywords = array();

    /**
     * Rozšířené informace o položkách záznamu
     * @var array
     */
    public $KeywordsInfo = array();

    /**
     * Sloupeček s ID vlastníka/autora
     * @var string
     */
    public $UserColumn = 'user_id';

    /**
     * Sloupeček obsahující datum vložení záznamu
     * @var string
     */
    public $MyCreateColumn = 'DatCreate';

    /**
     * Sloupeček obsahující datum modifikace záznamu
     * @var string
     */
    public $MyLastModifiedColumn = 'DatSave';

    /**
     * Sloupeček se jménem objektu
     * @var string
     */
    public $NameColumn = null;

    /**
     * Přidat položky register a use ?
     * @var boolean
     */
    public $AllowTemplating = false;

    /**
     * Dát tyto položky k dispozici i ostatním ?
     * @var boolean
     */
    public $PublicRecords = true;

    /**
     * Sloupeček s linkem na editor
     * @var string
     */
    public $WebLinkColumn = null;

    /**
     * URL dokumentace objektu
     * @var string
     */
    public $DocumentationLink = '';

    /**
     * Základní nezbytně nutné položky pro běžného uživatele
     * @var array
     */
    public $BasicControls = array();

    /**
     * Objekt konfigurace
     *
     * @param int|null $itemID
     */
    public function __construct($itemID = null)
    {
        $this->setMyTable(constant('DB_PREFIX') . $this->MyTable);
        parent::__construct();

//       foreach ($this->UseKeywords as $KeyWord => $ColumnType) {
//            switch ($ColumnType) {
//                case 'IDLIST':
//                    $this->Listings[$KeyWord] = array();
//                    break;
//                default:
//                    break;
//            }
//        }

        if (!is_null($itemID)) {
            if (is_string($itemID) && $this->NameColumn) {
                $this->setMyKeyColumn($this->NameColumn);
                $this->loadFromMySQL($itemID);
                $this->resetObjectIdentity();
            } else {
                $this->loadFromMySQL($itemID);
            }
        }

        if ($this->AllowTemplating) {
            $this->UseKeywords['name'] = 'VARCHAR(64)';
            $this->KeywordsInfo['name'] = array(
                'title' => _('Uložit jako předlohu pod jménem')
            );
            $this->UseKeywords['register'] = 'BOOL';
            $this->UseKeywords['use'] = 'SELECT';
            $this->KeywordsInfo['register'] = array(
                'title' => _('Není předloha')
            );
            $this->KeywordsInfo['use'] = array(
                'title' => 'použít předlohu - template',
                'mandatory' => true,
                'refdata' => array(
                    'table' => str_replace(DB_PREFIX, '', $this->MyTable),
                    'captioncolumn' => 'name',
                    'idcolumn' => $this->MyKeyColumn,
                    'condition' => array('register' => 0)
                )
            );
        }

        if ($this->PublicRecords) {
            $this->UseKeywords['public'] = 'BOOL';
            $this->KeywordsInfo['public'] = array(
                'title' => 'Veřejně k dispozici ostatním',
                'mandatory' => true
            );
            $this->KeywordsInfo['use']['refdata']['public'] = true;
        }
        $this->UseKeywords['generate'] = 'BOOL';
        $this->KeywordsInfo['generate'] = array(
            'title' => 'Generovat do konfigurace',
            'mandatory' => true
        );
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
            $this->setMyKeyColumn('name');
            $TemplateData = $this->getDataFromMySQL($template);
            if (count($TemplateData)) {
                $TemplateData = $TemplateData[0];
            } else {
                $this->addStatusMessage(sprintf(_('předloha %s nebyla načtena'), $TemplateData[$this->NameColumn]), 'error');

                return false;
            }
            $this->restoreObjectIdentity();
        }
        $this->addStatusMessage(sprintf(_('předloha %s byla načtena'), $TemplateData[$this->NameColumn]));
        unset($TemplateData[$this->MyKeyColumn]);
        unset($TemplateData[$this->NameColumn]);
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
            foreach ($columns as $columnName => $columnValue) {
                if ($columnValue == 'NULL') {
                    unset($columns[$columnName]);
                }
                if ($columnName == 'public') {
                    unset($columns['public']);
                }
                if (strlen($columnName) > $cmdlen) {
                    $cmdlen = strlen($columnName);
                }
            }
            ksort($columns);
            fputs($cfg, "define " . $this->Keyword . " {\n");
            foreach ($columns as $columnName => $columnValue) {

                if (array_key_exists($columnName, $this->UseKeywords)) {
                    if ($this->UseKeywords[$columnName] === 'IDLIST') {
                        if (is_array($columnValue)) {
                            $columnValue = join(',', $columnValue);
                        }
                    }

                    if (strstr($this->UseKeywords[$columnName], 'FLAGS')) {
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
        if ($this->getMyKeyColumn()) {
            $myStruct = array_merge(array($this->getMyKeyColumn() => 'INT'), $this->UseKeywords);
        } else {
            $myStruct = $this->UseKeywords;
        }

        if (!is_null($this->UserColumn)) {
            $myStruct = array_merge($myStruct, array($this->UserColumn => 'INT'));
        }

        if (!is_null($this->MyCreateColumn)) {
            $myStruct = array_merge($myStruct, array($this->MyCreateColumn => 'DATETIME'));
        }

        if (!is_null($this->MyLastModifiedColumn)) {
            $myStruct = array_merge($myStruct, array($this->MyLastModifiedColumn => 'DATETIME'));
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
            if ($columnName == $this->getMyKeyColumn()) {
                $sqlStruct[$columnName]['key'] = 'primary';
                $sqlStruct[$columnName]['ai'] = true;
                $sqlStruct[$columnName]['unsigned'] = true;
            }
            if ($columnName == $this->UserColumn) {
                $sqlStruct[$columnName]['key'] = true;
                $sqlStruct[$columnName]['unsigned'] = true;
            }
        }

        $this->mySqlUp();

        return $this->MyDbLink->createTable($sqlStruct);
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
                list($Column, $State) = explode('#', $key);
                if ($value == 'on') {
                    if (isset($data[$Column])) {
                        $data[$Column] .= $State;
                    } else {
                        $data[$Column] = $State;
                    }
                }
                unset($data[$key]);
            }
        }

        foreach ($this->UseKeywords as $fieldName => $fieldType) {

            switch ($fieldType) {
                case 'BOOL':
                    if (isset($data[$fieldName]) && ($data[$fieldName] !== null)) {
                        if (($data[$fieldName] != '0') || ($data[$fieldName] == true )) {
                            $data[$fieldName] = (bool) 1;
                        } else {
                            $data[$fieldName] = (bool) 0;
                        }
                    }

                    break;
                case 'IDLIST':
                    if (isset($data[$fieldName])) {
                        $data[$fieldName] = serialize(explode(',', $data[$fieldName]));
                    }
                    break;
                default:
                    break;
            }
        }

        if (isset($this->UserColumn) && !isset($data[$this->UserColumn]) || !strlen($data[$this->UserColumn])) {
            $data[$this->UserColumn] = EaseShared::user()->getUserID();
        }

        return parent::takeData($data, $dataPrefix);
    }

    /**
     * Smaže a znovu vytvoří SQL tabulku objektu
     */
    public function dbInit()
    {
        if ($this->MyDbLink->tableExist($this->MyTable)) {
            $this->MyDbLink->exeQuery('DROP TABLE ' . $this->MyTable);
            $this->addStatusMessage(sprintf(_('Tabulka %s byla smazána'), $this->MyTable), 'info');
        }
        if ($this->createSqlStructure()) {
            $this->addStatusMessage(sprintf(_('Tabulka %s byla vytvořena'), $this->MyTable), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('Tabulka %s nebyla vytvořena'), $this->MyTable), 'error');
        }
    }

    /**
     * Načte všechny záznamy uživatele a vygeneruje z nich konfigurační soubory
     * @param  string  $FileName Soubor do kterého se bude generovat konfigirace
     * @return boolean
     */
    public function writeConfig($FileName)
    {
        $AllData = $this->getAllData();
        foreach ($AllData as $CfgID => $Columns) {
            if (intval($Columns['generate'])) {
                unset($Columns['generate']);
                if (isset($Columns['register']) && (int) $Columns['register']) {
                    unset($Columns['register']);
                }
                $this->writeConf($FileName, $Columns);
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
        foreach ($this->KeywordsInfo as $keyword => $kwInfo) {
            if (isset($kwInfo['required']) && ($kwInfo['required'] == true)) {

                if ($this->AllowTemplating) {
                    if ($this->isTemplate($data)) {
                        if (!strlen($data['name'])) {
                            $this->addStatusMessage($this->Keyword . ': ' . sprintf(_('Předloha %s není pojmenována'), $data[$this->NameColumn]), 'error');
                            $errors++;
                        }
                    }
                }
                if (!isset($data[$keyword]) || !$data[$keyword] || ($data[$keyword] == 'a:0:{}')) {
                    $this->addStatusMessage($this->Keyword . ': ' . sprintf(_('Chybí hodnota pro požadovanou položku %s pro %s'), $keyword, $this->getName($data)), 'warning');
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
        foreach ($allData as $AdKey => $Data) {
            if ($Data[$this->UserColumn] == $userID) {
                $allDataOK[$AdKey] = $Data;
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
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromMySQL('*', array($this->UserColumn => EaseShared::user()->getUserID()))));
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
     * Uloží pole dat do MySQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  MyKeyColumn
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
        foreach ($this->UseKeywords as $keyWord => $columnType) {
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

        if ($this->AllowTemplating && $this->isTemplate()) {
            if (isset($data[$this->getMyKeyColumn()]) && (int) $data[$this->getMyKeyColumn()]) {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE `name`' . " = '" . $data['name'] . "' AND " . $this->MyKeyColumn . ' != ' . $data[$this->getMyKeyColumn()]);
            } else {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE `name`' . " = '" . $data['name'] . "'");
            }
        } else {
            if (isset($data[$this->getMyKeyColumn()]) && (int) $data[$this->getMyKeyColumn()]) {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE ' . $this->NameColumn . " = '" . $data[$this->NameColumn] . "' AND " . $this->MyKeyColumn . ' != ' . $data[$this->getMyKeyColumn()]);
            } else {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE ' . $this->NameColumn . " = '" . $data[$this->NameColumn] . "'");
            }
        }
        if ($Keycont) {
            if ($this->AllowTemplating && $this->isTemplate()) {
                $this->addStatusMessage(sprintf(_('Předloha %s je již definována. Zvolte prosím jiný název.'), $data['name']), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('%s %s je již definováno. Zvolte prosím jiné.'), $this->NameColumn, $data[$this->NameColumn]), 'warning');
            }

            return null;
        } else {
            $result = parent::saveToMySQL($data, $searchForID);
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
        $data = parent::getDataFromMySQL($itemID);
        foreach ($data as $recordID => $record) {
            foreach ($this->UseKeywords as $keyWord => $columnType) {
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
        $columnsToGet = array($this->getMyKeyColumn(), $this->NameColumn, 'generate', $this->MyLastModifiedColumn, $this->UserColumn);
        if ($this->AllowTemplating) {
            $columnsToGet[] = 'register';
            $columnsToGet[] = 'name';
        }

        if (!is_null($extraColumns)) {
            $columnsToGet = array_merge($columnsToGet, $extraColumns);
        }

        if ($this->PublicRecords && $withShared) {
            $columnsToGet[] = 'public';

            return $this->getColumnsFromMySQL($columnsToGet, $this->UserColumn . '=' . $thisID . ' OR ' . $this->UserColumn . ' IS NULL OR public=1 ', $this->NameColumn, $this->getMyKeyColumn());
        } else {
            return $this->getColumnsFromMySQL($columnsToGet, $this->UserColumn . '=' . $thisID . ' OR ' . $this->UserColumn . ' IS NULL ', $this->NameColumn, $this->getMyKeyColumn());
        }
    }

    /**
     * Vrací jméno aktuální položky
     *
     * @return string
     */
    public function getName($data = null)
    {
        if (is_null($data)) {
            if ($this->AllowTemplating) {
                if ($this->isTemplate()) {
                    return $this->getDataValue('name');
                }
            }

            return $this->getDataValue($this->NameColumn);
        } else {
            if ($this->AllowTemplating) {
                if ($this->isTemplate($data)) {
                    return $data['name'];
                }
            }

            return $data[$this->NameColumn];
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
        return (int) $this->getDataValue($this->UserColumn);
    }

    /**
     * Vrací mazací tlačítko
     *
     * @param  tring                      $Name jméno objektu
     * @return \EaseJQConfirmedLinkButton
     */
    public function deleteButton($Name = null)
    {
        if ($this->getOwnerID() == EaseShared::user()->getUserID()) {

            if ($this->AllowTemplating && $this->isTemplate()) {
                $columnsList = array($this->getMyKeyColumn(), $this->NameColumn, $this->UserColumn);
                if ($this->PublicRecords) {
                    $columnsList[] = 'public';
                }
                $used = $this->getColumnsFromMySQL($columnsList, array('use' => $this->getDataValue('name')), $this->NameColumn, $this->getMyKeyColumn());
                if (count($used)) {
                    $usedFrame = new EaseHtmlFieldSet(_('je předlohou pro'));
                    foreach ($used as $UsId => $UsInfo) {
                        if ($this->PublicRecords && ($UsInfo['public'] != true) && ($UsInfo[$this->UserColumn] != EaseShared::user()->getUserID() )) {
                            $usedFrame->addItem(new EaseHtmlSpanTag(null, $UsInfo[$this->NameColumn], array('class' => 'jellybean gray')));
                        } else {
                            $usedFrame->addItem(new EaseHtmlSpanTag(null, new EaseHtmlATag('?' . $this->getMyKeyColumn() . '=' . $UsId, $UsInfo[$this->NameColumn]), array('class' => 'jellybean')));
                        }
                    }

                    return $usedFrame;
                }
            }

            return new EaseJQConfirmedLinkButton('?' . $this->getMyKeyColumn() . '=' . $this->getID() . '&delete=true', _('Smazat ') . $Name . ' <i class="icon-remove-sign"></i>');
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
        $owner = new EaseUser($ownerID);

        return new EaseTWBLinkButton('userinfo.php?user_id=' . $ownerID, array($owner, '&nbsp;' . $owner->getUserLogin()));
    }

    /**
     * Smaže záznam
     */
    public function delete()
    {
        foreach ($this->Data as $columnName => $value) {
            if (is_array($value)) {
                $this->unsetDataValue($columnName);
            }
        }
        if ($this->deleteFromMySQL()) {
            $this->addStatusMessage(sprintf(_(' %s %s byl smazán '), $this->Keyword, $this->getName()), 'success');
            $this->dataReset();

            return true;
        } else {
            $this->addStatusMessage(sprintf(_(' %s %s nebyl smazán '), $this->Keyword, $this->getName()), 'warning');

            return false;
        }
    }

    /**
     * Je záznam vlastněn uživatelem ?
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
            if (str_replace(' ', '', $cfgLine) == 'define' . $this->Keyword . '{') {
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
                    if (!$this->AllowTemplating) {
                        unset($commonValues['register']);
                    }
                    if (!$this->PublicRecords) {
                        unset($commonValues['public']);
                    }
                    $buffer = array_merge($commonValues, $buffer);
                }

                $this->dataReset();

                $this->takeData($buffer);
                if ($this->saveToMySQL()) {

                    if ($this->isTemplate()) {
                        $this->addStatusMessage(_('předloha') . ' ' . $this->Keyword . ' <strong>' . $buffer['name'] . '</strong>' . _(' byl naimportován'), 'success');
                    } else {
                        if (!is_null($this->WebLinkColumn) && !isset($buffer[$this->WebLinkColumn])) {
                            $this->updateToMySQL(
                                    array($this->getMyKeyColumn() => $this->getMyKey(),
                                        $this->WebLinkColumn =>
                                        (str_replace(basename(EaseWebPage::getUri()), '', EaseWebPage::phpSelf(true))) .
                                        $this->Keyword . '.php?' .
                                        $this->getMyKeyColumn() . '=' .
                                        $this->getMyKey()));
                        }
                        $this->addStatusMessage($this->Keyword . ' <strong>' . $buffer[$this->NameColumn] . '</strong>' . _(' byl naimportován'), 'success');
                    }
                    $success++;
                } else {
                    if ($this->isTemplate()) {
                        $this->addStatusMessage($this->Keyword . ' <strong>' . $buffer['name'] . '</strong>' . _(' nebyl naimportován'), 'error');
                    } else {
                        $this->addStatusMessage($this->Keyword . ' <strong>' . $buffer[$this->NameColumn] . '</strong>' . _(' nebyl naimportován'), 'error');
                    }
                }
                $buffer = null;
            }
        }

//            $this->addStatusMessage(_('nebyl rozpoznán konfigurační soubor nagiosu pro').' '.$this->Keyword);
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
        $this->Data[$column][$memberID] = $memberName;
    }

    /**
     * Odebere notifikační příkaz skupiny
     *
     * @param  string  $column     název sloupečku
     * @param  int     $memberID
     * @param  string  $memberName
     * @return boolean
     */
    public function delMember($column, $memberID, $memberName)
    {
        if ($this->Data[$column][$memberID] == $memberName) {
            unset($this->Data[$column][$memberID]);

            return true;
        }
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
        $this->Data[$column][$memberID] = $memberNewName;

        return true;
    }

    /**
     *
     */
    public function saveMembers()
    {
        $webPage = EaseShared::webPage();
        $addColumn = $webPage->getGetValue('add');
        if ($addColumn) {
            $name = $webPage->getGetValue('name');
            $this->addMember($addColumn, $webPage->getRequestValue('member', 'int'), $name);
            $thisID = $this->saveToMySQL();
            if (is_null($thisID)) {
                $this->addStatusMessage(sprintf(_('položka %s nebyla přidána do %s.%s.%s'), $name, $this->Keyword, $this->getName(), $addColumn), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('položka %s byla přidána do %s.%s.%s'), $name, $this->Keyword, $this->getName(), $addColumn), 'success');
            }
        }
        $delColumn = $webPage->getGetValue('del');
        if (!is_null($delColumn)) {
            $del = $this->delMember($delColumn, $webPage->getRequestValue('member', 'int'), $webPage->getGetValue('name'));
            $thisID = $this->saveToMySQL();
            if (is_null($thisID) && !$del) {
                $this->addStatusMessage(sprintf(_('položka %s nebyla odebrána z %s.%s.%s'), $name, $this->Keyword, $this->getName(), $addColumn), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('položka %s byla odebrána z %s.%s.%s'), $name, $this->Keyword, $this->getName(), $addColumn), 'success');
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
        return new \EaseTWBLinkButton('?action=clone&' . $this->getMyKeyColumn() . '=' . $this->getId(), _('Klonovat'));
    }

}
