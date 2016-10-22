<?php

namespace Icinga\Editor;

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2016 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';


$oPage->onlyForLogged();
$oPage->addItem(new UI\PageTop(_('Network map')));

$oPage->addCss('

#netmap { border: 1px solid gray; margin-left: auto; margin-right: auto;}

    .background {
        stroke: white;
        stroke-width: 1px;
        fill: white;
    }

    .node {
        stroke: black;
        stroke-width: 1.5px;
    }

    .link {
        fill: none;
        stroke: #000;
        stroke-width: 3px;
        opacity: 0.7;
        marker-end: url(#end-arrow);
    }

    .label {
        font-family: Verdana;
        font-size: 25px;
        text-anchor: middle;
        cursor: pointer;
    }

    .passive {
        fill: blue;
    }

    .active {
        fill: green;
    }

    ');

$oPage->includeJavascript('js/graphlib-dot.min.js');
$oPage->includeJavascript('js/d3.v3.js');
$oPage->includeJavascript('js/cola.v3.min.js');
$oPage->includeJavascript('js/descent.js');
$oPage->includeJavascript('js/adaptor.js');
$oPage->includeJavascript('js/rectangle.js');

$oPage->addJavascript("$('#netmap').height(function(index, height) {
    return window.innerHeight - $(this).offset().top - 25;
}).width( function(index, width) {
    return window.innerWidth - $(this).offset().left - 50;
} );", null, true);

$dynamicScript = (new \Ease\Html\ScriptTag(null, ['src' => 'js/netmap.js']));

$oPage->container->addItem($dynamicScript);

$oPage->addItem(new UI\PageBottom());

$oPage->draw();

