<?php

namespace Icinga\Editor\UI;

/**
 * Description of SensorConfirmForm
 *
 * @author vitex
 */
class SensorConfirmForm extends \Ease\TWB\Form {

    /**
     * Formulář pro potvrzení nasazení senzoru
     * 
     * @param \Icinga\Editor\Engine\Host $host
     */
    public function __construct($host) {
        parent::__construct(['name' => 'sensor']);
        $this->addItem(new \Ease\Html\InputHiddenTag('operation', 'confirm'));
        $this->addItem(new \Ease\Html\InputHiddenTag($host->getKeyColumn(),
                        $host->getId()));
        $this->addItem(new \Ease\TWB\SubmitButton(_('Sensor deployed')));
        $status = $host->getSensorStatus();
        $this->addItem(new YesNoSwitch('confirm', ($status == 2)));
    }

}
