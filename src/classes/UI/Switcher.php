<?php
namespace Icinga\Editor\UI;


/**
 * Description of \Ease\TWB\Switch
 *
 * @author vitex
 */
class Switcher extends \Ease\Html\CheckboxTag {
    public $properties = array();
    
    function setProperties($properties){
        $this->properties = array_merge($this->properties,$properties);
    }
            
    function finalize(){
        \Ease\TWB\Part::twBootstrapize();
        $this->includeCss('/javascript/twitter-bootstrap/css/bootstrap-switch.css');
        $this->includeJavascript('/javascript/twitter-bootstrap/js/bootstrap-switch.js');
        $this->addJavascript('$("[name=\''. $this->getTagName() .'\']").bootstrapSwitch({' . \Ease\TWB\Part::partPropertiesToString($this->properties) . '})',null,true);
    }
}
