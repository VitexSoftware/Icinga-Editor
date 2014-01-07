<?php

/**
 * Volba služeb patřičných k hostu
 * 
 * @todo dodělat
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
class IEUserSelect extends EaseHtmlSelect
{
    function __construct($name, $items = null, $DefaultValue = null, $ItemsIDs = false, $Properties = null)
    {
        if(is_null($items)){
            $items = $this->loadItems();
            foreach ($items as $ItemID => $Item){
                if($ItemID == $DefaultValue){
                    $DefaultValue = $Item;
                }
            }
            $this->addItems($items);
        }
        parent::__construct($name, $items, $DefaultValue, $ItemsIDs, $Properties);
    }
    function loadItems()
    {   
        $User = new EaseUser();
        $UI = array();
        foreach ($User->getAllFromMySQL('user',array('id','login'),null,'login','id') as $UserInfo){
            $UI[$UserInfo['id']] = $UserInfo['login'];
        }
        return $UI;
    }

}

?>
