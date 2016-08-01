<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEServiceSelect
 *
 * @author vitex
 */
class HostSelect extends \Ease\Html\Select
{
    public $hosts     = ['' => ['image' => 'logos/icinga.gif']];
    public $platforms = [
        'generic' => ['image' => 'logos/unknown.gif'],
        'windows' => ['image' => 'logos/base/win40.gif'],
        'linux' => ['image' => 'logos/base/linux40.gif'],
    ];

    function loadItems()
    {
        $membersFound = ['' => '---'];
        $query        = 'SELECT  `host_id`, `icon_image`,`platform`,`host_name` FROM `'.'host` WHERE (user_id='.\Ease\Shared::user()->getUserID().' OR public=1) AND register=1 ORDER BY  host_name ';

        $membersFoundArray = \Ease\Shared::db()->queryToArray($query);
        if (count($membersFoundArray)) {
            foreach ($membersFoundArray as $request) {
                if (isset($request['icon_image'])) {
                    $icon = $request['icon_image'];
                } else {
                    if (isset($request['platform']) && isset($this->platforms[$request['platform']]['image'])) {
                        $icon = $this->platforms[$request['platform']]['image'];
                    } else {
                        $icon = 'logos/unknown.gif';
                    }
                }
                $this->hosts[$request['host_id']]  = ['image' => $icon];
                $membersFound[$request['host_id']] = $request['host_name'];
            }
        }
        return $membersFound;
    }

    public function finalize()
    {
        parent::finalize();
        $this->setTagID();
        reset($this->hosts);
        foreach ($this->pageParts as $optionName => $option) {
            $platform = current($this->hosts);
            if (isset($platform['image'])) {
                $this->pageParts[$optionName]->setTagProperties(['data-image' => $platform['image']]);
            }
            next($this->hosts);
        }
        \Ease\Shared::webPage()->addJavaScript('$("#'.$this->getTagID().'").msDropDown();',
            null, true);
        \Ease\Shared::webPage()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        \Ease\Shared::webPage()->includeCss('css/msdropdown/dd.css');
    }

}
