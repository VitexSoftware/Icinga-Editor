<?php

/**
 * Icinga Editor - titulní strana
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
require_once 'includes/IEInit.php';
$oPage->onlyForLogged();

$hostgroupID = $oPage->getRequestValue('hostgroup_id', 'int');
$level = $oPage->getRequestValue('level', 'int');
if (!$level) {
    $level = 1;
}

if (is_null($hostgroupID)) {
    $oPage->addStatusMessage(_('Chybné volání mapy skupiny'), 'warning');
    $oPage->redirect('hostgroups.php');
}



$hostgroup = new IEHostgroup($hostgroupID);


if ($oPage->isPosted()) {
    if (isset($_FILES) && count($_FILES)) {
        $tmpfilename = $_FILES['bgimage']['tmp_name'];
    } else {
        if ($oPage->isPosted()) {
            $oPage->addStatusMessage(_('Nebyl vybrán soubor s ikonou hosta'), 'warning');
        }
    }

    if (isset($tmpfilename) || ($_FILES['bgimage']['error'] = 0)) {
        if ($tmpfilename && IEIconSelector::imageTypeOK($tmpfilename)) {
            $newbackground = $hostgroup->saveBackground($tmpfilename, $level);
            if ($newbackground) {
                $hostgroup->saveToMySQL();
            }
        } else {
            if (file_exists($tmpfilename)) {
                unlink($tmpfilename);
            }
            $oPage->addStatusMessage(_('toto není obrázek požadovaného typu'), 'warning');
        }
    }
}

$oPage->addItem(new IEPageTop(_('Mapa skupiny hostů') . ' ' . $hostgroup->getName()));

$oPage->addCss('

#netmap { border: 1px solid gray; margin-left: auto; margin-right: auto;}
.node {
  cursor: move;
  fill: #ccc;
  stroke: #000;
  stroke-width: 1.5px;
}

    .link {
        fill: none;
        stroke: #0f0;
        stroke-width: 3px;
        opacity: 0.7;
        marker-end: url(#end-arrow);
    }

    .label {
//        fill: black;
        font-family: Verdana;
        font-size: 15px;
        text-anchor: middle;
        cursor: pointer;
    }

    .passive {
        fill: blue;
    }

    .active {
        fill: green;
    }

.node.fixed {
  fill: #f00;
}

    ');

$oPage->addCss('
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
    return window.innerWidth - $(this).offset().left - 20;
} );", null, true);


$oPage->addJavaScript("
 $('#leveltabs a').click(function (e) {
//  e.preventDefault()
    $('#netmap').attr( 'class', 'levelbg' + $(this).html() );
//    $(this).tab('show')
})
", null, true);



$dynamicScript = (new EaseHtmlScriptTag('
var d3cola = cola.d3adaptor().convergenceThreshold(0.1);

        var width = 800, height = 600;

        var outer = d3.select("body").append("svg")
//            .attr("width", width)
//            .attr("height", height)
            .attr("id","netmap")
            .attr("class","levelbg' . $level . '")
            .attr("style","background-position: center;background-repeat:no-repeat;")
            .attr("pointer-events", "all");

        outer.append(\'rect\')
            .attr(\'style\', \'fill-opacity:0\')
            .attr(\'width\', "100%")
            .attr(\'height\', "100%")
            .call(d3.behavior.zoom().on("zoom", redraw));


        var vis = outer
            .append(\'g\')
            .attr(\'transform\', \'translate(250,250) scale(0.6)\');

        function redraw() {
            vis.attr("transform", "translate(" + d3.event.translate + ")" + " scale(" + d3.event.scale + ")");
        }

        outer.append(\'svg:defs\').append(\'svg:marker\')
            .attr(\'id\', \'end-arrow\')
            .attr(\'viewBox\', \'0 -5 10 10\')
            .attr(\'refX\', 8)
            .attr(\'markerWidth\', 6)
            .attr(\'markerHeight\', 6)
            .attr(\'orient\', \'auto\')
          .append(\'svg:path\')
//            .attr(\'d\', \'M0,-5L10,0L0,5L2,0\')
            .attr(\'stroke-width\', \'0px\')
            .attr(\'fill\', \'#000\');

        d3.text("mapsource.php?hostgroup_id=' . $hostgroupID . '", function (f) {
            var digraph = graphlibDot.parse(f);

            var nodeNames = digraph.nodes();
            var nodes = new Array(nodeNames.length);
            nodeNames.forEach(function (name, i) {
                var v = nodes[i] = digraph._nodes[nodeNames[i]];
                v.id = i;
                v.name = name;
            });

            var edges = [];
            for (var e in digraph._edges) {
                var edge = digraph._edges[e];
                edges.push({ source: digraph._nodes[edge.u].id, target: digraph._nodes[edge.v].id });
            }

            d3cola
                .avoidOverlaps(true)
                .flowLayout(\'x\', 150)
//              .size([width, height])
                .nodes(nodes)
                .links(edges)
                .symmetricDiffLinkLengths(5)
                .linkDistance(50)
                .jaccardLinkLengths(150);

            var link = vis.selectAll(".link")
                .data(edges)
                .enter().append("path")
                .attr("class", "link");

            var margin = 10, pad = 10;
            var node = vis.selectAll(".node")
                .data(nodes)
                .enter().append("circle")
                .attr("class", "node")
                //.attr("cx", 5).attr("cy", 5)
                .attr("r","10")
                .on("dblclick",  dblclick )
                .on("dragstart", dragstart)
                .call(d3cola.drag);

            var label = vis.selectAll(".label")
                .data(nodes)
                .enter().append("text")
                .attr("class", function (d) {
                    if(d.value.color == "lightblue"){
                        return "label passive";
                    };
                    if(d.value.color == "lightgreen"){
                        return "label active";
                    };
                    return "label";
                    }
                    )
                .text(function (d) { return d.name; })
                .attr("data-url", function (d) { return d.value.URL; } )
                .on("dblclick",  dblclick )
                .on("dragstart", dragstart)
                .call(d3cola.drag)
                .each(function (d) {
                    var b = this.getBBox();
                    var extra = margin + pad;
                    d.width = b.width + extra;
                    d.height = b.height + extra;
                });

            var lineFunction = d3.svg.line()
                .x(function (d) { return d.x; })
                .y(function (d) { return d.y; })
                .interpolate("basis");

            var routeEdges = function () {
                d3cola.prepareEdgeRouting(margin/3);
                link.attr("d", function (d) {
                    return lineFunction(d3cola.routeEdge(d))
                    });
                if (isIE()) link.each(function (d) { this.parentNode.insertBefore(this, this) });
            }

function dblclick(d) {
  d3.select(this).classed("fixed", d.fixed = false);
}
function dragstart(d) {
  d3.select(this).classed("fixed", d.fixed = true);
}

            d3cola.start(10, 30, 100).on("tick", function () {
                node.each(function (d) { d.innerBounds = d.bounds.inflate(-margin);  })
                    .attr("cx", function (d) { return d.innerBounds.x })
                    .attr("cy", function (d) { return d.innerBounds.y })
                    .attr("width", function (d) { return d.innerBounds.width(); })
                    .attr("height", function (d) { return d.innerBounds.height(); });

                link.attr("d", function (d) {
                    cola.vpsc.makeEdgeBetween(d, d.source.innerBounds, d.target.innerBounds, 5);
                    var lineData = [{ x: d.sourceIntersection.x, y: d.sourceIntersection.y }, { x: d.arrowStart.x, y: d.arrowStart.y }];
                    return lineFunction(lineData);
                });
                if (isIE()) link.each(function (d) { this.parentNode.insertBefore(this, this) });

                label
                    .attr("x", function (d) { return d.x })
                    .attr("y", function (d) { return d.y + (margin + pad) / 2 });

            }).on("end", routeEdges);

        });
        function isIE() { return ((navigator.appName == \'Microsoft Internet Explorer\') || ((navigator.appName == \'Netscape\') && (new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})").exec(navigator.userAgent) != null))); }

//                .css("background-color",  function (d) { return d.value.color; } )


    '));


$levelTabs = new EaseTWBTabs('leveltabs', null);


$bgimages = $hostgroup->getDataValue('bgimages');
$levels = array_keys($bgimages);
if (!is_array($levels) || !count($levels)) {
    $levels = array('1' => '0');
}

foreach ($levels as $currentLevel) {
    $levelTab = $levelTabs->addTab($currentLevel, null, ($currentLevel == $level));

    $bgImgUplForm = new EaseTWBForm('bgImgUplForm' . $currentLevel, null, 'POST', null, array('enctype' => 'multipart/form-data', 'class' => 'form-inline'));
    $bgImgUplForm->addInput(new EaseHtmlInputFileTag('bgimage'), _('Obrázek'));
    $bgImgUplForm->addItem(new EaseHtmlInputHiddenTag('level', $currentLevel));
    $bgImgUplForm->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

    $levelTab->addItem(new EaseTWBPanel(sprintf(_('Obrázek pozadí pro úroveň %s'), $currentLevel), 'info', $bgImgUplForm));

    $oPage->addCss('.levelbg' . $currentLevel . ' { background-image: url("' . $bgimages[$currentLevel] . '"); } ');
}

$levelTab = $levelTabs->addTab( ++$currentLevel);
$bgImgUplForm = new EaseTWBForm('bgImgUplForm' . $currentLevel, null, 'POST', null, array('enctype' => 'multipart/form-data', 'class' => 'form-inline'));
$bgImgUplForm->addInput(new EaseHtmlInputFileTag('bgimage'), _('Obrázek'));
$bgImgUplForm->addItem(new EaseHtmlInputHiddenTag('level', $currentLevel));
$bgImgUplForm->addItem(new EaseTWSubmitButton(_('Uložit'), 'success'));

$levelTab->addItem(new EaseTWBPanel(sprintf(_('Obrázek pozadí pro úroveň %s'), $currentLevel), 'info', $bgImgUplForm));


$oPage->container->addItem($dynamicScript);

$oPage->container->addItem($levelTabs);


$oPage->addItem(new IEPageBottom());

$oPage->draw();

