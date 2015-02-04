<?php

//TODO
include 'service.php';

exit();

$dblink = EaseShared::db();
$IDColumn = 'command_id';
$nameColumn = 'command_name';
$sTable = 'command';

$conditions['command_type'] = 'check';

$sqlConds = " ( " . $dblink->prepSelect(array_merge($conditions, array('command_remote' => true, 'user_id' => EaseShared::user()->getUserID()))) . " ) OR ( " . $dblink->prepSelect($conditions) . " AND public=1 )  ";

$platform = $this->service->getDataValue('platform');
$sqlConds .= " AND ((`platform` =  '" . $platform . "') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";

$membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
    'SELECT ' . $nameColumn . ' ' .
    'FROM `' . DB_PREFIX . $sTable . '` ' .
    'WHERE ' . $sqlConds . ' ' .
    'ORDER BY ' . $nameColumn, $IDColumn);


$selector = new EaseHtmlSelect('command', null, $this->service->getDataValue('service'));

$this->addItem(new EaseTWBFormGroup(_('Lokální příkaz'), $selector, $this->service->getDataValue('command'), _('Příkaz který služba provádí')));


if (count($membersAviableArray)) {
    $selector->addItems(array_combine($membersAviableArray, $membersAviableArray));
}

$sqlConds = " ( " . $dblink->prepSelect(array_merge($conditions, array('command_remote' => true, $this->service->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $dblink->prepSelect($conditions) . " AND public=1 )  ";
//                    $SqlConds = $dblink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));
