<?php

namespace Icinga\Editor\UI;

/**
 * Ukládání hodnot z políčka ajaxem
 *
 * @copyright Vitex Software © 2011
 * @author Vitex <vitex@hippy.cz>
 * @package IcingaEditor
 * @subpackage WEBUI
 */

/**
 * Ukláda data z imputu přímo do databáze
 */
class TextInputSaver extends \Ease\Html\InputTextTag
{
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
     * ID Zaznamu
     * @var int
     */
    public $engine = null;

    /**
     * Input pro editaci položek uživatele
     * @param string $name
     * @param mixed  $value
     * @param \Icinga\Editor\Engine\Configurator    $engine Ukladajici trida
     * @param string $label
     * @param array  $properties
     */
    public function __construct($name, $value = NULL, $engine = null,
                                $label = NULL, $properties = NULL)
    {
        $this->engine = $engine;
        parent::__construct($name, $value, $label, $properties);
    }

    /**
     * Přidá odesílací javascript
     */
    public function finalize()
    {
        $this->setTagProperties(['OnChange' => '$.post(\'datasaver.php\', { SaverClass: \''.addslashes(get_class($this->engine)).'\', Field: \''.$this->getTagProperty('name').'\', Value: this.value, Key: '.$this->engine->getMyKey().' } )']);
//        $this->enclosedElement->SetTagProperties(array('OnChange' => '$.ajax( { type: \"POST\", url: \"DataSaver.php\", data: \"SaverClass=' . get_class($this) . '&amp;Field=' . $this->enclosedElement->GetTagProperty('name') . '&amp;Value=\" + this.value , async: false, success : function () { alert (this); }, statusCode: { 404: function () { alert(\'page not found\');} } }); '));
    }

    /**
     * Uloží data, pokud se to nepovede, pokusí se vytvořit chybějící sloupečky
     * a vrátí vysledek dalšího uložení
     * @param  array   $data
     * @param  boolean $SearchForID
     * @return int
     */
    public function saveToSQL($data = NULL, $SearchForID = false)
    {
        if (is_null($data)) {
            $data = $this->GetData();
        }
        $SaveResult = parent::SaveToSQL($data, $SearchForID);
        if (is_null($SaveResult)) {
            if ($this->CreateMissingColumns($data) > 0) {
                $SaveResult = parent::SaveToSQL($data, $SearchForID);
            }
        }

        return $SaveResult;
    }

    /**
     * Vytvoří v databázi sloupeček pro uložení hodnoty widgetu
     * @param  array $data
     * @return int
     */
    public function createMissingColumns($data = NULL)
    {
        if (is_null($data)) {
            $this->GetData();
        }
        unset($data[$this->GetmyKeyColumn()]);
        $KeyName = current(array_keys($data));

        return EaseDbMySqli::CreateMissingColumns($this,
                [$KeyName => str_repeat(' ', 1000)]);
    }

    /**
     * Přiřadí objektu uživatele a nastaví DB
     * @param  EaseUser     $user
     * @param  object|mixed $TargetObject
     * @return boolen
     */
    public function setUpUser(&$user, &$TargetObject = NULL)
    {
        $this->SetMyKey($user->GetUserID());

        return parent::SetUpUser($user, $TargetObject);
    }
}