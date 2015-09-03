<?php

/**
 * Formulář se sloupci
 *
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class IEColumnsForm extends EaseTWBForm
{

    /**
     * Šířka sloupce.
     *
     * @var int
     */
    public $colsize = 4;

    /**
     * Řádek.
     *
     * @var EaseTWBRow
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
     * @param string $formName      jméno formuláře
     * @param string $formAction    cíl formulář např login.php
     * @param string $formMethod    metoda odesílání POST|GET
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                              array('enctype' => 'multipart/form-data')
     */
    public function __construct($formName, $formAction = null, $formMethod = 'post', $formContents = null, $tagProperties = null)
    {
        parent::__construct($formName, $formAction, $formMethod, $formContents, $tagProperties);
        $this->row = $this->addItem(new EaseTWBRow());
    }

    /**
     * Vloží prvek do sloupce formuláře.
     *
     * @param mixed  $input       Vstupní prvek
     * @param string $caption     Popisek
     * @param string $placeholder předvysvětlující text
     * @param string $helptext    Dodatečná nápověda
     */
    public function addInput($input, $caption = null, $placeholder = null, $helptext = null)
    {
        if ($this->row->getItemsCount() > $this->itemsPerRow) {
            $this->row = $this->addItem(new EaseTWBRow());
        }

        return $this->row->addItem(new EaseTWBCol($this->colsize, new EaseTWBFormGroup($caption, $input, $placeholder, $helptext)));
    }

}
