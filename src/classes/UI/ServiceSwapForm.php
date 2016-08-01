<?php

namespace Icinga\Editor\UI;

/**
 * Formulář výměny jedné služby za druhou
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class ServiceSwapForm extends \Ease\TWB\Form
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
        $addNewItem = new ServiceSelect('new_service_id');

        $this->addItem(new \Ease\TWB\FormGroup(_('Náhradní služba'),
            $addNewItem, _('Jméno služby'),
            sprintf(_('Tato služba se zamění za právě zvolenou službu <strong>%s</strong> u všech hostů kteří ji používají'),
                $this->service->getName())
        ));

        $this->addItem(new \Ease\TWB\FormGroup(_('Vyměnit'),
            new \Ease\TWB\SubmitButton(_('Vyměnit službu').' '.\Ease\TWB\Part::GlyphIcon('flash'),
            'info')));

        $this->addItem(new \Ease\Html\InputHiddenTag('service_id',
            $this->service->getID()));
        $this->addItem(new \Ease\Html\InputHiddenTag('action', 'swap'));
    }

}
