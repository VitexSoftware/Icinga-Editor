
<?php
namespace Icinga\Editor;

/**
 * Icinga Editor - proxy pro zobrazení Icingy
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 * @deprecated since version 200
 */
require_once 'includes/IEInit.php';

if (isset($_GET['t'])) {
    $target = $_GET['t'];

    $page = file_get_contents('http://' . $oUser->getUserLogin() . ':' . $oUser->getSettingValue('plaintext') . '@' . $_SERVER['HTTP_HOST'] . '' . $target);

    //$page = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.%]*(\?\S+)?)?)?)@', '/cgi-bin/icinga/$1', $page);

    $page = str_replace(array('href="', 'src=\'', 'src="', 'action="'), array('href="/cgi-bin/icinga/', 'src=\'/cgi-bin/icinga/', 'src="/cgi-bin/icinga/', 'action="/cgi-bin/icinga/'), $page);
    $page = str_replace('/cgi-bin/icinga//icinga/', '/icinga/', $page);
    echo $page;
}

