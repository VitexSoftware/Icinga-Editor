<?php

/**
 * Ano/Ne switch
 *
 * @package    ABDotazník
 * @subpackage WebUI
 * @author     Vitex <dvorak@austro-bohemia.cz>
 * @copyright  2015 Austro-Bohemia s.r.o.
 */
require_once 'classes/IETWBSwitch.php';

/**
 * Description of ABYesNoSwitch
 *
 * @author vitex
 */
class IEYesNoSwitch extends IETWBSwitch
{

    function __construct($name, $checked = false, $value = null, $properties = null)
    {
        parent::__construct($name, $checked, 'on', $properties);
        $this->setProperties(array('onText' => _('ANO'), 'offText' => _('NE')));
    }

    function finalize()
    {
        parent::finalize();
        $this->addJavascript('$("[name=\'' . $this->getTagName() . '\']").on(\'switchChange.bootstrapSwitch\', function(event, state) {

        var saverClass = $("[name=\'class\']").val();
        var keyId = $(".keyId").val();
        var columnName = $(this).attr("name");

var jqxhr = $.post( "datasaver.php?SaverClass=" + saverClass , { Field: columnName, Value: state, Key: keyId }  ,   function() {
    console.log( "success" );
})
.done(function() {
    console.log( "second success" );
})
.fail(function() {
    console.log( "error" );
});

});
            ', null, true);
    }

}
