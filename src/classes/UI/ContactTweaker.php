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
     * @var IEContact
     */
    public $contact = null;

    /**
     * Objekt Hosta
     * @var IEHost
     */
    public $host = null;

    /**
     *
     * @var type
     */
    public $configurator = null;

    /**
     *
     * @var type
     */
    public $subcontactTypes = ['email', 'jabber', 'sms', 'twitter'];

    /**
     *
     * @var type
     */
    private $cnt;

    /**
     * Umožňuje měnit parametry služeb
     *
     * @param IEContact $contact
     * @param IEHost    $host    ObjektHostu
     */
    public function __construct($contact)
    {
        parent::__construct();
        $this->subcontactTypes = array_combine($this->subcontactTypes,
            $this->subcontactTypes);

        $this->contact = $contact;

        $this->addItem(new \Ease\Html\Div(_('Založeno').': '.$this->contact->getDataValue('DatCreate')));
        $oPage = \Ease\Shared::webPage();
        if ($oPage->isPosted()) {
            $oldId       = $this->contact->getId();
            $contactType = $oPage->getRequestValue('contact');
            $contactData = $oPage->getRequestValue('cnt');
            if (isset($contactType) && strlen($contactData)) {
                if ($this->contact->fork([$contactType => $contactData])) {
                    $this->addStatusMessage(sprintf(_('Kontaktní údaj %s %s byl přidán'),
                            $contactType, $contactData), 'success');
                    $this->cnt = '';
                } else {
                    $this->addStatusMessage(sprintf(_('Kontaktní údaj %s %s nebyl přidán'),
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
                    \Ease\TWB\Part::GlyphIcon('wrench').' '._('Vlastnosti')),
                new \Ease\Html\ATag('?contact_id='.$this->contact->getId().'&delsubcont_id='.$subcontatctID,
                    \Ease\TWB\Part::GlyphIcon('minus').' '._('smazat').' '.$subcontatctInfo['type'])
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
                new \Ease\TWB\FormGroup(_('Kontakt'),
                new \Ease\Html\InputTextTag('cnt', $this->cnt),
                \Ease\Shared::webPage()->getRequestValue('cnt'),
                _('telefonní číslo, email či jabberová adresa dle druhu kontaktu')
                )
            );
            $form->addItem(new \Ease\TWB\SubmitButton(_('Uložit'), 'success'));

            $this->addItem(new \Ease\TWB\Panel(_('Přidat kontaktní údaj'),
                'default', $form));
        } else {
            $this->addItem(new \Ease\Html\Div(
                _('K tomuto kontaktu již není možné přidávat další údaje.'),
                ['class' => 'well warning', 'style' => 'margin: 10px', 'id' => 'plno']));
        }
    }
}