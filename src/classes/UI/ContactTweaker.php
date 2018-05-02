<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEContactTweaker
 *
 * @author vitex
 */
class ContactTweaker extends \Ease\Html\Div
{
    /**
     * Objekt služby
     * @var Contact
     */
    public $contact = null;

    /**
     * Objekt Hosta
     * @var Host
     */
    public $host = null;

    /**
     *
     * @var 
     */
    public $configurator = null;

    /**
     * Subcontact types used
     * @var array
     */
    public $subcontactTypes = ['email', 'jabber', 'sms', 'redmine'];

    /**
     *
     * @var type
     */
    private $cnt;

    /**
     * Umožňuje měnit parametry služeb
     *
     * @param Contact $contact
     * @param Host    $host    ObjektHostu
     */
    public function __construct($contact)
    {
        parent::__construct();
        $this->subcontactTypes = array_combine($this->subcontactTypes,
            $this->subcontactTypes);

        $this->contact = $contact;

        $this->addItem(new \Ease\Html\Div(_('Created').': '.$this->contact->getDataValue('DatCreate')));
        $oPage = \Ease\Shared::webPage();
        if ($oPage->isPosted()) {
            $oldId       = $this->contact->getId();
            $contactType = $oPage->getRequestValue('contact');
            $contactData = $oPage->getRequestValue('cnt');
            if (isset($contactType) && strlen($contactData)) {
                if ($this->contact->fork([$contactType => $contactData])) {
                    $this->addStatusMessage(sprintf(_('Contact information %s %s was added'),
                            $contactType, $contactData), 'success');
                    $this->cnt = '';
                } else {
                    $this->addStatusMessage(sprintf(_('Contact information %s %s was not saved'),
                            $contactType, $contactData), 'error');
                    $this->cnt = \Ease\Shared::webPage()->getRequestValue('cnt');
                }
            }
            $this->contact->loadFromSQL($oldId);
        }
    }

    public function finalize()
    {
        $subcontatcts = $this->contact->getChilds();
        foreach ($subcontatcts as $subcontatctID => $subcontatctInfo) {
            $this->addItem(
                new \Ease\TWB\ButtonDropdown(
                    $subcontatctInfo['type'].' '.$subcontatctInfo['contact'],
                    'success', 'xs',
                    [
                    new \Ease\Html\ATag('contact.php?parent_id='.$this->contact->getId().'&contact_id='.$subcontatctID,
                        \Ease\TWB\Part::GlyphIcon('wrench').' '._('Properties')),
                    new \Ease\Html\ATag('?contact_id='.$this->contact->getId().'&delsubcont_id='.$subcontatctID,
                        \Ease\TWB\Part::GlyphIcon('minus').' '._('Delete').' '.$subcontatctInfo['type'])
                    ]
                )
            );

            unset($this->subcontactTypes[$subcontatctInfo['type']]);
            $this->addItem('<br/>');
        }

        if (count($this->subcontactTypes)) {

            $form = new \Ease\TWB\Form('ContatctTweak', 'contacttweak.php');
            $form->addItem(new \Ease\Html\Select('contact',
                    $this->subcontactTypes));
            $form->addItem(new \Ease\Html\InputHiddenTag('contact_id',
                    $this->contact->getId()));
            $form->addItem(
                new \Ease\TWB\FormGroup(_('Contact'),
                    new \Ease\Html\InputTextTag('cnt', $this->cnt,
                        ['maxlength' => 80]),
                    \Ease\Shared::webPage()->getRequestValue('cnt'),
                    _('phone number, email or jabber address by contact type - for redmine please use format https://apikey@redmine.you.com/?project=redmineproject ')
                )
            );
            $form->addItem(new \Ease\TWB\SubmitButton(_('Save'), 'success'));

            $this->addItem(new \Ease\TWB\Panel(_('Add contact information'),
                    'default', $form));
        } else {
            $this->addItem(new \Ease\Html\Div(
                    _('You can not add more data to this contact.'),
                    ['class' => 'well warning', 'style' => 'margin: 10px', 'id' => 'plno']));
        }
    }
}
