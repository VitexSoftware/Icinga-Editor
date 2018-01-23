<?php

namespace Icinga\Editor\UI;

/**
 * Select contacts for service
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2018 Vitex@hippy.cz (G)
 */
class ContactSelector extends \Ease\Container
{
    public $KeyColumn = 'service_name';

    /**
     * Editor k přidávání členů skupiny
     *
     * @param IEService|IEHost $holder
     */
    public function __construct($holder)
    {
        $contactsAssigned = [];
        parent::__construct();
        $fieldName        = $holder->getKeyColumn();
        $initialContent   = new \Ease\TWB\Panel(_('Contacts'));
        $initialContent->setTagCss(['width' => '100%']);

        if (is_null($holder->getMyKey())) {
            $initialContent->addItem(_('Please saver record first'));
        } else {
            $serviceName = $holder->getName();
            $contact     = new \Icinga\Editor\Engine\Contact();
            $allContacts = $contact->getListing(null, true,
                ['alias', 'parent_id']);
            $contacts    = $holder->getDataValue('contacts');
            if (count($contacts)) {
                foreach ($contacts as $contactId => $contactName) {
                    if (isset($allContacts[$contactId])) {
                        $contactsAssigned[$contactId] = $allContacts[$contactId];
                    }
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
                        new \Ease\TWB\ButtonDropdown(
                        $contactInfo[$contact->nameColumn], 'inverse', 'xs',
                        [
                        new \Ease\Html\ATag('contacttweak.php?contact_id='.$contactInfo['parent_id'].'&amp;service_id='.$holder->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Edit')),
                        new \Ease\Html\ATag('?addcontact='.$contactInfo[$contact->nameColumn].'&amp;contact_id='.$contactID.'&amp;'.$holder->getKeyColumn().'='.$holder->getMyKey().'&amp;'.$holder->nameColumn.'='.$holder->getName(),
                            \Ease\TWB\Part::GlyphIcon('plus').' '._('Start notifing'))
                    ]));
                }
            }

            if (count($contactsAssigned)) {
                $initialContent->addItem('<br/>');
                foreach ($contactsAssigned as $contactID => $contactInfo) {

                    $initialContent->addItem(
                        new \Ease\TWB\ButtonDropdown(
                        $contactInfo[$contact->nameColumn], 'success', 'xs',
                        [
                        new \Ease\Html\ATag(
                            '?delcontact='.$contactInfo[$contact->nameColumn].'&amp;contact_id='.$contactID.'&amp;'.$holder->getKeyColumn().'='.$holder->getMyKey().'&amp;'.$holder->nameColumn.'='.$holder->getName(),
                            \Ease\TWB\Part::GlyphIcon('remove').' '._('Stop notifing'))
                        , new \Ease\Html\ATag('contacttweak.php?contact_id='.$contactInfo['parent_id'].'&amp;service_id='.$holder->getId(),
                            \Ease\TWB\Part::GlyphIcon('wrench').' '._('Edit'))
                        ]
                        )
                    );
                }
            }
        }
        $this->addItem($initialContent);
    }

}
