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
$oPage->addItem(new IEPageTop(_('Mapa sítě')));

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
        fill: white;
    }

    .link {
        fill: none;
        stroke: #000;
        stroke-width: 3px;
        opacity: 0.7;
        marker-end: url(#end-arrow);
    }

    .label {
        fill: black;
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

$oPage->container->addItem(new EaseHtmlScriptTag('
var d3cola = cola.d3adaptor().convergenceThreshold(0.1);

        var width = 800, height = 600;

        var outer = d3.select("body").append("svg")
//            .attr("width", width)
//            .attr("height", height)
            .attr("id","netmap")
            .attr("pointer-events", "all");

        outer.append(\'rect\')
            .attr(\'class\', \'background\')
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
            .attr(\'d\', \'M0,-5L10,0L0,5L2,0\')
            .attr(\'stroke-width\', \'0px\')
            .attr(\'fill\', \'#000\');

        d3.text("mapsource.php", function (f) {
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
//                .size([width, height])
                .nodes(nodes)
                .links(edges)
                .jaccardLinkLengths(150);

            var distMatrix = floydWarshall(nodes, links, function(d) { return d.name });

            var link = vis.selectAll(".link")
                .data(edges)
                .enter().append("path")
                .attr("class", "link");

            var margin = 10, pad = 12;
            var node = vis.selectAll(".node")
                .data(nodes)
                .enter().append("rect")
                .attr("class", "node")
                .attr("rx", 5).attr("ry", 5)
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
                .on("dblclick", function(e) { window.location.href=e.value.URL; }  )
                .call(d3cola.drag)
                .each(function (d) {
                    var b = this.getBBox();
                    var extra = 2 * margin + 2 * pad;
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
            d3cola.start(10, 30, 100).on("tick", function () {
                node.each(function (d) { d.innerBounds = d.bounds.inflate(-margin); })
                    .attr("x", function (d) { return d.innerBounds.x; })
                    .attr("y", function (d) { return d.innerBounds.y; })
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



$oPage->addItem(new IEPageBottom());

$oPage->draw();

