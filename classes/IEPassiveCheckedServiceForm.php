<?php

/**
 * Formulář průvodce založením nové služby
 *
 * @package    IcingaEditor
 * @subpackage WebUI
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2015 Vitex@hippy.cz (G)
 */
require_once 'classes/IEPlatformSelector.php';
require_once 'Ease/EaseTWBootstrap.php';

/**
 * Description of NewPassiveCheckedHostForm
 *
 * @author vitex
 */
class IEPassiveCheckedServiceForm extends EaseTWBForm
{

    /**
     * Objekt služby
     * @var IEService
     */
    public $service = null;

    /**
     * Formulář založení pasivní služby
     *
     * @param IEService $service
     */
    function __construct($service)
    {
        parent::__construct('passive-service');
        $this->service = $service;
    }

    function finalize()
    {
        parent::finalize();

        $this->addItem(new EaseTWBFormGroup(_('Jméno'), new EaseHtmlInputTextTag('service_name'), $this->service->getName(), _('Volné místo disku'), _('Název služby testu')));
        $this->addItem(new EaseTWBFormGroup(_('Platforma'), new IEPlatformSelector('platform'), $this->service->getDataValue('platform'), _('Platforma sledovaného stroje')));

        $this->addItem(new EaseTWSubmitButton(_('Založit') . '&nbsp' . EaseTWBPart::GlyphIcon('forward'), 'success'));
    }

    function commandSelector()
    {
        $dblink = EaseShared::db();
        $IDColumn = 'command_id';
        $nameColumn = 'command_name';
        $sTable = DB_PREFIX . 'command';

        $conditions['command_type'] = 'check';

        $sqlConds = " ( " . $dblink->prepSelect(array_merge($conditions, array('command_local' => true, 'user_id' => EaseShared::user()->getUserID()))) . " ) OR ( " . $dblink->prepSelect($conditions) . " AND public=1 )  ";


        $platform = 'generic';
        $sqlConds .= " AND ((`platform` =  '" . $platform . "') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";

        $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
            'SELECT ' . $nameColumn . ' ' .
            'FROM `' . DB_PREFIX . $sTable . '` ' .
            'WHERE ' . $sqlConds . ' ' .
            'ORDER BY ' . $nameColumn, $IDColumn);

        $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
        $selector->enclosedElement->setTagClass('form-control');
        if (!$required) {
            $selector->addItems(array('' => ''));
        }
        if (count($membersAviableArray)) {
            $selector->addItems(array_combine($membersAviableArray, $membersAviableArray));
        }

        $sqlConds = " ( " . $dblink->prepSelect(array_merge($conditions, array('command_remote' => true, $this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $dblink->prepSelect($conditions) . " AND public=1 )  ";
//                    $SqlConds = $dblink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

        $membersAviableArray = $dblink->queryTo2DArray(
            'SELECT ' . $nameColumn . ' ' .
            'FROM `' . DB_PREFIX . $sTable . '` ' .
            'WHERE ' . $sqlConds . ' ' .
            'ORDER BY ' . $nameColumn, $IDColumn);

        $addNewItem = $fieldBlock->addItem(new EaseHtmlInputSearchTag($fieldName . '-remote', $this->objectEdited->getDataValue($fieldName . '-remote'), array('class' => 'search-input', 'title' => _('vzdálený test'))));
        $addNewItem->setDataSource('jsoncommand.php?maxRows=10');

        $fieldBlock->addItem(new EaseLabeledTextInput($fieldName . '-params', $this->objectEdited->getDataValue($fieldName . '-params'), _('Parametry'), array('style' => 'width: 100%')));
    }

}
