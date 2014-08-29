<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEFXPreloader
 *
 * @author vitex
 */
class IEFXPreloader extends EaseHtmlDivTag
{

    public function __construct($name = null)
    {
        $properties['class'] = 'fuelux preloader';
        parent::__construct($name, '<i></i><i></i><i></i><i></i>', $properties);
    }

    public function finalize()
    {
        EaseShared::webPage()->includeCss('twitter-bootstrap/css/fuelux.css', true);
        EaseShared::webPage()->includeCss('twitter-bootstrap/css/fuelux-responsive.css', true);
    }

}
