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
class EaseTWBSwitch extends EaseHtmlCheckboxTag {
    public $properties = array();
    
    function setProperties($properties){
        $this->properties = array_merge($this->properties,$properties);
    }
            
    function finalize(){
        EaseTWBPart::twBootstrapize();
        $this->includeCss('/javascript/twitter-bootstrap/css/bootstrap-switch.css');
        $this->includeJavascript('/javascript/twitter-bootstrap/js/bootstrap-switch.js');
        $this->addJavascript('$("[name=\''. $this->getTagName() .'\']").bootstrapSwitch({' . EaseTWBPart::partPropertiesToString($this->properties) . '})',null,true);
    }
}
