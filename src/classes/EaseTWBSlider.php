<?php

require_once 'Ease/EaseHtmlForm.php';
require_once 'Ease/EaseTWBootstrap.php';

/**
 * Description of EaseTWBSlider
 *
 * @author vitex
 */
class EaseTWBSlider extends EaseHtmlInputTextTag
{

    function __construct($name, $value = null, $properties = null)
    {
        if (!isset($properties['data-slider-min'])) {
            $properties['data-slider-min'] = '-20';
        }
        if (!isset($properties['data-slider-max'])) {
            $properties['data-slider-max'] = '20';
        }
        if (!isset($properties['data-slider-step'])) {
            $properties['data-slider-step'] = '1';
        }
        if (isset($value) && !isset($properties['data-slider-value'])) {
            $properties['data-slider-value'] = $value;
        }
        if (!isset($properties['data-slider-orientation'])) {
            $properties['data-slider-orientation'] = 'horizontal';
        }
        if (!isset($properties['data-slider-selection'])) {
            $properties['data-slider-selection'] = 'none';
        }
        //$properties['data-slider-tooltip'] = 'hide';

        parent::__construct($name, $value, $properties);
    }

    function finalize()
    {
        EaseShared::webPage()->includeCss('css/slider.css');
        EaseShared::webPage()->includeJavaScript('js/bootstrap-slider.js');

        $id = $this->getTagID();
        if ($id) {
            $me = '#' . $id;
        } else {
            $me = "input[name='" . $this->getTagName() . "']";
        }

        $this->addJavaScript(
            '$("' . $me . '").slider();', null, true
        );
    }

}
