<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once 'Ease/EaseTWBootstrap.php';
require_once 'Ease/EaseHtmlForm.php';

/**
 * Description of EaseTWBSwitch
 *
 * @author vitex
 */
class IETWBSwitch extends EaseHtmlCheckboxTag
{

    public $properties = array();

    /**
     * Zobrazuje HTML Checkbox
     *
     * @param string $name       jméno tagu
     * @param bool   $checked    stav checkboxu
     * @param string $value      vracená hodnota checkboxu
     * @param array  $properties parametry tagu
     */
    public function __construct($name, $checked = false, $value = null, $properties = null)
    {
        parent::__construct($name, $checked, $value, $properties);
        if (!isset($properties['onText'])) {
            $properties['onText'] = _('Ano');
        }
        if (!isset($properties['offText'])) {
            $properties['offText'] = _('Ne');
        }

        $this->setProperties($properties);
    }

    function setProperties($properties)
    {
        $this->properties = array_merge($this->properties, $properties);
    }

    function finalize()
    {
        EaseTWBPart::twBootstrapize();
        $this->includeCss('/javascript/twitter-bootstrap/css/bootstrap-switch.css');
        $this->includeJavascript('/javascript/twitter-bootstrap/js/bootstrap-switch.js');
        $this->addJavascript('$("[name=\'' . $this->getTagName() . '\']").bootstrapSwitch({' . EaseTWBPart::partPropertiesToString($this->properties) . '})', null, true);
    }

}
