function getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null
}

var hostgroup_id = getURLParameter('hostgroup_id');
var level = getURLParameter('level');
if (!level) {
    level = 1;
}

var margin = 20, pad = 20;

var width = 960,
        height = 500;

var force = d3.layout.force()
        .size([width, height])
        .charge(-400) //Odpudivost nodů
        .linkDistance(60) //Délka liky
        .on("tick", tick);

var drag = force.drag()
        .on("dragstart", dragstart)
        .on("dragend", dragend);


var svg = d3.select("body").append("svg")
        .attr("width", width)
        .attr("id", "netmap")
        .attr("height", height)
        .attr("class", "levelbg" + level)
        .attr("style", "background-position: center;background-repeat:no-repeat; background-size:100%;")


var link = svg.selectAll(".link"),
        node = svg.selectAll(".node"),
        label = svg.selectAll(".label");

d3.json("mapsource.php?format=json&hostgroup_id=" + hostgroup_id, function (error, graph) {
    force
            .nodes(graph.nodes)
            .links(graph.links)
            .start();

    link = link.data(graph.links)
            .enter().append("line")
            .attr("class", "link");

    node = node.data(graph.nodes)
            .enter().append("circle")
            .attr("class", "node")
            .attr("r", 5)
            .on("mouseover", mouseover)
            .on("click", click)
            .on("dblclick", dblclick)
            .call(drag);

//    label = label.data(graph.nodes)
//            .enter()
//            .append("text")
//            .attr("class", 'label')
//            .text(function (d) {
//                return d.label;
//            });

});

function tick() {
    link.attr("x1", function (d) {
        return d.source.x;
    })
            .attr("y1", function (d) {
                return d.source.y;
            })
            .attr("x2", function (d) {
                return d.target.x;
            })
            .attr("y2", function (d) {
                return d.target.y;
            });

    node.attr("cx", function (d) {
        return d.x;
    })
            .attr("cy", function (d) {
                return d.y;
            }).
            attr("data-level", function (d) {
                return d.z;
            }).
            attr("data-id", function (d) {
                return d.node_id;
            }).
            attr("class", function (d) {
                if (d.fixed) {
                    return 'node fixed';
                } else {
                    return 'node';
                }
            });

//    label
//            .attr("x", function (d) {
//                return d.x;
//            })
//            .attr("y", function (d) {
//                return d.y + (margin + pad) / 2
//            });


}

function dblclick(d) {
    d3.select(this).classed("fixed", d.fixed = false);
    $.post("nodeproperties.php", {host_id: d.node_id, x: 0, y: 0, "hostgroup_id": hostgroup_id});
    tick();
}

function click(d) {
}

function dragstart(d) {
    d3.select(this).classed("fixed", d.fixed = true);
}

function dragend(d) {
    $.post("nodeproperties.php", {host_id: d.id, x: d.x, y: d.y});
}

function mouseover(d) {
    var nodepos = $("[data-id='" + d.node_id + "']").position();
    $('#nodeinfo').html( '' );
    $('#nodeinfo').load("nodeproperties.php?hostgroup_id=" + hostgroup_id + "&host_id=" + d.id).css({
        "margin-left":"10px",
        "margin-top":"10px",
        "padding": "3px",
        "border-radius": "10px",
        "background":"lightgray",
        "border":"1px solid #112244",
        "position":"absolute",
        "z-index":"10",
        "top": nodepos.top,
        "left": nodepos.left
    }).show();
}


function switchNodeLevel( input ){
    var level = $(input).attr('data-level');
    var host_id = $(input).attr('data-host_id');
    $.post("nodeproperties.php", {host_id: host_id, z: level });
}

function showLevelNodes( level ) {
    $(".node" ).css('visibility','hidden');
    $("[data-level='" + level +"']" ).css('visibility','visible');
    $("#netmap").attr("class", "levelbg" + level);
}