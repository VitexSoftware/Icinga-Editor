<?php

/**
 * Ukládání hodnot z políčka ajaxem
 * 
 * @copyright Vitex Software © 2011
 * @author Vitex <vitex@hippy.cz>
 * @package IcingaEditor
 * @subpackage WEBUI
 */require_once 'Ease/EaseHtmlForm.php';

/**
 * Ukláda data z imputu přímo do databáze
 */
class IETextInputSaver extends EaseLabeledTextInput {

    /**
     * Pracujeme s tabulkou mains
     * @var string 
     */
    public $myTable = 'user';
    
    /**
     * Sloupeček pro poslední modifikaci
     * @var type 
     */
    public $myLastModifiedColumn = 'DatSave';


    /**
     * Input pro editaci položek uživatele
     * @param string $name
     * @param mixed $value
     * @param string $Label
     * @param int $UserID
     * @param array $Properties 
     */
    function __construct($name, $value = NULL, $Label = NULL, $Properties = NULL) {
        parent::__construct($name, $value, $Label, $Properties);
    }

    /**
     * Přidá odesílací javascript
     */
    function finalize() {
        parent::Finalize();
        $this->enclosedElement->SetTagProperties(array('OnChange' => '$.post(\'DataSaver.php\', { SaverClass: \'' . get_class($this) . '\', Field: \'' . $this->enclosedElement->GetTagProperty('name') . '\', Value: this.value } )'));
//        $this->enclosedElement->SetTagProperties(array('OnChange' => '$.ajax( { type: \"POST\", url: \"DataSaver.php\", data: \"SaverClass=' . get_class($this) . '&amp;Field=' . $this->enclosedElement->GetTagProperty('name') . '&amp;Value=\" + this.value , async: false, success : function() { alert (this); }, statusCode: { 404: function() { alert(\'page not found\');} } }); '));
    }

    /**
     * Uloží data, pokud se to nepovede, pokusí se vytvořit chybějící sloupečky 
     * a vrátí vysledek dalšího uložení
     * @param array $data
     * @param boolean $SearchForID
     * @return int 
     */
    function saveToMySQL($data = NULL, $SearchForID = false) {
        if (is_null($data)) {
            $data = $this->GetData();
        }
        $SaveResult = parent::SaveToMySQL($data, $SearchForID);
        if (is_null($SaveResult)) {
            if ($this->CreateMissingColumns($data) > 0) {
                $SaveResult = parent::SaveToMySQL($data, $SearchForID);
            }
        }
        return $SaveResult;
    }

    /**
     * Vytvoří v databázi sloupeček pro uložení hodnoty widgetu
     * @param array $data
     * @return int 
     */
    function createMissingColumns($data = NULL) {
        if (is_null($data)) {
            $this->GetData();
        }
        unset($data[$this->GetmyKeyColumn()]);
        $KeyName = current(array_keys($data));
        return EaseDbMySqli::CreateMissingColumns($this, array($KeyName => str_repeat(' ', 1000)));
    }

    /**
     * Přiřadí objektu uživatele a nastaví DB
     * @param EaseUser $User
     * @param object|mixed $TargetObject
     * @return boolen 
     */
    function setUpUser(&$User, &$TargetObject = NULL) {
        $this->SetMyKey($User->GetUserID());
        return parent::SetUpUser($User, $TargetObject);
    }
    
}

?>
