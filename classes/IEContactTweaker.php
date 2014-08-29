<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'classes/IECommand.php';

/**
 * Description of IEContactTweaker
 *
 * @author vitex
 */
class IEContactTweaker extends EaseHtmlDivTag
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
    public $subcontactTypes = array('email', 'jabber', 'sms', 'twitter');
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
        $this->subcontactTypes = array_combine($this->subcontactTypes, $this->subcontactTypes);

        $this->contact = $contact;

        $this->addItem(new EaseHtmlDivTag(null, _('Založeno') . ': ' . $this->contact->getDataValue('DatCreate')));
        $oPage = EaseShared::webPage();
        if ($oPage->isPosted()) {
            $oldId = $this->contact->getId();
            $contactType = $oPage->getRequestValue('contact');
            $contactData = $oPage->getRequestValue('cnt');
            if (isset($contactType) && strlen($contactData)) {
                if ($this->contact->fork(array($contactType => $contactData))) {
                    $this->addStatusMessage(sprintf(_('Kontaktní údaj %s %s byl přidán'),$contactType,$contactData),'success');
                    $this->cnt = '';
                } else {
                    $this->addStatusMessage(sprintf(_('Kontaktní údaj %s %s nebyl přidán'),$contactType,$contactData),'error');
                    $this->cnt = EaseShared::webPage()->getRequestValue('cnt');
                }
            }
            $this->contact->loadFromMySQL($oldId);
        }
    }

    public function finalize()
    {
        $subcontatcts = $this->contact->getChilds();
        foreach ($subcontatcts as $subcontatctID => $subcontatctInfo) {
            $this->addItem(
                            new EaseTWBButtonDropdown(
                            $subcontatctInfo['type'].' '.$subcontatctInfo['contact'], 'success', 'xs', array(
                                new EaseHtmlATag('contact.php?parent_id='.$this->contact->getId().'&contact_id='.$subcontatctID, EaseTWBPart::GlyphIcon('wrench'). ' ' . _('Vlastnosti') ),
                                new EaseHtmlATag('?contact_id='.$this->contact->getId().'&delsubcont_id='.$subcontatctID, EaseTWBPart::GlyphIcon('minus') . ' '._('smazat').' '. $subcontatctInfo['type'])
                                                                    )
                           )
                    );

            unset($this->subcontactTypes[$subcontatctInfo['type']]);
            $this->addItem('<br/>');
        }

        if (count($this->subcontactTypes)) {

        $form = new EaseTWBForm('ContatctTweak','contacttweak.php');
        $form->addItem(new EaseHtmlSelect('contact', $this->subcontactTypes));
        $form->addItem(new EaseHtmlInputHiddenTag('contact_id',  $this->contact->getId()));
        $form->addItem(
                new EaseTWBFormGroup(_('Kontakt'),
                        new EaseHtmlInputTextTag('cnt',  $this->cnt),
                        EaseShared::webPage()->getRequestValue('cnt'),
                        _('telefonní číslo, email či jabberová adresa dle druhu kontaktu')
                        )
        );
        $form->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

        $this->addItem(new EaseHtmlFieldSet(_('Přidat kontaktní údaj'), $form));
        } else {
            $this->addItem(new EaseHtmlDivTag('plno',_('K tomuto kontaktu již není možné přidávat další údaje.'),array('class'=>'well warning','style'=>'margin: 10px')));
        }
    }

}
