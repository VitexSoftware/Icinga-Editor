<?php

/**
 * Volba contactů sledovaných danou službou
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEContactSelector extends EaseContainer
{

    public $myKeyColumn = 'service_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEService|IEHost $holder
     */
    public function __construct($holder)
    {
        $contactsAssigned = array();
        parent::__construct();
        $fieldName = $this->getmyKeyColumn();
        $initialContent = new EaseHtmlFieldSet(_('Cíle notifikací'));
        $initialContent->setTagCss(array('width' => '100%'));

        if (is_null($holder->getMyKey())) {
            $initialContent->addItem(_('Nejprve je potřeba uložit záznam'));
        } else {
            $serviceName = $holder->getName();
            $contact = new IEContact();
            $allContacts = $contact->getListing(null, true, array('alias', 'parent_id'));
            foreach ($holder->getDataValue('contacts') as $contactId => $contactName) {
                if (isset($allContacts[$contactId])) {
                    $contactsAssigned[$contactId] = $allContacts[$contactId];
                }
            }
            
            foreach ($allContacts as $contactID => $contactInfo) {
                if ($contactInfo['register'] != 1) {
                    unset($allContacts[$contactID]);
                }
                if (!$contactInfo['parent_id']) {
                    unset($allContacts[$contactID]);
                }
                
            }

            foreach ($contactsAssigned as $contactID => $contactInfo) {
                unset($allContacts[$contactID]);
            }

            if (count($allContacts)) {

                foreach ($allContacts as $contactID => $contactInfo) {
                    $initialContent->addItem(
                            new EaseTWBButtonDropdown(
                            $contactInfo[$contact->nameColumn], 'inverse', 'xs', array(
                        new EaseHtmlATag('contacttweak.php?contact_id=' . $contactInfo['parent_id'] . '&amp;service_id=' . $holder->getId(), EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace')),
                        new EaseHtmlATag('?addcontact=' . $contactInfo[$contact->nameColumn] . '&amp;contact_id=' . $contactID . '&amp;' . $holder->getmyKeyColumn() . '=' . $holder->getMyKey() . '&amp;' . $holder->nameColumn . '=' . $holder->getName(), EaseTWBPart::GlyphIcon('plus') . ' ' . _('Začít obesílat'))
                    )));
                }
            }

            if (count($contactsAssigned)) {
                $initialContent->addItem('<br/>');
                foreach ($contactsAssigned as $contactID => $contactInfo) {

                    $initialContent->addItem(
                            new EaseTWBButtonDropdown(
                            $contactInfo[$contact->nameColumn], 'success', 'xs', array(
                        new EaseHtmlATag(
                                '?delcontact=' . $contactInfo[$contact->nameColumn] . '&amp;contact_id=' . $contactID . '&amp;' . $holder->getmyKeyColumn() . '=' . $holder->getMyKey() . '&amp;' . $holder->nameColumn . '=' . $holder->getName(), EaseTWBPart::GlyphIcon('remove') . ' ' . _('Přestat obesílat'))
                        , new EaseHtmlATag('contacttweak.php?contact_id=' . $contactInfo['parent_id'] . '&amp;service_id=' . $holder->getId(), EaseTWBPart::GlyphIcon('wrench') . ' ' . _('Editace'))
                            )
                            )
                    );
                }
            }
        }
        $this->addItem($initialContent);
    }

}
