<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEServiceSelect
 *
 * @author vitex
 */
class HostgroupSelect extends \Ease\Html\Select {

    use \Ease\SQL\Orm;
    
    function loadItems() {
        $this->myTable = 'hostgroup';
        
        $membersFound = ['' => '---'];

        $membersFoundArray = $this->listingQuery()->where('user_id', \Ease\Shared::user()->getUserID())->orderBy('hostgroup_name')->fetchAll() ;
        if (count($membersFoundArray)) {
            foreach ($membersFoundArray as $request) {
                $membersFound[$request['hostgroup_id']] = $request['hostgroup_name'];
            }
        }
        return $membersFound;
    }

    public function finalize() {
        parent::finalize();
        \Ease\WebPage::singleton()->addJavaScript('$("#' . $this->getTagID() . '").msDropDown();',
                null, true);
        \Ease\WebPage::singleton()->includeJavaScript('js/msdropdown/jquery.dd.min.js');
        \Ease\WebPage::singleton()->includeCss('css/msdropdown/dd.css');
    }

}
