<?php

/**
 * Description of IEFXPreloader
 *
 * @author vitex
 */
class IEFXPreloader extends EaseHtmlDivTag
{

    public function __construct($id = null)
    {
        parent::__construct($id, null, array('class' => 'loader', 'data-initialize' => 'loader'));
    }

    public function finalize()
    {
        EaseShared::webPage()->includeCss('twitter-bootstrap/css/fuelux.css', true);
        EaseShared::webPage()->includeJavascript("/javascript/twitter-bootstrap/fuelux.js");
        EaseShared::webPage()->addJavascript("$('#" . $this->getTagID() . "').loader();");
        EaseShared::webPage()->addCSS('
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
