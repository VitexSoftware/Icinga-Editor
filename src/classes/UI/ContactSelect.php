<?php

namespace Icinga\Editor\UI;

/**
 * Volba kontaktu
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class ContactSelect extends \Ease\Html\Select {

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
    public function __construct($name, $items = null, $defaultValue = null,
            $itemsIDs = false, $properties = null) {
        $this->contact = new \Icinga\Editor\Engine\Contact();
        parent::__construct($name, $items, $defaultValue, $itemsIDs, $properties);
    }

    function loadItems() {
        $items = [];
        $contacts = $this->contact->getColumnsFromSQL([$this->contact->keyColumn,
            $this->contact->nameColumn], null, $this->contact->nameColumn);
        if (count($contacts)) {
            foreach ($contacts as $contact) {
                $items[$contact[$this->contact->keyColumn]] = $contact[$this->contact->nameColumn];
            }
        }
        return $items;
    }

    public function finalize() {
        parent::finalize();
        \Ease\WebPage::singleton()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown();',
                null, true);
        \Ease\WebPage::singleton()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        \Ease\WebPage::singleton()->includeCss('css/msdropdown/dd.css');
    }

}
