<?php

namespace Icinga\Editor;

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

$request  = $oPage->getRequestValue('term');
$platform = $oPage->getRequestValue('platform');
$source   = $oPage->getRequestValue('source', 'array');
$limit    = $oPage->getRequestValue('maxRows', 'int');
if ($limit) {
    $limit = 'LIMIT '.$limit;
} else {
    $limit = '';
}

$membersFound = [];

if ($request) {

    if ($platform) {
        $sqlConds = " AND ((`platform` =  '".$platform."') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";
    } else {
        $sqlConds = '';
    }

    $query = 'SELECT `command_name` FROM `command` WHERE command_type=\'check\' AND (user_id='.$oUser->getUserID().' OR public=1) AND command_name LIKE \'%'.\Ease\Shared::db()->AddSlashes($request).'%\' '.$sqlConds.' ORDER BY command_name '.$limit;

    $membersFoundArray = \Ease\Shared::db()->queryToArray($query);
    if (count($membersFoundArray)) {
        foreach ($membersFoundArray as $request) {
            $membersFound[] = $request['command_name'];
        }
    }
}

echo json_encode($membersFound);
