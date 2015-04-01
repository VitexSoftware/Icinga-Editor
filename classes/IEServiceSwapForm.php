<?php

/**
 * Formulář výměny jedné služby za druhou
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEServiceSwapForm extends EaseTWBForm
{

    /**
     * Služba
     * @var IEService
     */
    public $service = null;

    /**
     * Formulář výměny jedné služby za jinou
     *
     * @param IEService $service
     */
    function __construct($service)
    {
        parent::__construct('swapService', null, 'GET');
        $this->service = $service;
    }

    function finalize()
    {
        $addNewItem = new IEServiceSelect('new_service_id');

        $this->addItem(new EaseTWBFormGroup(_('Náhradní služba'), $addNewItem, _('Jméno služby'), sprintf(_('Tato služba se zamění za právě zvolenou službu <strong>%s</strong> u všech hostů kteří ji používají'), $this->service->getName())
        ));

        $this->addItem(new EaseTWBFormGroup(_('Vyměnit'), new EaseTWSubmitButton(_('Vyměnit službu') . ' ' . EaseTWBPart::GlyphIcon('flash'), 'info')));

        $this->addItem(new EaseHtmlInputHiddenTag('service_id', $this->service->getID()));
        $this->addItem(new EaseHtmlInputHiddenTag('action', 'swap'));
    }

}
