<?php
/**
 * Ano/Ne switch
 *
 * @package   Dotazník
 * @subpackage WebUI
 * @author     Vitex <dvorak@austro-bohemia.cz>
 * @copyright  2015 Austro-Bohemia s.r.o.
 */

namespace Icinga\Editor\UI;

/**
 * Description of ABYesNoSwitch
 *
 * @author vitex
 */
class YesNoSwitch extends TWBSwitch
{
    public $keyCode = 'var key = $(".keyId").val();';

    function __construct($name, $checked = false, $value = null,
                         $properties = null)
    {
        parent::__construct($name, $checked, 'on', $properties);
    }

    function finalize()
    {
        parent::finalize();
        $this->addJavascript('$("[name=\''.$this->getTagName().'\']").on(\'switchChange.bootstrapSwitch\', function(event, state) {

        var saverClass = $("[name=\'class\']").val();
        '.$this->keyCode.'

        if(key) {
            var field = $(this).attr("name");
            var input = $("[name=\''.$this->getTagName().'\']");

            $.post(\'datasaver.php\', {
                SaverClass: saverClass,
                Field: field,
                Value: state,
                Key: key,
                success: function () {
                    input.parent().parent().css({borderColor: "#0f0", borderStyle: "solid"}).animate({borderWidth: \'5px\'}, \'slow\', \'linear\');
                    input.parent().parent().animate({borderColor: \'gray\', borderWidth: \'1px\'});
                }
            }
            ).fail(function () {
                    input.parent().parent().css({borderColor: "#f00", borderStyle: "solid"}).animate({borderWidth: \'5px\'}, \'slow\', \'linear\');
                    input.parent().parent().animate({borderColor: \'gray\', borderWidth: \'1px\'});
            });
        }

        });
            ', null, true);
    }
}
