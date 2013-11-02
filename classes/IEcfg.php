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
class IECfg extends EaseBrick
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
     * @param int|null $ItemID 
     */
    function __construct($ItemID = null)
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

        if (!is_null($ItemID)) {
            if (is_string($ItemID) && $this->NameColumn) {
                $this->setMyKeyColumn($this->NameColumn);
                $this->loadFromMySQL($ItemID);
                $this->resetObjectIdentity();
            } else {
                $this->loadFromMySQL($ItemID);
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
     * @param int|string $Template identifikátor záznamu k načtení
     */
    function loadTemplate($Template)
    {
        if (is_numeric($Template)) {
            $TemplateData = $this->getDataFromMySQL((int) $Template);
        } else {
            $this->setMyKeyColumn('name');
            $TemplateData = $this->getDataFromMySQL($Template);
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
     * @param string $Filename
     * @param array $Columns 
     */
    public function writeConf($Filename, $Columns)
    {
        $Cfg = fopen(constant('CFG_GENERATED') . '/' . $Filename, 'a+');
        if ($Cfg) {
            $Cmdlen = 0;
            foreach ($Columns as $ColumnName => $ColumnValue) {
                if ($ColumnValue == 'NULL') {
                    unset($Columns[$ColumnName]);
                }
                if ($ColumnName == 'public') {
                    unset($Columns['public']);
                }
                if (strlen($ColumnName) > $Cmdlen) {
                    $Cmdlen = strlen($ColumnName);
                }
            }
            ksort($Columns);
            fputs($Cfg, "define " . $this->Keyword . " {\n");
            foreach ($Columns as $ColumnName => $ColumnValue) {

                if (array_key_exists($ColumnName, $this->UseKeywords)) {
                    if ($this->UseKeywords[$ColumnName] === 'IDLIST') {
                        if (is_array($ColumnValue)) {
                            $ColumnValue = join(',', $ColumnValue);
                        }
                    }

                    if (strstr($this->UseKeywords[$ColumnName], 'FLAGS')) {
                        $ColumnValue = join(',', str_split(str_replace(',', '', $ColumnValue)));
                    }

                    if (!strlen(trim($ColumnValue))) {
                        continue;
                    }

                    fputs($Cfg, "\t$ColumnName" . str_repeat(' ', ($Cmdlen - strlen($ColumnName) + 1)) . str_replace("\n", '\n', $ColumnValue) . "\n");
                }
            }
            fputs($Cfg, "}\n\n");
            fclose($Cfg);
        }
    }

    /**
     * Vytvoří SQL tabulku pro ukládání dat objektu
     * 
     * @return type 
     */
    function createSqlStructure()
    {
        if ($this->getMyKeyColumn()) {
            $MyStruct = array_merge(array($this->getMyKeyColumn() => 'INT'), $this->UseKeywords);
        } else {
            $MyStruct = $this->UseKeywords;
        }

        if (!is_null($this->UserColumn)) {
            $MyStruct = array_merge($MyStruct, array($this->UserColumn => 'INT'));
        }

        if (!is_null($this->MyCreateColumn)) {
            $MyStruct = array_merge($MyStruct, array($this->MyCreateColumn => 'DATETIME'));
        }

        if (!is_null($this->MyLastModifiedColumn)) {
            $MyStruct = array_merge($MyStruct, array($this->MyLastModifiedColumn => 'DATETIME'));
        }

        $SQLStruct = array();
        foreach ($MyStruct as $ColumnName => $ColumnType) {

            if (strstr($ColumnType, 'FLAGS')) {
                $ColumnType = 'VARCHAR(' . count(explode(',', $ColumnType)) . ')';
            }

            if (strstr($ColumnType, 'RADIO')) {
                $Options = explode(',', $ColumnType);
                $Maxlen = 0;
                foreach ($Options as $OP) {
                    $Len = strlen($OP);
                    if ($Len > $Maxlen) {
                        $Maxlen = $Len;
                    }
                }
                $ColumnType = 'VARCHAR(' . $Maxlen . ')';
            }

            if ($ColumnType == 'VARCHAR()') {
                $ColumnType = 'VARCHAR(255)';
            }

            if ($ColumnType == 'SERIAL') {
                $ColumnType = 'TEXT';
            }

            if ($ColumnType == 'SLIDER') {
                $ColumnType = 'TINYINT(3)';
            }

            if ($ColumnType == 'IDLIST') {
                $ColumnType = 'TEXT';
            }

            if ($ColumnType == 'SELECT') {
                $ColumnType = 'VARCHAR(64)';
            }

            if ($ColumnType == 'SELECT+PARAMS') {
                $ColumnType = 'VARCHAR(64)';
            }


            $SQLStruct[$ColumnName]['type'] = $ColumnType;
            if ($ColumnName == $this->getMyKeyColumn()) {
                $SQLStruct[$ColumnName]['key'] = 'primary';
                $SQLStruct[$ColumnName]['ai'] = true;
                $SQLStruct[$ColumnName]['unsigned'] = true;
            }
            if ($ColumnName == $this->UserColumn) {
                $SQLStruct[$ColumnName]['key'] = true;
                $SQLStruct[$ColumnName]['unsigned'] = true;
            }
        }

        $this->mySqlUp();
        return $this->MyDbLink->createTable($SQLStruct);
    }

    /**
     * Vrací počet položek v db daného uživatele
     * 
     * @param int $thisID
     * @return int  
     */
    function getMyRecordsCount($thisID = null, $WithShared = false)
    {
        return count($this->getListing($thisID, $WithShared));
    }

    /**
     * Převezme data do aktuálního pole dat a zpracuje checkboxgrupy
     * 
     * @param array  $Data       asociativní pole dat
     * @param string $DataPrefix prefix datové skupiny
     * 
     * @return int
     */
    function takeData($Data, $DataPrefix = null)
    {
        unset($Data['add']);
        unset($Data['del']);
        unset($Data['Save']);
        unset($Data['CheckBoxGroups']);
        foreach ($Data as $key => $value) {
            if ($value === 'NULL') {
                $Data[$key] = null;
            }
            if (strstr($key, '#')) {
                list($Column, $State) = explode('#', $key);
                if ($value == 'on') {
                    if (isset($Data[$Column])) {
                        $Data[$Column] .= $State;
                    } else {
                        $Data[$Column] = $State;
                    }
                }
                unset($Data[$key]);
            }
        }

        foreach ($this->UseKeywords as $FieldName => $FieldType) {

            switch ($FieldType) {
                case 'BOOL':
                    if (isset($Data[$FieldName]) && ($Data[$FieldName] !== null)) {
                        if (($Data[$FieldName] != '0') || ($Data[$FieldName] == true )) {
                            $Data[$FieldName] = (bool) 1;
                        } else {
                            $Data[$FieldName] = (bool) 0;
                        }
                    }

                    break;
                case 'IDLIST':
                    if (isset($Data[$FieldName])) {
                        $Data[$FieldName] = serialize(explode(',', $Data[$FieldName]));
                    }
                    break;
                default:
                    break;
            }
        }

        if (isset($this->UserColumn) && !isset($Data[$this->UserColumn]) || !strlen($Data[$this->UserColumn])) {
            $Data[$this->UserColumn] = EaseShared::user()->getUserID();
        }
        return parent::takeData($Data, $DataPrefix);
    }

    /**
     * Smaže a znovu vytvoří SQL tabulku objektu
     */
    function dbInit()
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
     * @param string $FileName Soubor do kterého se bude generovat konfigirace
     * @return boolean 
     */
    function writeConfig($FileName)
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
     * @param array $Data
     */
    function controlRequied($Data)
    {
        $errors = 0;
        foreach ($this->KeywordsInfo as $Kw => $KwInfo) {
            if (isset($KwInfo['required']) && ($KwInfo['required'] == true)) {

                if ($this->AllowTemplating) {
                    if ($this->isTemplate($Data)) {
                        if (!strlen($Data['name'])) {
                            $this->addStatusMessage($this->Keyword . ': ' . sprintf(_('Předloha %s není pojmenována'), $Data[$this->NameColumn]), 'error');
                            $errors++;
                        }
                    }
                }
                if (!isset($Data[$Kw]) || !$Data[$Kw] || ($Data[$Kw] == 'a:0:{}')) {
                    $this->addStatusMessage($this->Keyword . ': ' . sprintf(_('Chybí hodnota pro požadovanou položku %s pro %s'), $Kw, $this->getName($Data)), 'warning');
                    $errors++;
                }
            }
        }
        return $errors;
    }

    /**
     * Zkontroluje všechny záznamy a přeskočí cizí záznamy
     * 
     * @param array $AllData všechna vstupní data
     * @return array
     */
    function controlAllData($AllData)
    {
        $AllDataOK = array();
        $UserID = EaseShared::user()->getUserID();
        foreach ($AllData as $AdKey => $Data) {
            if ($Data[$this->UserColumn] == $UserID) {
                $AllDataOK[$AdKey] = $Data;
            }
        }
        return $AllDataOK;
    }

    /**
     * Vrací všechna data uživatele
     * 
     * @return array 
     */
    function getAllUserData()
    {
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromMySQL('*', array($this->UserColumn => EaseShared::user()->getUserID()))));
    }

    /**
     * Vrací všechna data
     * 
     * @return array 
     */
    function getAllData()
    {
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromMySQL('*')));
    }

    /**
     * Uloží pole dat do MySQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  MyKeyColumn
     * 
     * @param array $Data        asociativní pole dat
     * @param bool  $SearchForID Zjistit zdali updatovat nebo insertovat
     * 
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    function saveToMySQL($Data = null, $SearchForID = false)
    {
        if (is_null($Data)) {
            $Data = $this->getData();
        }
        foreach ($this->UseKeywords as $KeyWord => $ColumnType) {
            if (isset($Data[$KeyWord]) && !is_null($Data[$KeyWord]) && !is_array($Data[$KeyWord]) && !strlen($Data[$KeyWord])) {
                $Data[$KeyWord] = null;
            }
            switch ($ColumnType) {
                case 'IDLIST':
                    if (isset($Data[$KeyWord]) && is_array($Data[$KeyWord])) {
                        $Data[$KeyWord] = serialize($Data[$KeyWord]);
                    }
                    break;
                default:
                    break;
            }
        }

        if ($this->AllowTemplating && $this->isTemplate()) {
            if (isset($Data[$this->getMyKeyColumn()]) && (int) $Data[$this->getMyKeyColumn()]) {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE `name`' . " = '" . $Data['name'] . "' AND " . $this->MyKeyColumn . ' != ' . $Data[$this->getMyKeyColumn()]);
            } else {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE `name`' . " = '" . $Data['name'] . "'");
            }
        } else {
            if (isset($Data[$this->getMyKeyColumn()]) && (int) $Data[$this->getMyKeyColumn()]) {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE ' . $this->NameColumn . " = '" . $Data[$this->NameColumn] . "' AND " . $this->MyKeyColumn . ' != ' . $Data[$this->getMyKeyColumn()]);
            } else {
                $Keycont = $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE ' . $this->NameColumn . " = '" . $Data[$this->NameColumn] . "'");
            }
        }
        if ($Keycont) {
            if ($this->AllowTemplating && $this->isTemplate()) {
                $this->addStatusMessage(sprintf(_('Předloha %s je již definována. Zvolte prosím jiný název.'), $Data['name']), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('%s %s je již definováno. Zvolte prosím jiné.'), $this->NameColumn, $Data[$this->NameColumn]), 'warning');
            }
            return null;
        } else {
            $Result = parent::saveToMySQL($Data, $SearchForID);
        }
        $this->setMyKey($Result);
        return $Result;
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID
     * 
     * @param int $ItemID klíč záznamu
     * 
     * @return array Results
     */
    function getDataFromMySQL($ItemID = null)
    {
        $Data = parent::getDataFromMySQL($ItemID);
        foreach ($Data as $RecordID => $Record) {
            foreach ($this->UseKeywords as $KeyWord => $ColumnType) {
                switch ($ColumnType) {
                    case 'IDLIST':
                        if (isset($Data[$RecordID][$KeyWord]) && (substr($Data[$RecordID][$KeyWord], 0, 2) == 'a:')) {
                            $Data[$RecordID][$KeyWord] = unserialize($Data[$RecordID][$KeyWord]);
                        } else {
                            $Data[$RecordID][$KeyWord] = array();
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $Data;
    }

    /**
     * Vrací seznam dostupných položek
     * 
     * @param int $thisID id jiného než přihlášeného uživatele
     * @param boolean $WithShared Vracet i nasdílené položky
     * @param array $ExtraColumns další vracené položky
     * 
     * @return array 
     */
    function getListing($thisID = null, $WithShared = true, $ExtraColumns = null)
    {
        if (is_null($thisID)) {
            $thisID = EaseShared::user()->getUserID();
        }
        $ColumnsToGet = array($this->getMyKeyColumn(), $this->NameColumn, 'generate', $this->MyLastModifiedColumn, $this->UserColumn);
        if ($this->AllowTemplating) {
            $ColumnsToGet[] = 'register';
            $ColumnsToGet[] = 'name';
        }

        if (!is_null($ExtraColumns)) {
            $ColumnsToGet = array_merge($ColumnsToGet, $ExtraColumns);
        }

        if ($this->PublicRecords && $WithShared) {
            $ColumnsToGet[] = 'public';
            return $this->getColumnsFromMySQL($ColumnsToGet, $this->UserColumn . '=' . $thisID . ' OR ' . $this->UserColumn . ' IS NULL OR public=1 ', $this->NameColumn, $this->getMyKeyColumn());
        } else {
            return $this->getColumnsFromMySQL($ColumnsToGet, $this->UserColumn . '=' . $thisID . ' OR ' . $this->UserColumn . ' IS NULL ', $this->NameColumn, $this->getMyKeyColumn());
        }
    }

    /**
     * Vrací jméno aktuální položky
     * 
     * @return string 
     */
    function getName($Data = null)
    {
        if (is_null($Data)) {
            if ($this->AllowTemplating) {
                if ($this->isTemplate()) {
                    return $this->getDataValue('name');
                }
            }
            return $this->getDataValue($this->NameColumn);
        } else {
            if ($this->AllowTemplating) {
                if ($this->isTemplate($Data)) {
                    return $Data['name'];
                }
            }
            return $Data[$this->NameColumn];
        }
    }

    /**
     * Vrací ID aktuálního záznamu 
     * @return int
     */
    function getId()
    {
        return (int) $this->getMyKey();
    }

    /**
     * Vrací ID vlastníka 
     * @return type
     */
    function getOwnerID()
    {
        return (int) $this->getDataValue($this->UserColumn);
    }

    /**
     * Vrací mazací tlačítko
     * 
     * @param tring $Name jméno objektu
     * @return \EaseJQConfirmedLinkButton
     */
    function deleteButton($Name = null)
    {
        if ($this->getOwnerID() == EaseShared::user()->getUserID()) {

            if ($this->AllowTemplating && $this->isTemplate()) {
                $ColumnsList = array($this->getMyKeyColumn(), $this->NameColumn, $this->UserColumn);
                if ($this->PublicRecords) {
                    $ColumnsList[] = 'public';
                }
                $Used = $this->getColumnsFromMySQL($ColumnsList, array('use' => $this->getDataValue('name')), $this->NameColumn, $this->getMyKeyColumn());
                if (count($Used)) {
                    $UsedFrame = new EaseHtmlFieldSet(_('je předlohou pro'));
                    foreach ($Used as $UsId => $UsInfo) {
                        if ($this->PublicRecords && ($UsInfo['public'] != true) && ($UsInfo[$this->UserColumn] != EaseShared::user()->getUserID() )) {
                            $UsedFrame->addItem(new EaseHtmlSpanTag(null, $UsInfo[$this->NameColumn], array('class' => 'jellybean gray')));
                        } else {
                            $UsedFrame->addItem(new EaseHtmlSpanTag(null, new EaseHtmlATag('?' . $this->getMyKeyColumn() . '=' . $UsId, $UsInfo[$this->NameColumn]), array('class' => 'jellybean')));
                        }
                    }
                    return $UsedFrame;
                }
            }
            return new EaseJQConfirmedLinkButton('?' . $this->getMyKeyColumn() . '=' . $this->getID() . '&delete=true', _('Smazat ') . $Name . ' <i class="icon-remove-sign"></i>');
        } else {
            return '';
        }
    }

    function isTemplate($Data = null)
    {
        if (is_null($Data)) {
            return (!(int) $this->getDataValue('register') && strlen($this->getDataValue('name')));
        } else {
            return (!(int) $Data['register'] && strlen($Data['name']));
        }
    }

    /**
     * Zobrazí tlačítko s ikonou a odkazem na stránku s informacemi o vlastníku
     * @return \EaseTWBLinkButton
     */
    function ownerLinkButton()
    {
        $OwnerID = $this->getOwnerID();
        $Owner = new EaseUser($OwnerID);
        return new EaseTWBLinkButton('userinfo.php?user_id=' . $OwnerID, array($Owner, '&nbsp;' . $Owner->getUserLogin()));
    }

    /**
     * Smaže záznam
     */
    function delete()
    {
        foreach ($this->Data as $ColumnName => $Value) {
            if (is_array($Value)) {
                $this->unsetDataValue($ColumnName);
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
     * @param type $thisID
     * @return type
     */
    function isOwnedBy($thisID = null)
    {
        if (is_null($thisID)) {
            $thisID = EaseShared::user()->getUserID();
        }
        return ($this->getOwnerID() == $thisID);
    }

    /**
     * 
     * @param type $FileName
     * @param type $CommonValues
     * @return type
     */
    function importFile($FileName, $CommonValues)
    {
        return $this->importArray($this->readRawConfigFile($FileName), $CommonValues);
    }

    /**
     * 
     * @param text $CfgText
     * @param array $CommonValues
     * @return type
     */
    function importText($CfgText, $CommonValues)
    {
        return $this->importArray(array_map('trim', preg_split('/\r\n|\n|\r/', $CfgText)), $CommonValues);
    }

    /**
     * Načte konfiguraci ze souboru
     * 
     * @param array $Cfg
     * @param array $CommonValues Hodnoty vkládané ke každému záznamu
     */
    function importArray($Cfg, $CommonValues = null)
    {
        $Success = 0;
        $Buffer = null;
        if (!count($Cfg)) {
            return null;
        }
        foreach ($Cfg as $CfgLine) {
            if (str_replace(' ', '', $CfgLine) == 'define' . $this->Keyword . '{') {
                $Buffer = array();
                continue;
            }
            if (is_array($Buffer)) {
                if (preg_match("/^([a-zA-Z_]*)[\s|\t]*(.*)$/", $CfgLine, $Matches)) {
                    if ($Matches[2] != '}') {
                        $Buffer[$Matches[1]] = $Matches[2];
                    }
                }
            }
            if (is_array($Buffer) && str_replace(' ', '', $CfgLine) == '}') {
                if (!is_null($CommonValues)) {
                    if (!$this->AllowTemplating) {
                        unset($CommonValues['register']);
                    }
                    if (!$this->PublicRecords) {
                        unset($CommonValues['public']);
                    }
                    $Buffer = array_merge($CommonValues, $Buffer);
                }

                $this->dataReset();

                $this->takeData($Buffer);
                if ($this->saveToMySQL()) {


                    if ($this->isTemplate()) {
                        $this->addStatusMessage(_('předloha') . ' ' . $this->Keyword . ' <strong>' . $Buffer['name'] . '</strong>' . _(' byl naimportován'), 'success');
                    } else {
                        if (!is_null($this->WebLinkColumn) && !isset($Buffer[$this->WebLinkColumn])) {
                            $this->updateToMySQL(
                                    array($this->getMyKeyColumn() => $this->getMyKey(),
                                        $this->WebLinkColumn =>
                                        (str_replace(basename(EaseWebPage::getUri()), '', EaseWebPage::phpSelf(true))) .
                                        $this->Keyword . '.php?' .
                                        $this->getMyKeyColumn() . '=' .
                                        $this->getMyKey()));
                        }
                        $this->addStatusMessage($this->Keyword . ' <strong>' . $Buffer[$this->NameColumn] . '</strong>' . _(' byl naimportován'), 'success');
                    }
                    $Success++;
                } else {
                    if ($this->isTemplate()) {
                        $this->addStatusMessage($this->Keyword . ' <strong>' . $Buffer['name'] . '</strong>' . _(' nebyl naimportován'), 'error');
                    } else {
                        $this->addStatusMessage($this->Keyword . ' <strong>' . $Buffer[$this->NameColumn] . '</strong>' . _(' nebyl naimportován'), 'error');
                    }
                }
                $Buffer = null;
            }
        }

//            $this->addStatusMessage(_('nebyl rozpoznán konfigurační soubor nagiosu pro').' '.$this->Keyword);
        return $Success;
    }

    /**
     * Načte konfigurační soubor do pole
     * 
     * @param type $CfgFile
     * @return type
     */
    static function readRawConfigFile($CfgFile)
    {
        if (!is_file($CfgFile)) {
            EaseShared::user()->addStatusMessage(_('Očekávám název souboru'), 'warning');
            return null;
        }
        $RawCfg = file($CfgFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $Cfg = array();
        foreach ($RawCfg as $RawCfgLine) {
            $RawCfgLine = trim($RawCfgLine);
            if (!strlen($RawCfgLine)) {
                continue;
            }
            if ($RawCfgLine[0] != '#') {
                if (preg_match('@(cfg_file=)(.*)@', $RawCfgLine, $regs)) {
                    foreach (self::readRawConfigFile($regs[2]) as $Line) {
                        $Cfg[] = $Line;
                    }
                } elseif (preg_match('@(cfg_dir=)(.*)@', $RawCfgLine, $regs)) {
                    foreach (self::readRawConfigDir($regs[2]) as $Line) {
                        $Cfg[] = $Line;
                    }
                } else {
                    if (strstr($RawCfgLine, ';')) { //Odstraní komentáře za otazníkem
                        $RawCfgLine = trim(current(explode(';', $RawCfgLine)));
                    }
                    $Cfg[] = $RawCfgLine;
                }
            }
        }
        return $Cfg;
    }

    /**
     * Načte všechny konfiguráky v adresáři
     *  
     * @param string $DirName
     * @return array pole řádků načtené konfigurace
     */
    static function readRawConfigDir($DirName)
    {
        $Cfg = array();
        if (is_dir($DirName)) {
            $d = dir($DirName);
            while (false !== ($entry = $d->read())) {
                if (substr($entry, -4) == '.cfg') {
                    foreach (self::readRawConfigFile($DirName . '/' . $entry) as $Line) {
                        $Cfg[] = $Line;
                    }
                }
            }
            $d->close();
        }
        return $Cfg;
    }

    /**
     * Upraví 
     * @param type $RawData
     * @return type
     */
    function rawToData($RawData)
    {
        $Data = $RawData;
        return $Data;
    }

    /**
     * Přidá hosta služby
     * 
     * @param string $Column název sloupce 
     * @param int    $MemberID
     * @param string $MemberName 
     */
    function addMember($Column, $MemberID, $MemberName)
    {
        $this->Data[$Column][$MemberID] = $MemberName;
    }

    /**
     * Odebere notifikační příkaz skupiny
     * 
     * @param string $Column název sloupečku
     * @param int    $MemberID
     * @param string $MemberName
     * @return boolean 
     */
    function delMember($Column, $MemberID, $MemberName)
    {
        if ($this->Data[$Column][$MemberID] == $MemberName) {
            unset($this->Data[$Column][$MemberID]);
            return true;
        }
    }

    /**
     * Odebere notifikační příkaz skupiny
     * 
     * @param string $Column název sloupečku
     * @param int    $memberID
     * @param string $memberNewName
     * @return boolean 
     */
    function renameMember($Column, $memberID, $memberNewName)
    {
        $this->Data[$Column][$memberID] = $memberNewName;
        return true;
    }

    /**
     * 
     */
    function saveMembers()
    {
        $WebPage = EaseShared::webPage();
        $AddColumn = $WebPage->getGetValue('add');
        if ($AddColumn) {
            $Name = $WebPage->getGetValue('name');
            $this->addMember($AddColumn, $WebPage->getRequestValue('member', 'int'), $Name);
            $thisID = $this->saveToMySQL();
            if (is_null($thisID)) {
                $this->addStatusMessage(sprintf(_('položka %s nebyla přidána do %s.%s.%s'), $Name, $this->Keyword, $this->getName(), $AddColumn), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('položka %s byla přidána do %s.%s.%s'), $Name, $this->Keyword, $this->getName(), $AddColumn), 'success');
            }
        }
        $DelColumn = $WebPage->getGetValue('del');
        if (!is_null($DelColumn)) {
            $Del = $this->delMember($DelColumn, $WebPage->getRequestValue('member', 'int'), $WebPage->getGetValue('name'));
            $thisID = $this->saveToMySQL();
            if (is_null($thisID) && !$Del) {
                $this->addStatusMessage(sprintf(_('položka %s nebyla odebrána z %s.%s.%s'), $Name, $this->Keyword, $this->getName(), $AddColumn), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('položka %s byla odebrána z %s.%s.%s'), $Name, $this->Keyword, $this->getName(), $AddColumn), 'success');
            }
        }
    }

    /**
     * Rekurzivně deserializuje pole z řetězců v datech
     *  
     * @param array $AllData
     * @return array
     */
    static function unserializeArrays($AllData)
    {
        foreach ($AllData as $KeyWord => $KeyData) {
            if (is_array($KeyData)) {
                $AllData[$KeyWord] = self::unserializeArrays($KeyData);
            } else {
                if (strlen($KeyData) && (substr($KeyData, 0, 2) == 'a:')) {
                    $AllData[$KeyWord] = unserialize($KeyData);
                }
            }
        }
        return $AllData;
    }

    /**
     * Reloadne icingu
     */
    static public
            function reloadIcinga()
    {
        $Testing = popen("sudo /etc/init.d/icinga reload", 'r');
        if ($Testing) {
            $ErrorCount = 0;
            while (!feof($Testing)) {
                $Line = fgets($Testing);
                EaseShared::user()->addStatusMessage('Reload: ' . $Line);
            }
            fclose($Testing);
        }
        return TRUE;
    }


}

?>
