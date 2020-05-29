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
class ServiceSwapForm extends \Ease\TWB\Form {

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
    function __construct($service) {
        parent::__construct('swapService', null, 'GET');
        $this->service = $service;
    }

    function finalize() {
        $addNewItem = new ServiceSelect('new_service_id');

        $this->addItem(new \Ease\TWB\FormGroup(_('Replacement Service'),
                        $addNewItem, _('Service Name'),
                        sprintf(_('This service is replacement for the selected service %s for all guests who use it'),
                                '<strong>' . $this->service->getName() . '</strong>')
        ));

        $this->addItem(new \Ease\TWB\FormGroup(_('Replace'),
                        new \Ease\TWB\SubmitButton(_('Replace service') . ' ' . \Ease\TWB\Part::GlyphIcon('flash'),
                                'info')));

        $this->addItem(new \Ease\Html\InputHiddenTag('service_id',
                        $this->service->getID()));
        $this->addItem(new \Ease\Html\InputHiddenTag('action', 'swap'));
    }

}
