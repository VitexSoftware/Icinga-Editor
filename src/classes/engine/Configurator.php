<?php
/**
 * Správce konfigurace
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\Engine;

/**
 * Konfigurator
 *
 * @author vitex
 */
class Configurator extends \Ease\Brick
{
    /**
     * Tabulka do níž objekt ukládá svá data
     * @var string
     */
    public $myTable = NULL;

    /**
     * Klíčové slovo objektu
     * @var string
     */
    public $keyword = NULL;

    /**
     * Objektem používané položky
     * @var array
     */
    public $useKeywords = [];

    /**
     * Rozšířené informace o položkách záznamu
     * @var array
     */
    public $keywordsInfo = [];

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
    public $basicControls = [];

    /**
     * Objekt vlastníka objektu
     * @var \Icinga\Editor\User
     */
    public $owner = null;

    /**
     * Cache pro rekurzivní konfigurace
     * @var array
     */
    public $parentCache = null;

    /**
     * Column where to get Object Icon
     * @var string 
     */
    public $iconImageColumn = null;

    /**
     * Objekt konfigurace
     *
     * @param int|null $itemID
     */
    public function __construct($itemID = null)
    {
        if (!isset($_SESSION['parentCache'])) { //Todo: Zaktualizovat po editaci šablon
            $_SESSION['parentCache'] = [];
        }
        $this->parentCache = &$_SESSION['parentCache'];
        parent::__construct();
        $this->user        = \Ease\Shared::user();
//       foreach ($this->useKeywords as $KeyWord => $ColumnType) {
//            switch ($ColumnType) {
//                case 'IDLIST':
//                    $this->Listings[$KeyWord] = array();
//                    break;
//                default :
//                    break;
//            }
//        }

        if (!is_null($itemID)) {
            if (is_string($itemID) && $this->nameColumn) {
                $this->setKeyColumn($this->nameColumn);
                $this->loadFromSQL($itemID);
                $this->resetObjectIdentity();
            } else {
                $this->loadFromSQL($itemID);
            }
        } else {
// $this->setDataValue($this->userColumn, \Ease\Shared::user()->getID());
        }

        if ($this->allowTemplating) {
            $this->useKeywords['name']      = 'VARCHAR(64)';
            $this->keywordsInfo['name']     = [
                'severity' => 'advanced',
                'title' => _('Save as template')
            ];
            $this->useKeywords['register']  = 'BOOL';
            $this->useKeywords['use']       = 'SELECT';
            $this->keywordsInfo['register'] = [
                'severity' => 'advanced',
                'title' => _('Not an template')
            ];
            $this->keywordsInfo['use']      = [
                'severity' => 'advanced',
                'title' => 'use template',
                'mandatory' => true,
                'refdata' => [
                    'table' => $this->myTable,
                    'captioncolumn' => 'name',
                    'idcolumn' => $this->keyColumn,
                    'condition' => ['register' => 0]
                ]
            ];
        }

        if ($this->publicRecords) {
            $this->useKeywords['public']                    = 'BOOL';
            $this->keywordsInfo['public']                   = [
                'severity' => 'advanced',
                'title' => 'Publicaly to use by other users',
                'mandatory' => true
            ];
            $this->keywordsInfo['use']['refdata']['public'] = true;
        }
        $this->keywordsInfo['user_id'] = [
            'severity' => 'advanced',
            'title' => _('Owner')
        ];

        $this->useKeywords['generate']  = 'BOOL';
        $this->keywordsInfo['generate'] = [
            'title' => 'Generate to Configuration',
            'severity' => 'advanced',
            'mandatory' => true
        ];

        if (isset($this->userColumn)) {
            $this->useKeywords[$this->userColumn]  = 'USER';
            $this->keywordsInfo[$this->userColumn] = [
                'severity' => 'advanced',
                'title' => _('owner'),
                'refdata' => [
                    'table' => 'user',
                    'captioncolumn' => 'login',
                    'idcolumn' => 'user_id']
            ];
        }
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
    public function loadFromSQL($itemID = null, $dataPrefix = null,
                                $multiplete = false)
    {
        $result  = parent::loadFromSQL($itemID, $dataPrefix, $multiplete);
        $ownerid = $this->getDataValue($this->userColumn);
        if ($ownerid) {
            $this->owner = new \Icinga\Editor\User((int) $ownerid);
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
            $TemplateData = $this->getDataFromSQL((int) $template);
        } else {
            $this->setKeyColumn('name');
            $TemplateData = $this->getDataFromSQL($template);
            if (count($TemplateData)) {
                $TemplateData = $TemplateData[0];
            } else {
                $this->addStatusMessage(sprintf(_('template %s was not loaded'),
                        $TemplateData[$this->nameColumn]), 'error');

                return false;
            }
            $this->restoreObjectIdentity();
        }
        $this->addStatusMessage(sprintf(_('template %s was loaded'),
                $TemplateData[$this->nameColumn]));
        unset($TemplateData[$this->keyColumn]);
        unset($TemplateData[$this->nameColumn]);
        $this->setData($TemplateData);

        return true;
    }

    /**
     * Write config file for nagios/icinga
     *
     * @param string $filename
     * @param array  $columns
     */
    public function writeConf($filename, $columns)
    {
        $cfg = fopen(constant('CFG_GENERATED').'/'.$filename, 'a+');
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
            fputs($cfg,
                "define ".$this->keyword." { #".$columns[$this->keyColumn]."@".$this->myTable." \n");
            foreach ($columns as $columnName => $columnValue) {

                if (is_array($columnValue) && (current($columnValue) == 'vitex')) {
                    $origValue = $columnValue;
                }



                if (array_key_exists($columnName, $this->useKeywords)) {
                    if ($this->useKeywords[$columnName] === 'IDLIST') {
                        if (is_array($columnValue)) {
                            $columnValue = join(',', $columnValue);
                        }
                    }

                    if (strstr($this->useKeywords[$columnName], 'FLAGS')) {
                        $columnValue = join(',',
                            str_split(str_replace(',', '', $columnValue)));
                    }

                    if (is_array($columnValue) || !strlen(trim($columnValue))) {
                        continue;
                    }

                    if ($columnValue == 'Array') {
                        echo "";
                    }


                    fputs($cfg,
                        "\t$columnName".str_repeat(' ',
                            ($cmdlen - strlen($columnName) + 1)).str_replace("\n",
                            '\n', $columnValue)."\n");
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
        if ($this->getKeyColumn()) {
            $myStruct = array_merge([$this->getKeyColumn() => 'INT'],
                $this->useKeywords);
        } else {
            $myStruct = $this->useKeywords;
        }

        if (!is_null($this->userColumn)) {
            $myStruct = array_merge($myStruct, [$this->userColumn => 'INT']);
        }

        if (!is_null($this->myCreateColumn)) {
            $myStruct = array_merge($myStruct,
                [$this->myCreateColumn => 'DATETIME']);
        }

        if (!is_null($this->myLastModifiedColumn)) {
            $myStruct = array_merge($myStruct,
                [$this->myLastModifiedColumn => 'DATETIME']);
        }

        $sqlStruct = [];
        foreach ($myStruct as $columnName => $columnType) {

            if (strstr($columnType, 'FLAGS')) {
                $columnType = 'VARCHAR('.count(explode(',', $columnType)).')';
            }

            if (strstr($columnType, 'RADIO')) {
                $options = explode(',', $columnType);
                $maxlen  = 0;
                foreach ($options as $option) {
                    $len = strlen($option);
                    if ($len > $maxlen) {
                        $maxlen = $len;
                    }
                }
                $columnType = 'VARCHAR('.$maxlen.')';
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
            if ($columnName == $this->getKeyColumn()) {
                $sqlStruct[$columnName]['key']      = 'primary';
                $sqlStruct[$columnName]['ai']       = true;
                $sqlStruct[$columnName]['unsigned'] = true;
            }
            if ($columnName == $this->userColumn) {
                $sqlStruct[$columnName]['key']      = true;
                $sqlStruct[$columnName]['unsigned'] = true;
            }
        }


        return $this->dblink->createTable($sqlStruct);
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
     * Take data to current object add process checkgroups
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
        unset($data['class']);
        unset($data['CheckBoxGroups']);
        if (isset($data['useFromTemplate']) && count($data['useFromTemplate'])) {
            foreach ($data['useFromTemplate'] as $key => $one) {
                $data[$key] = null;
            }
            unset($data['useFromTemplate']);
        }
        foreach ($data as $key => $value) {
            if (isset($value) && is_string($value) && (strtoupper($value) === 'NULL')) {
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
            if (!isset($data[$fieldName])) {
                continue;
            }
            switch ($fieldType) {
                case 'BOOL':
                    if (is_string($data[$fieldName])) {
                        switch ($data[$fieldName]) {
                            case '1':
                            case 'true':
                            case 'on':
                            case 'y':
                                $data[$fieldName] = (bool) 1;
                                break;
                            case '0':
                            case 'false':
                            case 'off':
                            case 'n':
                            default :
                                $data[$fieldName] = (bool) 0;
                                break;
                        }
                    } else {
                        $data[$fieldName] = (bool) $data[$fieldName];
                    }


                    break;
                case 'IDLIST':
                    if (isset($data[$fieldName]) && !is_array($data[$fieldName])) {
                        if (substr($data[$fieldName], 0, 2) != 'a:') {
                            $data[$fieldName] = serialize(explode(',',
                                    $data[$fieldName]));
                        }
                    }
                    break;
                default :
                    break;
            }
        }

//        if (isset($this->userColumn) && !isset($data[$this->userColumn]) || !strlen($data[$this->userColumn])) {
//            $data[$this->userColumn] = \Ease\Shared::user()->getUserID();
//        }

        return parent::takeData($data, $dataPrefix);
    }

    /**
     * Smaže a znovu vytvoří SQL tabulku objektu
     * @deprecated since version 1.2.1 - use phinx
     */
    public function dbInit()
    {
        if ($this->dblink->tableExist($this->myTable)) {
            $this->dblink->exeQuery('DROP TABLE '.$this->myTable);
            $this->addStatusMessage(sprintf(_('Tabulka %s byla smazána'),
                    $this->myTable), 'info');
        }
        if ($this->createSqlStructure()) {
            $this->addStatusMessage(sprintf(_('Tabulka %s byla vytvořena'),
                    $this->myTable), 'success');
        } else {
            $this->addStatusMessage(sprintf(_('Tabulka %s nebyla vytvořena'),
                    $this->myTable), 'error');
        }
    }

    /**
     * Načte všechny záznamy uživatele a vygeneruje z nich configuration filey
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
                if (isset($this->userColumn)) {
                    unset($columns[$this->userColumn]);
                }
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
                            $this->addStatusMessage($this->keyword.': '.sprintf(_('Teplate %s need name'),
                                    $data[$this->nameColumn]), 'error');
                            $errors++;
                        }
                    }
                }
                if (!isset($data[$keyword]) || !$data[$keyword] || ($data[$keyword]
                    == 'a:0:{}')) {
                    $this->addStatusMessage($this->keyword.': '.sprintf(_('Required value missing %s for %s'),
                            $keyword, $this->getName($data)), 'warning');
                    $errors++;
                }
            }
        }

        return $errors;
    }

    /**
     * Vrací efektivní konfigurační hodnotu
     *
     * @param string $keyword
     * @param boolean $templateValue Vracet hodnotu předlohy i když není použta
     * @return  array array( 'nastavujici rodic' => hodnota )
     */
    public function getCfg($keyword, $templateValue = false)
    {
        $parent_used = 0;
        if ($templateValue) {
            $value = null;
        } else {
            $value = $this->getDataValue($keyword);
        }
        if (is_null($value)) {
            $parent_name = $this->getDataValue('use');
            while (is_null($value) && $parent_name) {
                if ($parent_name) {
                    if (!isset($parent)) {
                        $parent = clone $this;
                    }
                    $parent->dataReset();
                    $parent->setKeyColumn('name');
                    $parent->nameColumn = 'name';
                    if (strstr($parent_name, ',')) {
                        $parents = explode(',', $parent_name);
                        foreach ($parents as $parent_name) {
                            if (isset($this->parentCache[$parent_name][$keyword])) {
                                $parentValue = $this->parentCache[$parent_name][$keyword];
                            } else {
                                $parentValue                               = $parent->getColumnsFromSQL([
                                    $keyword, 'use'], ['name' => $parent_name]);
                                $this->parentCache[$parent_name][$keyword] = $parentValue;
                            }
                            if (is_null($parent->getDataValue($keyword))) {
                                $parent->setDataValue($keyword,
                                    $parentValue[0][$keyword]);
                                $parent->setDataValue('use',
                                    $parentValue[0]['use']);
                                $parent_used = $parent_name;
                            }
                        }
                    } else {
                        if (isset($this->parentCache[$parent_name][$keyword])) {
                            $parentValue = $this->parentCache[$parent_name][$keyword];
                        } else {
                            $parentValue                               = $parent->getColumnsFromSQL([
                                $keyword, 'use'], ['name' => $parent_name]);
                            $this->parentCache[$parent_name][$keyword] = $parentValue;
                        }
                        if (isset($parentValue[0])) {
                            $parent->setDataValue($keyword,
                                $parentValue[0][$keyword]);
                            $parent->setDataValue('use', $parentValue[0]['use']);
                        }
                        $parent_used = $parent_name;
                    }
                    $parent_name = $parent->getDataValue('use');
                    $value       = $parent->getDataValue($keyword);
                }
            }
        }
        return [$parent_used => $value];
    }

    /**
     * Vrací efektivní konfigurační hodnotu
     *
     * @param string $keyword
     */
    public function getCfgValue($keyword)
    {
        $cfg = $this->getCfg($keyword);
        if (!is_null($cfg) && is_array($cfg) && count($cfg)) {
            $cfg = current($cfg);
        }
        return $cfg;
    }

    /**
     * Vrací efektivní hodnoty všech načtených položek konfigurace
     *
     * @return array
     */
    function getEffectiveCfg()
    {
        $cfg = [];
        foreach (array_keys($this->getData()) as $column) {
            $cfg[$column] = $this->getCfgValue($column);
        }
        return $cfg;
    }

    /**
     * Zkontroluje všechny záznamy a přeskočí cizí záznamy
     *
     * @param  array $allData všechna vstupní data
     * @return array
     */
    public function controlAllData($allData)
    {
        $allDataOK = [];
        $userID    = \Ease\Shared::user()->getUserID();
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
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromSQL('*',
                        [$this->userColumn => \Ease\Shared::user()->getUserID()])));
    }

    /**
     * Vrací všechna data
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->controlAllData(self::unserializeArrays($this->getColumnsFromSQL('*')));
    }

    /**
     * Uloží pole dat do MySQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  KeyColumn
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null, $searchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        foreach ($this->useKeywords as $keyWord => $columnType) {
            if (!array_key_exists($keyWord, $data)) {
                continue;
            }
//            if (isset($data[$keyWord]) && !is_null($data[$keyWord]) && !is_array($data[$keyWord]) && !strlen($data[$keyWord]) && is_bool($data[$keyWord])) {
//                $data[$keyWord] = null;
//            }
            switch ($columnType) {
                case 'BOOL':
                    switch ($data[$keyWord]) {
                        case 'true':
                        case '1':
                            $value = true;
                            break;
                        case 'false':
                        case '':
                            $value = false;
                            break;
                        case 'null' :
                        case 'NULL' :
                        default :
                            $value = null;
                    }
                    break;

                case 'ARRAY':
                case 'IDLIST':
                    if (isset($data[$keyWord]) && is_array($data[$keyWord])) {
                        $data[$keyWord] = serialize($data[$keyWord]);
                    }
                    break;
                default :
                    break;
            }
        }
        $dbId = null;
        if ($this->allowTemplating && $this->isTemplate() && isset($data['name'])) {
            if (isset($data[$this->getKeyColumn()]) && (int) $data[$this->getKeyColumn()]) {
                $dbId = $this->dblink->queryToValue('SELECT `'.$this->keyColumn.'` FROM '.$this->myTable.' WHERE `name`'." = '".$data['name']."' AND ".$this->keyColumn.' != '.$data[$this->getKeyColumn()]);
            } else {
                $dbId = $this->dblink->queryToValue('SELECT `'.$this->keyColumn.'` FROM '.$this->myTable.' WHERE `name`'." = '".$data['name']."'");
            }
        } else {
            if (isset($data[$this->nameColumn])) {
                if (isset($data[$this->getKeyColumn()]) && (int) $data[$this->getKeyColumn()]) {
                    $dbId = $this->dblink->queryToValue('SELECT `'.$this->keyColumn.'` FROM '.$this->myTable.' WHERE '.$this->nameColumn." = '".$data[$this->nameColumn]."' AND ".$this->keyColumn.' != '.$data[$this->getKeyColumn()]);
                } else {
                    $dbId = $this->dblink->queryToValue('SELECT `'.$this->keyColumn.'` FROM '.$this->myTable.' WHERE '.$this->nameColumn." = '".$data[$this->nameColumn]."'");
                }
            }
        }
        if (!empty($dbId) && ($dbId != $this->getMyKey($data) )) {
            $result = -1;
            if ($this->allowTemplating && $this->isTemplate()) {
                $this->addStatusMessage(sprintf(_('Template %s allready defined. Please use another name'),
                        $data['name']), 'warning');
            } else {
                $this->addStatusMessage(sprintf(_('%s %s allready defined. Please use another name.'),
                        $this->nameColumn, $data[$this->nameColumn]), 'warning');
            }
        } else {
            foreach ($data as $fieldName => $value) {
                if (!is_null($value)) {
                    if (is_string($value)) {
                        $data[$fieldName] = $this->dblink->addSlashes($value);
                    } else {
                        if (is_bool($value)) {
                            $data[$fieldName] = intval($value);
                        }
                    }
                }
            }
            $result = parent::saveToSQL($data, $searchForID);
            if (!is_null($result) && (get_class($this->user) == 'Icinga\Editor\User')) {
                \Ease\Shared::user()->setSettingValue('unsaved', true);
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
    public function getDataFromSQL($itemID = null)
    {
        if (is_string($itemID)) {
            $this->setKeyColumn($this->nameColumn);
            $data = parent::getDataFromSQL($itemID);
            $this->restoreObjectIdentity();
        } else {
            $data = parent::getDataFromSQL($itemID);
        }
        foreach ($data as $recordID => $record) {
            foreach ($this->useKeywords as $keyWord => $columnType) {
                switch ($columnType) {
                    case 'ARRAY':
                    case 'IDLIST':
                        if (isset($data[$recordID][$keyWord]) && (substr($data[$recordID][$keyWord],
                                0, 2) == 'a:')) {
                            $data[$recordID][$keyWord] = unserialize(stripslashes($data[$recordID][$keyWord]));
                        } else {
                            $data[$recordID][$keyWord] = [];
                        }
                        break;
                    default :
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Vrací seznam položek uživatele
     *
     * @param int     $thisID       id jiného než přihlášeného uživatele
     * @param array   $extraColumns další vracené položky
     *
     * @return array
     */
    public function getOwned($thisID = null, $extraColumns = null)
    {
        if (is_null($thisID)) {
            $thisID = \Ease\Shared::user()->getUserID();
        }
        $columnsToGet = [$this->getKeyColumn(), $this->nameColumn, 'generate',
            $this->myLastModifiedColumn, $this->userColumn];

        if (!is_null($extraColumns)) {
            $columnsToGet = array_merge($columnsToGet, $extraColumns);
        }

        $data = $this->getColumnsFromSQL($columnsToGet,
            $this->userColumn.'='.$thisID, $this->nameColumn,
            $this->getKeyColumn());

        return $this->unserializeArrays($data);
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
    public function getListing($thisID = null, $withShared = true,
                               $extraColumns = null)
    {
        if (is_null($thisID)) {
            $thisID = \Ease\Shared::user()->getUserID();
        }
        $columnsToGet = [$this->getKeyColumn(), $this->nameColumn, 'generate',
            $this->myLastModifiedColumn, $this->userColumn];
        if ($this->allowTemplating) {
            $columnsToGet[] = 'register';
            $columnsToGet[] = 'name';
        }

        if (!is_null($extraColumns)) {
            $columnsToGet = array_merge($columnsToGet, $extraColumns);
        }

        if ($this->publicRecords && $withShared) {
            $columnsToGet[] = 'public';

            $data = $this->getColumnsFromSQL($columnsToGet,
                $this->userColumn.'='.$thisID.' OR '.$this->userColumn.' IS NULL OR public=1 ',
                $this->nameColumn, $this->getKeyColumn());
        } else {
            $data = $this->getColumnsFromSQL($columnsToGet,
                $this->ownershipCondition($thisID), $this->nameColumn,
                $this->getKeyColumn());
        }

        return empty($data) ? [] : $this->unserializeArrays($data);
    }

    public function ownershipCondition($thisID)
    {
        if (is_null($thisID)) {
            $thisID = \Ease\Shared::user()->getUserID();
        }

        return $this->userColumn.'='.$thisID.' OR '.$this->userColumn.' IN (SELECT DISTINCT user_id FROM user_to_group WHERE group_id IN (SELECT group_id FROM user_to_group WHERE user_id = '.$thisID.'))';
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
     * Nastaví jméno položky
     *
     * @param string $name
     * @return boolean
     */
    function setName($name)
    {
        if (isset($this->nameColumn)) {
            return $this->setDataValue($this->nameColumn, $name);
        }
        return false;
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
        if (($this->getOwnerID() == \Ease\Shared::user()->getUserID()) || \Ease\Shared::user()->getSettingValue('admin')) {

            if ($this->allowTemplating && $this->isTemplate()) {
                $columnsList = [$this->getKeyColumn(), $this->nameColumn,
                    $this->userColumn];
                if ($this->publicRecords) {
                    $columnsList[] = 'public';
                }
                $used = $this->getColumnsFromSQL($columnsList,
                    ['use' => $this->getDataValue('name')], $this->nameColumn,
                    $this->getKeyColumn());
                if (count($used)) {
                    $usedFrame = new \Ease\TWB\Panel(_('is template for'),
                        'info', null, _('thus can not be deleted'));
                    foreach ($used as $usId => $usInfo) {
                        if ($this->publicRecords && ($usInfo['public'] != true) && ($usInfo[$this->userColumn]
                            != \Ease\Shared::user()->getUserID() )) {
                            $usedFrame->addItem(new \Ease\Html\Span(
                                    $usInfo[$this->nameColumn],
                                    ['class' => 'jellybean gray']));
                        } else {
                            $usedFrame->addItem(new \Ease\Html\Span(
                                    new \Ease\Html\ATag('?'.$this->getKeyColumn().'='.$usId.'&'.$urlAdd,
                                        $usInfo[$this->nameColumn]),
                                    ['class' => 'jellybean']));
                        }
                    }

                    return $usedFrame;
                }
            }

            \Ease\Shared::webPage()->addItem(new \Icinga\Editor\UI\ConfirmationDialog('delete'.$this->getId(),
                    '?'.$this->getKeyColumn().'='.$this->getID().'&delete=true'.'&'.$urlAdd,
                    _('Delete').' '.$name,
                    sprintf(_('Are you sure to delete %s ?'),
                        '<strong>'.$this->getName().'</strong>')));
            return new \Ease\Html\ButtonTag(
                [\Ease\TWB\Part::GlyphIcon('remove'), _('Delete').' '.$this->keyword.' '.$this->getName()],
                ['style' => 'cursor: default', 'class' => 'btn btn-danger',
                'id' => 'triggerdelete'.$this->getId(), 'data-id' => $this->getId()
            ]);
        } else {
            return '';
        }
    }

    /**
     * Is current service template ?
     *
     * @param array $data
     * 
     * @return boolean
     */
    public function isTemplate($data = null)
    {
        if (is_null($data)) {
            return (!(int) $this->getDataValue('register') && strlen($this->getDataValue('name')));
        } else {
            return (!(int) $data['register'] && strlen($data['name']));
        }
    }

    /**
     * Button with link to service owner page
     *
     * @param int $ownerID alternative user ID
     * 
     * @return \\Ease\TWB\LinkButton
     */
    public function ownerLinkButton($ownerID = null)
    {
        $ownerLink = null;
        if (is_null($ownerID)) {
            $ownerID = $this->getOwnerID();
        }
        if ($ownerID) {
            $owner     = new \Ease\User($ownerID);
            $ownerLink = new \Ease\TWB\LinkButton('userinfo.php?user_id='.$ownerID,
                [$owner, '&nbsp;'.$owner->getUserLogin()]);
        } else {
            $ownerLink = new \Ease\TWB\LinkButton('overview.php',
                ['<img class="avatar" src="img/vsmonitoring.png">', '&nbsp;'._('Without owner')]);
        }
        return $ownerLink;
    }

    /**
     * Link to page with owner info
     *
     * @param int $ownerID alternativní ID uživatele
     * @return \\Ease\TWB\LinkButton
     */
    public function ownerLink($ownerID = null)
    {
        $ownerLink = null;
        if (is_null($ownerID)) {
            $ownerID = $this->getOwnerID();
        }
        if ($ownerID) {
            $owner     = new \Ease\User($ownerID);
            $ownerLink = new \Ease\Html\ATag('userinfo.php?user_id='.$ownerID,
                $owner->getUserLogin());
        } else {
            $ownerLink = new \Ease\Html\ATag('overview.php', _('Without owner'));
        }
        return $ownerLink;
    }

    /**
     * Delete record
     *
     * @param  int     $id to delete another than current record
     *
     * @return boolean operation result
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
        if ($this->deleteFromSQL($id)) {
            $this->addStatusMessage(sprintf(_(' %s %s was deleted '),
                    $this->keyword, $this->getName()), 'success');
            $this->dataReset();
            \Ease\Shared::user()->setSettingValue('unsaved', true);

            return true;
        } else {
            $this->addStatusMessage(sprintf(_(' %s %s was not deleted '),
                    $this->keyword, $this->getName()), 'warning');

            return false;
        }
    }

    /**
     * Is current record owned by user with given ID ?
     *
     * @param  int $thisID
     *
     * @return boolean
     */
    public function isOwnedBy($thisID = null)
    {
        if (is_null($thisID)) {
            $thisID = \Ease\Shared::user()->getUserID();
        }

        return ($this->getOwnerID() == $thisID);
    }

    /**
     * Import configuration from file
     *
     * @param  string $fileName
     * @param  array  $commonValues defaults
     * 
     * @return int
     */
    public function importFile($fileName, $commonValues)
    {
        return $this->importArray($this->readRawConfigFile($fileName),
                $commonValues);
    }

    /**
     * Import configuration from string
     *
     * @param  text  $cfgText
     * @param  array $commonValues
     *
     * @return boolean
     */
    public function importText($cfgText, $commonValues)
    {
        return $this->importArray(array_map('trim',
                    preg_split('/\r\n|\n|\r/', $cfgText)), $commonValues);
    }

    /**
     * Read configuration from array
     *
     * @param array $cfgArray
     * @param array $commonValues Hodnoty vkládané ke každému záznamu
     *
     * @return boolean operation restult status
     */
    public function importArray($cfgArray, $commonValues = null)
    {
        $success = 0;
        $buffer  = null;
        if (count($cfgArray)) {
            foreach ($cfgArray as $cfgLine) {
                if (strstr($cfgLine, '#')) {
                    $cfgLine = strstr($cfgLine, '#', true);
                }
                if (str_replace(' ', '', $cfgLine) == 'define'.$this->keyword.'{') {
                    $buffer = [];
                    continue;
                }
                if (is_array($buffer)) {
                    if (preg_match("/^([a-zA-Z_]*)[\s|\t]*(.*)$/", $cfgLine,
                            $matches)) {
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
                    $import = $this->saveToSQL();


                    if (is_null($import)) {
                        if ($this->isTemplate()) {
                            $this->addStatusMessage($this->keyword.' <strong>'.$buffer['name'].'</strong> '._('was not imported'),
                                'error');
                        } else {
                            $this->addStatusMessage($this->keyword.' <strong>'.$buffer[$this->nameColumn].'</strong> '._('was not imported'),
                                'error');
                        }
                    } else {
                        if ($import != -1) {
                            if ($this->isTemplate()) {
                                $this->addStatusMessage(_('Preset').' '.$this->keyword.' <strong>'.$buffer['name'].'</strong> '._('was imported'),
                                    'success');
                            } else {
                                if (!is_null($this->webLinkColumn) && !isset($buffer[$this->webLinkColumn])) {
                                    $this->updateToSQL(
                                        [$this->getKeyColumn() => $this->getMyKey(),
                                            $this->webLinkColumn =>
                                            (str_replace(basename(\Ease\WebPage::getUri()),
                                                '', \Ease\WebPage::phpSelf(true))).
                                            $this->keyword.'.php?'.
                                            $this->getKeyColumn().'='.
                                            $this->getMyKey()]);
                                }
                                $this->addStatusMessage($this->keyword.' <strong>'.$buffer[$this->nameColumn].'</strong> '._('was imported'),
                                    'success');
                            }
                            $success++;
                        }
                    }

                    $buffer = null;
                }
            }
        }

//            $this->addStatusMessage(_('nebyl rozpoznán configuration file nagiosu pro').' '.$this->keyword);
        return $success;
    }

    /**
     * Read config file to array
     *
     * @param string $cfgFile filename to read
     * @param Importer $importer Importer object
     * @return array
     */
    public static function readRawConfigFile($cfgFile, $importer = null)
    {
        $cfg = [];
        if (!is_file($cfgFile)) {
            \Ease\Shared::user()->addStatusMessage(_('I need filename'),
                'warning');

            return null;
        }
        $rawCfg = file($cfgFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($rawCfg) && is_object($importer)) {
            $importer->files[] = $cfgFile;
        }
        $cfg = [];
        foreach ($rawCfg as $rawCfgLine) {
            $rawCfgLine = trim($rawCfgLine);
            if (!strlen($rawCfgLine)) {
                continue;
            }
            if ($rawCfgLine[0] != '#') {
                if (preg_match('@(cfg_file=)(.*)@', $rawCfgLine, $regs)) {
                    foreach (self::readRawConfigFile($regs[2], $importer) as $line) {
                        $cfg[] = $line;
                    }
                } elseif (preg_match('@(cfg_dir=)(.*)@', $rawCfgLine, $regs)) {
                    foreach (self::readRawConfigDir($regs[2], $importer) as $line) {
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
     * @param Importer $importer Objekt importeru
     *
     * @return array  rows of configuration
     */
    public static function readRawConfigDir($dirName, $importer = null)
    {
        $cfg = [];
        if (is_dir($dirName)) {
            $d     = dir($dirName);
            while (false !== ($entry = $d->read())) {
                if ($entry[0] == '.') {
                    continue;
                }
                if (substr($entry, -4) == '.cfg') {
                    foreach (self::readRawConfigFile($dirName.'/'.$entry,
                        $importer) as $line) {
                        $cfg[] = $line;
                    }
                } elseif (is_dir($dirName.'/'.$entry)) {
                    foreach (self::readRawConfigDir($dirName.'/'.$entry,
                        $importer) as $line) {
                        $cfg[] = $line;
                    }
                }
            }
            $d->close();
        }

        return $cfg;
    }

    /**
     * Convert raw data to intrnal format
     *
     * @param  array $rawData
     *
     * @return array
     */
    public function rawToData($rawData)
    {
        $data = $rawData;

        return $data;
    }

    /**
     * Assign Host with service
     *
     * @param string $column     název sloupce
     * @param int    $memberID
     * @param string $memberName
     *
     * @return boolean Member adding status
     */
    public function addMember($column, $memberID, $memberName)
    {
        if (isset($this->data[$column]) && is_string($this->data[$column])) {
            $field               = unserialize(stripslashes($this->data[$column]));
            $field[$memberID]    = $memberName;
            $this->data[$column] = addslashes(serialize($field));
        } else {
            $this->data[$column][$memberID] = $memberName;
        }
        return true;
    }

    /**
     * Remove meber from group
     *
     * @param  string  $column     název sloupečku
     * @param  int     $memberID
     * @param  string  $memberName
     * 
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
     * Rename group member
     *
     * @param  string  $column        column name
     * @param  int     $memberID
     * @param  string  $memberNewName
     *
     * @return boolean
     */
    public function renameMember($column, $memberID, $memberNewName)
    {
        $this->data[$column][$memberID] = $memberNewName;

        return true;
    }

    /**
     * Save members
     */
    public function saveMembers()
    {
        $webPage   = \Ease\Shared::webPage();
        $addColumn = $webPage->getGetValue('add');
        $name      = $webPage->getGetValue('name');
        if ($addColumn) {
            $this->addMember($addColumn,
                $webPage->getRequestValue('member', 'int'), $name);
            $thisID = $this->saveToSQL();
            if (is_null($thisID)) {
                $this->addStatusMessage(sprintf(_('item %s was not added to %s/%s/%s'),
                        $name, $this->keyword, $this->getName(), $addColumn),
                    'warning');
            } else {
                $this->addStatusMessage(sprintf(_('item %s added to %s/%s/%s'),
                        $name, $this->keyword, $this->getName(), $addColumn),
                    'success');
            }
        }
        $delColumn = $webPage->getGetValue('del');
        if (!is_null($delColumn)) {
            $thisID = null;
            $del    = $this->delMember($delColumn,
                $webPage->getRequestValue('member', 'int'),
                $webPage->getGetValue('name'));
            if ($del) {
                $thisID = $this->saveToSQL();
            }
            if (is_null($thisID) && !$del) {
                $this->addStatusMessage(sprintf(_('item %s was not removed from %s/%s/%s'),
                        $name, $this->keyword, $this->getName(), $delColumn),
                    'warning');
            } else {
                $this->addStatusMessage(sprintf(_('item %s was removed from %s/%s/%s'),
                        $name, $this->keyword, $this->getName(), $delColumn),
                    'success');
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
                    if (self::isSerialized($keyData)) {
                        $allData[$keyWord] = unserialize(stripslashes($keyData));
                    } else {
                        \Ease\Shared::webPage()->addStatusMessage(_('Deserialization error').':'.$keyData,
                            'error');
                    }
                }
            }
        }

        return $allData;
    }

    static function isSerialized($str)
    {
        $str = stripslashes($str);
        return ($str == serialize(false) || @unserialize($str) !== false);
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
                \Ease\Shared::user()->addStatusMessage('Reload: '.$line);
            }
            fclose($testing);
        }

        return TRUE;
    }

    public function cloneButton()
    {
        return new \Ease\TWB\LinkButton('?action=clone&'.$this->getKeyColumn().'='.$this->getId(),
            _('Klonovat'));
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
        $results   = [];
        $conds     = [];
        $columns[] = $this->keyColumn;
        foreach ($this->useKeywords as $keyword => $keywordInfo) {
            if (strstr($keywordInfo, 'VARCHAR')) {
                $conds[]   = " `$keyword` LIKE '%".$what."%'";
                $columns[] = "`$keyword`";
            }
        }

        $res = \Ease\Shared::db()->queryToArray("SELECT ".implode(',', $columns).",".$this->nameColumn." FROM ".$this->myTable." WHERE ".implode(' OR ',
                $conds).' ORDER BY '.$this->nameColumn, $this->keyColumn);
        foreach ($res as $result) {
            $occurences = '';
            foreach ($result as $key => $value) {
                if (strstr($value, $what)) {
                    $occurences .= '('.$key.': '.$value.') ';
                }
            }
            $results[$result[$this->keyColumn]] = [$this->nameColumn => $result[$this->nameColumn],
                'what' => $occurences];
        }
        return $results;
    }

    public function getCsv($queryRaw)
    {
        $transactions = self::getListing($queryRaw);
        $this->getCSVFile($transactions);
    }

    /**
     * Print SQL Query result in requested format
     *
     * @param type $queryRaw
     */
    public function output($queryRaw)
    {
        switch (\Ease\Shared::webPage()->getRequestValue('export')) {
            case 'csv':
                $this->getCsv($queryRaw);
                break;
            case 'pdf':
                $this->getPdf($queryRaw);
                break;

            default :
// header("Content-type: application/json");

                echo $this->getJson($queryRaw);
                break;
        }
    }

    /**
     * Prepare data to CSV export
     *
     * @param array $data
     * 
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

    /**
     * Convert raw data to html - add links to parents
     *
     * @param array $data
     *
     * @return array
     */
    public function htmlizeData($data)
    {
        if (is_array($data) && count($data)) {
            $usedCache = [];
            foreach ($data as $rowId => $row) {

                if ($this->allowTemplating && isset($row['use']) && $row['use']) {
                    $use = $row['use'];
                    if (!isset($usedCache[$use])) {
                        $used             = clone $this;
                        $used->nameColumn = 'name';
                        if ($used->loadFromSQL($use)) {
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
                                $data[$rowId][$templateKey] = '<span class="inherited" title="'._('Template').': '.$usedCache[$use]['name'].'">'.$templateValue.'</span>';
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

    /**
     * Conver raw data row to HTML (add platform icons etc.)
     *
     * @param array $row
     *
     * @return array
     */
    public function htmlizeRow($row)
    {
        if (is_array($row) && count($row)) {
            foreach ($row as $key => $value) {
                if ($key == $this->keyColumn) {
                    continue;
                }
                if (!isset($this->useKeywords[$key])) {
                    continue;
                }
                $fieldType = $this->useKeywords[$key];
                $fType     = preg_replace('/\(.*\)/', '', $fieldType);
                switch ($fType) {
                    case 'PLATFORM':
                        switch ($value) {
                            case 'windows':
                                $icon = 'logos/base/win40.gif';
                                break;
                            case 'linux':
                                $icon = 'logos/base/linux40.gif';
                                break;
                            default :
                                $icon = 'logos/unknown.gif';
                                break;
                        }
                        $row[$key] = '<img class="gridimg" src="'.$icon.'"> '.$value;
                        break;
                    case 'BOOL':
                        if (is_null($value) || !strlen($value)) {
                            $row[$key] = '<em>NULL</em>';
                        } else {
                            if ($value === '0') {
                                $row[$key] = \Ease\TWB\Part::glyphIcon('unchecked')->__toString();
                            } else {
                                if ($value === '1') {
                                    $row[$key] = \Ease\TWB\Part::glyphIcon('check')->__toString();
                                }
                            }
                        }
                        break;
                    case 'IDLIST':
                        if (!is_array($value) && strlen($value)) {
                            if (strstr($value, ':{')) {
                                $values = unserialize(stripslashes($value));
                            } else {
                                $values = ['0' => $value];
                            }
                            if (!is_array($values)) {
                                $this->addStatusMessage(sprintf(_('Unserialization error %s #%s '),
                                        $value, $key));
                            }
                            if (isset($this->keywordsInfo[$key]['refdata'])) {
                                $idcolumn     = $this->keywordsInfo[$key]['refdata']['idcolumn'];
                                $table        = $this->keywordsInfo[$key]['refdata']['table'];
                                $searchColumn = $this->keywordsInfo[$key]['refdata']['captioncolumn'];
                                $target       = str_replace('_id', '.php',
                                    $idcolumn);
                                foreach ($values as $id => $name) {
                                    if ($id) {
                                        $values[$id] = '<a title="'.$table.'" href="'.$target.'?'.$idcolumn.'='.$id.'">'.$name.'</a>';
                                    } else {
                                        $values[$id] = '<a title="'.$table.'" href="search.php?search='.$name.'&table='.$table.'&column='.$searchColumn.'">'.$name.'</a> '.\Ease\TWB\Part::glyphIcon('search');
                                    }
                                }
                            }
                            $value     = implode(',', $values);
                            $row[$key] = $value;
                        }
                        break;
                    case 'USER':
                        $row[$key] = (string) $this->ownerLink((int) $row[$key]);
                        break;
                    default :
                        if (isset($this->keywordsInfo[$key]['refdata']) && strlen(trim($value))) {
                            $table        = $this->keywordsInfo[$key]['refdata']['table'];
                            $searchColumn = $this->keywordsInfo[$key]['refdata']['captioncolumn'];
                            $row[$key]    = '<a title="'.$table.'" href="search.php?search='.$value.'&table='.$table.'&column='.$searchColumn.'">'.$value.'</a> '.\Ease\TWB\Part::glyphIcon('search');
                        }
                        if (strstr($key, 'image') && strlen(trim($value))) {
                            $row[$key] = '<img title="'.$value.'" src="logos/'.$value.'" class="gridimg">';
                        }
                        if (strstr($key, 'url')) {
                            $row[$key] = '<a href="'.$value.'">'.$value.'</a>';
                        }

                        break;
                }
            }
        }
        return $row;
    }

    /**
     * Transfer object data to another Icinga Editor instance
     */
    public function transfer($target)
    {
        if (is_null($target) || !strlen(trim($target))) {
            $this->addStatusMessage(_('Export target URL missing'), 'warning');
        } else {
            if (\Ease\Shared::user()->getSettingValue('exporturl') != $target) {
                \Ease\Shared::user()->setSettingValue('exporturl', $target);
                \Ease\Shared::user()->saveToSQL();
            }

            $data = $this->getData();
            if (!count($data)) {
                $this->addStatusMessage(sprintf(_('Transfer %s / %s se failed'),
                        get_class($this), $this->getName()), 'error');
                return false;
            }

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data),
                ],
            ];
            $context = stream_context_create($options);
            $result  = file_get_contents($target.'/importer.php?class='.$this->keyword,
                false, $context);

            if (!$result || trim($result) == 'false') {
                $this->addStatusMessage(_('Transfer failed'), 'warning');
                return true;
            } else {
                $this->addStatusMessage($result, 'success');
                return false;
            }
        }
    }

    /**
     * Configuration transfer form
     *
     * @return \\Ease\TWB\Form
     */
    public function &transferForm()
    {
        $exportForm = new \Ease\TWB\Form('Export', $this->keyword.'.php');
        $exportForm->addItem(new \Ease\Html\InputHiddenTag('action', 'export'));
        $exportForm->addItem(new \Ease\Html\InputHiddenTag($this->keyColumn,
                $this->getId()));
        $exportForm->addInput(new \Ease\Html\InputTextTag('destination',
                \Ease\Shared::user()->getSettingValue('exporturl')),
            _('Export Target'));

        $exportForm->addItem(new \Ease\Html\H4Tag(_('Recursive import')));

        foreach ($this->keywordsInfo as $columnName => $columnInfo) {
            if (isset($columnInfo['refdata']['table'])) {
                $exportForm->addInput(new \Icinga\Editor\UI\TWBSwitch('rels['.$columnName.']'),
                    $columnInfo['title']);
            }
        }

        $exportForm->addInput(new \Ease\TWB\SubmitButton(_('Export'), 'warning'));
        return $exportForm;
    }

    /**
     * Import whole data table
     *
     * @param array $data
     */
    public function importData($data)
    {
        foreach ($data as $rowId => $dataRow) {
            $this->importDataRow($dataRow);
        }
    }

    /**
     * Impoty one row of icinga 1.x configuration
     *
     * @param array $dataRow
     * 
     * @return int počet přijatých řádek
     */
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
                        $fixedValue = [];
                        foreach ($value as $item) {
                            $localId = $this->dblink->queryToValue('SELECT '.$columnInfo['refdata']['idcolumn'].' FROM '.$columnInfo['refdata']['table'].' WHERE '.$columnInfo['refdata']['captioncolumn']." = '$item'");
                            if ($localId) {
                                $fixedValue[$localId] = $item;
                            } else {
                                $this->addStatusMessage(sprintf(_('Unknown item %s column %s within import'),
                                        $item, $column));
                            }
                        }
                        $dataRow[$column] = $fixedValue;
                    }


                    break;
                case 'unknown':
                    unset($dataRow[$column]);
                    $this->addStatusMessage(sprintf(_('Unknown imported column  %s'),
                            $column));
                    break;
                default :
            }
        }
        return $this->takeData($dataRow);
    }

    /**
     * Export object ant its dependencies
     *
     * @param strig $target targer Icinga editor instance URL
     *
     * @return bool
     */
    public function transferDeps($target, $rels = null)
    {
        foreach ($this->keywordsInfo as $columnName => $columnInfo) {
            if (isset($columnInfo['refdata']['table'])) {
                if (is_array($rels) && isset($rels[$columnName])) {
                    $className = '\\Icinga\\Editor\\Engine\\'.ucfirst($columnInfo['refdata']['table']);
                    $transfer  = new $className($this->getDataValue($columnName));
                    $transfer->transfer($target);
                }
            }
        }
        return $this->transfer($target);
    }

    /**
     * Vrací typ sloupečku
     *
     * @param  string $columnName
     * @return string
     */
    function getColumnType($columnName)
    {
        $columType = null;
        if (isset($this->useKeywords[$columnName])) {
            $columnType = $this->useKeywords[$columnName];
        }
        return $columnType;
    }

    /**
     * Object info frame
     *
     * @return \Ease\Html\DlTag Vrací seznam vlastností a jejich hodnot z objektu
     */
    public function getInfoBlock()
    {
        $infoBlock = new \Ease\Html\DlTag;

        if (isset($this->nameColumn)) {
            $infoBlock->addDef(_('Name'), $this->getName());
        }

        if (isset($this->myLastModifiedColumn)) {
            $lastModify = $this->getDataValue($this->myLastModifiedColumn);
            if (!$lastModify) {
                $lastModify = _('Not yet modified');
            } else {
                $lastModify = self::sqlDateTimeToLocaleDateTime($lastModify);
            }
            $infoBlock->addDef(_('Last change'), $lastModify);
        }

        if (isset($this->myCreateColumn)) {
            $infoBlock->addDef(_('Created'),
                self::sqlDateTimeToLocaleDateTime($this->getDataValue($this->myCreateColumn)));
        }

        if (isset($this->userColumn)) {
            $infoBlock->addDef(_('Owner'), $this->ownerLinkButton());
        }

        if (isset($this->useKeywords['generate']) && !(int) $this->getDataValue('generate')) {
            $infoBlock->addItem(new \Ease\TWB\Label('warning',
                    _('do not generate to configuration')));
        }

        if ($this->publicRecords) {
            if ((int) $this->getDataValue('public')) {
                $infoBlock->addItem(new \Ease\TWB\Label('info',
                        _('record is public')));
            }
        }

        return $infoBlock;
    }

    /**
     * Conver language to local format
     *
     * @param string $sqldate SQL datum
     * @param string $format  output format
     *
     * @return string         date converted
     */
    static function sqlDateToLocaleDate($sqldate, $format = 'm/d/Y')
    {
        if ($sqldate) {
            return DateTime::createFromFormat('Y-m-d', $sqldate)->format($format);
        }
    }

    /**
     * Převede sql datum a čas do národního formátu
     *
     * @param string $sqldate SQL datum a čas
     * @param string $format  formát výstupu
     *
     * @return string         převedené datum a čas
     */
    static function sqlDateTimeToLocaleDateTime($sqldate,
                                                $format = 'm/d/Y h:i:s')
    {
        if ($sqldate) {
            return \DateTime::createFromFormat('Y-m-d H:i:s', $sqldate)->format($format);
        }
    }

    /**
     * Vrací ID objektu dle jména
     *
     * @param string $name
     * @return int
     */
    function getIdByName($name)
    {

        $id = $this->dblink->queryToValue('SELECT '.$this->getKeyColumn().' FROM '.$this->getMyTable().' WHERE '.$this->nameColumn.' LIKE \''.$this->dblink->addSlashes($name).' \'');
        if (is_numeric($id)) {
            $id = intval($id);
        }
        return $id;
    }

    /**
     * Přiřadí objektu odkaz na objekt uživatele
     *
     * @param object|\Ease\User $user         pointer to user object
     * @param object          $targetObject objekt kterému je uživatel
     *                                      přiřazován.
     *
     * @return boolean
     */
    function setUpUser(&$user, &$targetObject = null)
    {
        if (isset($this->userColumn)) {
            $this->setDataValue($this->userColumn, $user->getMyKey());
        }
        return $this->getDataValue($this->userColumn);
    }

    /**
     * Přepne vlastníka záznamů
     *
     * @param int $currentID ID stávajícího vlastníka
     * @param int $newID     ID nového vlastníka
     */
    function switchOwners($currentID, $newID)
    {
        $this->dblink->exeQuery('UPDATE '.$this->myTable." SET ".$this->userColumn." = '$newID' WHERE  ".$this->userColumn." = $currentID");
    }

    /**
     * Vrací URL konfiguračního rozhraní
     *
     * @return string
     */
    static function getBaseURL()
    {
        if (isset($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } else {
            $scheme = 'http';
        }

        $enterPoint = $scheme.'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']).'/';

//        $enterPoint = str_replace('\\', '', $enterPoint); //Win Hack
        return $enterPoint;
    }

    function csvizeRow()
    {
        
    }

    function getListingQuerySelect()
    {
        
    }

    function getListingQueryWhere()
    {
        
    }

    function operationsMenu()
    {
        
    }

    function handleUpload()
    {
        
    }

    function unsetUnknownColumns()
    {
        
    }

    function sqlColumnsToSelect()
    {
        
    }

    function getWhere()
    {
        
    }

    /**
     * Get bject Icon 
     * 
     * @return \Ease\Html\ImgTag
     */
    function getObjectIcon()
    {
        return new \Ease\Html\ImgTag($this->getObjectIconUrl(),
            $this->keyword.' #'.$this->getMyKey(),
            ['title' => $this->getObjectName()]);
    }

    /**
     * Icon Of Image that represents current object
     * @return string
     */
    public function getObjectIconUrl()
    {
        if (isset($this->iconImageColumn)) {
            $iconImage = $this->getDataValue($this->iconImageColumn);
            if (strlen($iconImage)) {
                $iconImage = 'logos/'.$iconImage;
            }
        }

        if (is_null($iconImage) || ($iconImage == 'null')) {
            $iconImage = 'logos/unknown.gif';
        }

        return $iconImage;
    }

    /**
     * Get URL Link To Current Object
     * @return string
     */
    function getObjectLink()
    {
        $link = $this->keyword.'.php';

        $id = $this->getMyKey();
        if (!is_null($id)) {
            $link .= '?'.$this->getKeyColumn().'='.$this->getMyKey();
        }

        return $link;
    }

    /**
     * Object Icon Image that link to object
     * 
     * @return \Ease\Html\ATag
     */
    public function getIconLink()
    {
        return new \Ease\Html\ATag($this->getObjectLink(),
            $this->getObjectIcon());
    }
}
