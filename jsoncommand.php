<?php

/**
 * Vrací jSon
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

$oPage->onlyForLogged();

if (!$oUser->GetUserID()) {
    die(_('nejprve se prosím přihlaš'));
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');


$Request = $oPage->getRequestValue('term');
$Source = $oPage->getRequestValue('source','array');
$Limit = $oPage->getRequestValue('maxRows', 'int');
if ($Limit) {
    $Limit = 'LIMIT ' . $Limit;
} else {
    $Limit = '';
}

$MembersFound = array();

if ($Request) {
    $MembersFoundArray = EaseShared::myDbLink()->queryToArray('SELECT `command_name` FROM `'. DB_PREFIX . 'command` WHERE command_type=\'check\' AND (user_id='.$oUser->getUserID().' OR public=1) AND command_name LIKE \'%' . EaseShared::myDbLink()->AddSlashes($Request) . '%\' ORDER BY command_name ' . $Limit);
    if (count($MembersFoundArray)) {
        foreach ($MembersFoundArray as $Request) {
            $MembersFound[] = $Request['command_name'];
        }
    }
}

echo json_encode($MembersFound);


?>
