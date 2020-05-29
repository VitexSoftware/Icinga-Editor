<?php

namespace Icinga\Editor\UI;

/**
 * Formulář se sloupci
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class ColumnsForm extends \Ease\TWB\Form {

    /**
     * Šířka sloupce.
     *
     * @var int
     */
    public $colsize = 4;

    /**
     * Řádek.
     *
     * @var \Ease\TWB\Row
     */
    public $row = null;

    /**
     * Počet položek na řádek.
     *
     * @var int
     */
    public $itemsPerRow = 4;

    /**
     * Formulář Bootstrapu.
     *
     * @param string $properties    Form roperties. eg array('enctype' => 'multipart/form-data')
     * @param mixed  $formContents  prvky uvnitř formuláře
     */
    public function __construct($properties = [], $formContents = null) {
        parent::__construct($properties, $formContents);
        $this->row = $this->addItem(new \Ease\TWB\Row());
    }

    /**
     * Vloží prvek do sloupce formuláře.
     *
     * @param mixed  $input       Vstupní prvek
     * @param string $caption     Popisek
     * @param string $placeholder předvysvětlující text
     * @param string $helptext    Dodatečná nápověda
     */
    public function addInput($input, $caption = null, $placeholder = null,
            $helptext = null) {
        if ($this->row->getItemsCount() > $this->itemsPerRow) {
            $this->row = $this->addItem(new \Ease\TWB\Row());
        }

        return $this->row->addItem(new \Ease\TWB\Col($this->colsize,
                                new \Ease\TWB\FormGroup($caption, $input, $placeholder,
                                        $helptext)));
    }

}
