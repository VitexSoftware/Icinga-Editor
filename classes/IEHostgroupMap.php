<?php

/**
 * Description of IEHostgroupMap
 *
 * @author vitex
 */
class IEHostgroupMap extends IEHostMap
{

    public $hostgroup = null;

    public function __construct($hostgroup_id, $directed = false, $attributes = array(), $name = 'G', $strict = true, $returnError = false)
    {
        $this->hostgroup = new IEHostgroup($hostgroup_id);

        $attributes['rankdir'] = 'LR';
//        $attributes['fontsize'] = '8';
        parent::__construct($directed, $attributes, $name, $strict, $returnError);
    }

    /**
     * Naplní diagram
     */
    function fillUp()
    {
        $members = $this->hostgroup->getDataValue('members');
        $host = new IEHost();
        $hosts = $host->getColumnsFromMySQL(
            array('alias', 'address', 'parents', 'notifications_enabled', 'active_checks_enabled', 'passive_checks_enabled', $host->myCreateColumn, $host->myLastModifiedColumn, $host->nameColumn, $host->myKeyColumn), 'host_id IN ( ' . implode(',', array_keys($members)) . ' )'
        );


        foreach ($hosts as $hostNo => $host_info) {
            if (strlen(trim($host_info['parents']))) {
                $host_info['parents'] = unserialize($host_info['parents']);
            }
            if (isset($host_info[$host->nameColumn])) {
                $name = $host_info[$host->nameColumn];
            } else {
                continue;
            }
            $alias = $host_info['alias'];
            $color = '';
            if ($host_info['active_checks_enabled']) {
                $color = 'lightgreen';
            }
            if ($host_info['passive_checks_enabled']) {
                $color = 'lightblue';
            }


            $this->addNode($name, array(
//              'URL' => 'services.php?' . $host_info['host_id'],
              'id' => 'host_' . $host_info[$host->myKeyColumn],
//              'fontsize' => '10',
//              'fillcolor' => $color,
              'color' => $color,
//              'height' => '0.2',
//              'width' => '2.1',
//              'fixedsize' => false,
              'shape' => 'point',
              'style' => 'filled',
              'tooltip' => $name,
              'label' => $name)
            );

            if (isset($host_info[$host->nameColumn])) {
                if (is_array($host_info['parents'])) {
                    foreach ($host_info['parents'] as $parent_name) {
                        $this->addEdge(array($host_info[$host->nameColumn] => $parent_name));
                    }
                }
            }
        }
    }

}
