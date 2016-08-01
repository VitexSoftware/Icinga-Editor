<?php

namespace Icinga\Editor\UI;

/**
 * Description of \Ease\TWB\Slider
 *
 * @author vitex
 */
class Slider extends \Ease\Html\InputTextTag
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
        \Ease\Shared::webPage()->includeCss('css/slider.css');
        \Ease\Shared::webPage()->includeJavaScript('js/bootstrap-slider.js');

        $id = $this->getTagID();
        if ($id) {
            $me = '#'.$id;
        } else {
            $me = "input[name='".$this->getTagName()."']";
        }

        $this->addJavaScript(
            '$("'.$me.'").slider();', null, true
        );
    }

}
