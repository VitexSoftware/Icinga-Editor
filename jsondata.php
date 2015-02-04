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
$Source = $oPage->getRequestValue('source','array');
$limit = $oPage->getRequestValue('maxRows', 'int');
if ($limit) {
    $limit = 'LIMIT ' . $limit;
} else {
    $limit = '';
}

$MembersFound = array();

if ($request) {
    $MembersFoundArray = EaseShared::myDbLink()->queryToArray('SELECT '.current($Source).' FROM `'. DB_PREFIX . key($Source).'` WHERE user_id='.$oUser->getUserID().' AND '.current($Source).' LIKE \'%' . EaseShared::myDbLink()->AddSlashes($request) . '%\' ORDER BY contact_name ' . $limit);
    if (count($MembersFoundArray)) {
        foreach ($MembersFoundArray as $request) {
            $MembersFound[] = $request[current($Source)];
        }
    }
}

echo json_encode($MembersFound);
