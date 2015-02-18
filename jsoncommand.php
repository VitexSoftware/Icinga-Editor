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

$request = $oPage->getRequestValue('term');
$platform = $oPage->getRequestValue('platform');
$Source = $oPage->getRequestValue('source', 'array');
$limit = $oPage->getRequestValue('maxRows', 'int');
if ($limit) {
    $limit = 'LIMIT ' . $limit;
} else {
    $limit = '';
}

$MembersFound = array();

if ($request) {

    if ($platform) {
        $sqlConds = " AND ((`platform` =  '" . $platform . "') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";
    } else {
        $sqlConds = '';
    }

    $query = 'SELECT `command_name` FROM `command` WHERE command_type=\'check\' AND (user_id=' . $oUser->getUserID() . ' OR public=1) AND command_name LIKE \'%' . EaseShared::myDbLink()->AddSlashes($request) . '%\' ' . $sqlConds . ' ORDER BY command_name ' . $limit;

    $MembersFoundArray = EaseShared::myDbLink()->queryToArray($query);
    if (count($MembersFoundArray)) {
        foreach ($MembersFoundArray as $request) {
            $MembersFound[] = $request['command_name'];
        }
    }
}

echo json_encode($MembersFound);
