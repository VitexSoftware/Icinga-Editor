<?php

namespace Icinga\Editor\UI;

require_once 'Image/GraphViz.php';

/**
 * Diagram mapy sítě
 *
 * @package    IcingaEditor
 * @subpackage Engine
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
class HostMap extends Image_GraphViz
{

    /**
     * Constructor.
     *
     * Setting the name of the Graph is useful for including multiple image
     * maps on one page. If not set, the graph will be named 'G'.
     *
     * @param boolean $directed    Directed (TRUE) or undirected (FALSE) graph.
     *                             Note: You MUST pass a boolean, and not just
     *                             an  expression that evaluates to TRUE or
     *                             FALSE (i.e. NULL, empty string, 0 will NOT
     *                             work)
     * @param array   $attributes  Attributes of the graph
     * @param string  $name        Name of the Graph
     * @param boolean $strict      Whether to collapse multiple edges between
     *                             same nodes
     * @param boolean $returnError Set to TRUE to return PEAR_Error instances
     *                             on failures instead of FALSE
     *
     * @access public
     */
    public function __construct($directed = false, $attributes = array(),
                                $name = 'G', $strict = true,
                                $returnError = false)
    {
        $attributes['overlap'] = 'prism';
        parent::__construct($directed, $attributes, $name, $strict, $returnError);
        $this->fillUp();
    }

    /**
     * Naplní diagram
     */
    function fillUp()
    {
        $host  = new IEHost();
        $hosts = $host->getListing(null, false,
            array('alias', 'address', 'parents', 'notifications_enabled', 'active_checks_enabled',
            'passive_checks_enabled', $host->myCreateColumn, $host->myLastModifiedColumn));

        foreach ($hosts as $host => $host_info) {
            $name = $host_info['host_name'];
            if (!$name) {
                continue;
            }

            $alias = $host_info['alias'];
            if (!$alias) {
                $alias = $host_info['host_name'];
            }

            $color = '';
            if ($host_info['active_checks_enabled']) {
                $color = 'lightgreen';
            }
            if ($host_info['passive_checks_enabled']) {
                $color = 'lightblue';
            }



            $this->addNode($name,
                array(
                'URL' => 'host.php?host_id='.$host_info['host_id'],
                'fontsize' => '10',
                'color' => $color,
                'height' => '0.2',
                'width' => '2.1',
                'fixedsize' => false,
                'style' => 'filled',
                'tooltip' => $host_info['address'],
                'label' => $alias));


            if (isset($host_info['host_name'])) {
                if (is_array($host_info['parents'])) {
                    foreach ($host_info['parents'] as $parent_name) {
                        $this->addEdge(array($host_info['host_name'] => $parent_name));
                    }
                }
            }
        }
    }

    function draw()
    {
        error_reporting(E_ALL ^ E_STRICT);

        $tmpfile = $this->saveParsedGraph();
        if (!$tmpfile || $this->PEAR->isError($tmpfile)) {
            return $tmpfile;
        }

        $outputfile = $tmpfile.'.'.'svg';

        $rendered = $this->renderDotFile($tmpfile, $outputfile, 'svg', 'twopi');
        if ($rendered !== true) {
            return $rendered;
        }

        $return = true;
        if (readfile($outputfile) === false) {
            $return = false;
        }
        @unlink($outputfile);
        @unlink($tmpfile);

        return $return;
    }

    public function getObjectName()
    {
        return 'svg';
    }
}