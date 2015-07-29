<?php

/**
 * Description of IESensorConfirmForm
 *
 * @author vitex
 */
class IESensorConfirmForm extends EaseTWBForm
{

    /**
     * Formulář pro potvrzení nasazení senzoru
     * @param IEHost $host
     */
    public function __construct($host)
    {
        parent::__construct('sensor');
        $this->addItem(new EaseHtmlInputHiddenTag('operation', 'confirm'));
        $this->addItem(new EaseHtmlInputHiddenTag($host->getmyKeyColumn(), $host->getId()));
        $this->addItem(new EaseTWSubmitButton(_('Senzor je nasazen')));
        $status = $host->getSensorStatus();
        $this->addItem(new IETWBSwitch('confirm', ($status == 2)));
    }

}
