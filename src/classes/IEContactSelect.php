<?php

/**
 * Volba kontaktu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class IEContactSelect extends EaseHtmlSelect
{

    /**
     * Objekt kontaktu
     * @var IEContact
     */
    public $contact = null;

    /**
     * Contact select box
     *
     * @param string $name         jmeno
     * @param array  $items        polozky
     * @param mixed  $defaultValue id predvolene polozky
     * @param array  $itemsIDs     id položek
     * @param array  $properties   tag properties
     */
    public function __construct($name, $items = null, $defaultValue = null, $itemsIDs = false, $properties = null)
    {
        $this->contact = new IEContact;
        parent::__construct($name, $items, $defaultValue, $itemsIDs, $properties);
    }

    function loadItems()
    {
        $items = array();
        $contacts = $this->contact->getColumnsFromMySQL(array($this->contact->myKeyColumn, $this->contact->nameColumn), null, $this->contact->nameColumn);
        if (count($contacts)) {
            foreach ($contacts as $contact) {
                $items[$contact[$this->contact->myKeyColumn]] = $contact[$this->contact->nameColumn];
            }
        }
        return $items;
    }

    public function finalize()
    {
        parent::finalize();
        EaseShared::webPage()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown();', null, true);
        EaseShared::webPage()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        EaseShared::webPage()->includeCss('css/msdropdown/dd.css');
    }

}
