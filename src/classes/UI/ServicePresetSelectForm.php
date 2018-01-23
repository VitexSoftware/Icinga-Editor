<?php

namespace Icinga\Editor\UI;

/**
 * Service Preset Select Form
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015-2018 Vitex@hippy.cz (G)
 */
class ServicePresetSelectForm extends \Ease\TWB\Form
{

    /**
     * Form for service preset select
     */
    public function __construct()
    {
        parent::__construct('ServicePresetSelForm', null, 'POST');
        $this->addItem(new \Ease\Html\InputHiddenTag('action', 'applystemplate'));
        $this->addItem(new StemplateSelect('stemplate_id'));
    }

    public function finalize()
    {
            $this->addItem(new \Ease\TWB\SubmitButton(_('Apply services Preset'),
            'success'));
        parent::finalize();
    }

}
