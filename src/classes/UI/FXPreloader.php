<?php

namespace Icinga\Editor\UI;

/**
 * Description of UI\FXPreloader
 *
 * @author vitex
 */
class FXPreloader extends \Ease\Html\DivTag {

    public function __construct($id = null) {
        parent::__construct($id, null,
                ['class' => 'loader', 'data-initialize' => 'loader']);
    }

    public function finalize() {
        $this->includeCss('twitter-bootstrap/css/fuelux.css',
                true);
        $this->includeJavascript("/javascript/twitter-bootstrap/fuelux.js");
        $this->addJavascript("$('#" . $this->getTagID() . "').loader();");
        $this->addCSS('
#' . $this->getTagID() . '{
    position: absolute;
    top: 50%;
    left: 50%;
    margin-top: -50px;
    margin-left: -50px;
    width: 100px;
    height: 100px;
    visibility: hidden;
}​
            ');
    }

}
