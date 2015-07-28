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
class IEHostgroupSelect extends EaseHtmlSelect
{

    function loadItems()
    {
        $membersFound = array('' => '---');
        $query = 'SELECT  `hostgroup_id`, `hostgroup_name` FROM `' . 'hostgroup` WHERE (user_id=' . $this->user->getUserID() . ')  ORDER BY  hostgroup_name ';

        $membersFoundArray = EaseShared::myDbLink()->queryToArray($query);
        if (count($membersFoundArray)) {
            foreach ($membersFoundArray as $request) {
                $membersFound[$request['hostgroup_id']] = $request['hostgroup_name'];
            }
        }
        return $membersFound;
    }

    public function finalize()
    {
        parent::finalize();
        EaseShared::webPage()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown();', null, true);
        EaseShared::webPage()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        EaseShared::webPage()->includeCss('css/msdropdown/dd.css');
    }

}
