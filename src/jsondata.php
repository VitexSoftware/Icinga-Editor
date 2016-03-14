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

$request = $oPage->getRequestValue('term');
$source = $oPage->getRequestValue('source', 'array');
$limit = $oPage->getRequestValue('maxRows', 'int');
if ($limit) {
    $limit = 'LIMIT ' . $limit;
} else {
    $limit = '';
}

$membersFound = array();

if ($request) {
    $membersFoundArray = \Ease\Shared::myDbLink()->queryToArray('SELECT ' . current($source) . ' FROM `' . key($source) . '` WHERE user_id=' . $oUser->getUserID() . ' AND ' . current($source) . ' LIKE \'%' . \Ease\Shared::myDbLink()->AddSlashes($request) . '%\' ORDER BY contact_name ' . $limit);
    if (count($membersFoundArray)) {
        foreach ($membersFoundArray as $request) {
            $membersFound[] = $request[current($source)];
        }
    }
}

echo json_encode($membersFound);
