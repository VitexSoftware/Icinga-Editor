<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IEServiceSelect
 *
 * @author vitex
 */
class IEServiceSelect extends EaseHtmlSelect
{

    public $services = array('' => array('image' => 'logos/icinga.gif'));
    public $platforms = array(
      'generic' => array('image' => 'logos/unknown.gif'),
      'windows' => array('image' => 'logos/base/win40.gif'),
      'linux' => array('image' => 'logos/base/linux40.gif'),
    );

    function loadItems()
    {
        $MembersFound = array('' => '---');
        $query = 'SELECT  `service_id`, `icon_image`,`platform`,`service_description` FROM `' . 'service` WHERE (user_id=' . $this->user->getUserID() . ' OR public=1) AND register=1 ORDER BY  service_description ';

        $MembersFoundArray = EaseShared::myDbLink()->queryToArray($query);
        if (count($MembersFoundArray)) {
            foreach ($MembersFoundArray as $request) {
                $MembersFound[$request['service_id']] = $request['service_description'];
                if (isset($request['icon_image'])) {
                    $icon = $request['icon_image'];
                } else {
                    if (isset($request['platform'])) {
                        $icon = $this->platforms[$request['platform']]['image'];
                    } else {
                        $icon = 'logos/unknown.gif';
                    }
                }
                $this->services[$request['service_id']] = array('image' => $icon);
            }
        }
        return $MembersFound;
    }

    public function finalize()
    {
        parent::finalize();
        reset($this->services);
        foreach ($this->pageParts as $optionName => $option) {
            $platform = current($this->services);
            if (isset($platform['image'])) {
                $this->pageParts[$optionName]->setTagProperties(array('data-image' => $platform['image']));
            }
            next($this->services);
        }
        EaseShared::webPage()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown();', null, true);
        EaseShared::webPage()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        EaseShared::webPage()->includeCss('css/msdropdown/dd.css');
    }

}
