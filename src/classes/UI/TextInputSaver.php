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
        $this->setTagProperties(['OnChange' => '$.post(\'datasaver.php\', { SaverClass: \''.addslashes(get_class($this->engine)).'\', Field: \''.$this->getTagProperty('name').'\', Value: this.value, Key: '.$this->engine->getMyKey().', success : function () { alert (this); } } )']);
//        $this->enclosedElement->SetTagProperties(array('OnChange' => '$.ajax( { type: \"POST\", url: \"DataSaver.php\", data: \"SaverClass=' . get_class($this) . '&amp;Field=' . $this->enclosedElement->GetTagProperty('name') . '&amp;Value=\" + this.value , async: false, success : function () { alert (this); }, statusCode: { 404: function () { alert(\'page not found\');} } }); '));
    }

}
