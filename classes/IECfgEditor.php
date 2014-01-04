<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'IEGroupMembersEditor.php';
require_once 'IEUserSelect.php';

/**
 * Description of IECfgEditor
 *
 * @author vitex
 */
class IECfgEditor extends EaseContainer
{

    /**
     * Právě editovaný objekt
     * @var IECfg Objekt konfigurace
     */
    public $ObjectEdited = null;

    /**
     * Vyžadované položky formuláře
     * @var array
     */
    public $ReqFields = array();

    /**
     * Vytvoří editační formulář podle CFG objektu
     *
     * @param IECfg $this->ObjectEdited
     */
    public function __construct($cfgObject)
    {
        parent::__construct();
        $this->ObjectEdited = &$cfgObject;
        if (EaseShared::user()->getSettingValue('admin')) {
            $this->fullEditor();
        } else {
            $this->lightEditor();
        }
    }

    /**
     * Vloží do stránky widget pro editaci hodnoty
     *
     * @param EaseHtmlDivTag $fieldBlock
     * @param string         $fieldName
     * @param mixed          $value
     */
    public function insertWidget($fieldBlock, $fieldName, $value)
    {
        $keywordInfo = $this->ObjectEdited->keywordsInfo[$fieldName];
        $fieldType = $this->ObjectEdited->useKeywords[$fieldName];
        $Required = (isset($keywordInfo['requeired']) && ($keywordInfo['requeired'] === true));
        $fType = preg_replace('/\(.*\)/', '', $fieldType);

        switch ($fType) {
            case 'INT':
            case 'STRING':
            case 'VARCHAR':
                if ($Required) {
                    $fieldBlock->addItem(new EaseHtmlDivTag(null, new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('class' => 'required', 'title' => $fieldName))));
                } else {
                    $fieldBlock->addItem(new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('title' => $fieldName)));
                }
                break;
            case 'TINYINT':
            case 'BOOL':
                $fieldBlock->addItem(new EaseLabeledCheckbox($fieldName, $value, $keywordInfo['title'], array('title' => $fieldName)));
                break;
            case 'FLAGS':
                $values = array();
                $checkboxes = array();
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));

                foreach ($flags as $flag) {
                    if (isset($keywordInfo[$flag])) {
                        $checkboxes[$flag] = $keywordInfo[$flag];
                    } else {
                        $this->addStatusMessage(_('Chybi definice') . ' ' . $fieldName . ' ' . $flag, 'error');
                    }
                }
                foreach ($checkboxes as $chKey => $chTopic) {
                    $checkboxes[$chKey] = '&nbsp;' . $chTopic . '</br>';
                    if (strchr($value, $chKey)) {
                        $values[$chKey] = true;
                    } else {
                        $values[$chKey] = false;
                    }
                }
                $SliderField = $fieldBlock->addItem(new EaseHtmlFieldSet($keywordInfo['title'], new EaseHtmlCheckboxGroup($fieldName, $checkboxes, $values)));
                $SliderField->setTagCss(array('width' => '100%'));
                break;
            case 'IDLIST':
                if (!is_array($value)) {
                    $values = array();
                }
                $fieldBlock->addItem(new IEGroupMembersEditor($fieldName, $keywordInfo['title'], $this->ObjectEdited, $value));
                break;
            case 'SLIDER':
                $SliderField = $fieldBlock->addItem(new EaseHtmlFieldSet($keywordInfo['title'], new EaseJQuerySlider($fieldName, (int) $value)));
                $SliderField->setTagCss(array('width' => '100%'));
                break;
            case 'TEXT':
                $FB = $fieldBlock->addItem(new EaseLabeledTextarea($fieldName, $value, $keywordInfo['title']));
                $FB->EnclosedElement->setTagCss(array('width' => '100%'));
                break;
            case 'ENUM':
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));
                $Selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                $Selector->addItems(array_combine($flags, $flags));
                $Selector->EnclosedElement->setTagCss(array('width' => '100%'));
                break;
            case 'RADIO':
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));
                if (is_array($flags)) {
                    foreach (array_values($flags) as $flag) {
                        $InfoFlags[$flag] = '&nbsp;' . $keywordInfo[$flag] . '<br>';
                    }
                    $Buttons = new EaseHtmlRadiobuttonGroup($fieldName, $InfoFlags);
                    $Buttons->setValue($value);
                    $FB = $fieldBlock->addItem(new EaseHtmlFieldSet($keywordInfo['title'], $Buttons));
                    $FB->setTagCss(array('width' => '100%'));
                }
                break;
            case 'SELECT':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $STable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $Conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $Conditions = array();
                }

                $SqlConds = " ( " . $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array('public' => 1))) . ")  ";

                $MembersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $nameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $SqlConds . ' ' .
                        'ORDER BY ' . $nameColumn, $IDColumn);

                $Selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                if (!$Required) {
                    $Selector->addItems(array('' => ''));
                }
                if (count($MembersAviableArray)) {
                    $Selector->addItems(array_combine($MembersAviableArray, $MembersAviableArray));
                }
                break;

            case 'SELECT+PARAMS':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $STable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $Conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $Conditions = array();
                }

                $Conditions['command_type'] = 'check';

                $SqlConds = " ( " . $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array('command_local' => true, $this->ObjectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->ObjectEdited->myDbLink->prepSelect($Conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

                $MembersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $nameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $SqlConds . ' ' .
                        'ORDER BY ' . $nameColumn, $IDColumn);

                $Selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                if (!$Required) {
                    $Selector->addItems(array('' => ''));
                }
                if (count($MembersAviableArray)) {
                    $Selector->addItems(array_combine($MembersAviableArray, $MembersAviableArray));
                }

                $SqlConds = " ( " . $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array('command_remote' => true, $this->ObjectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->ObjectEdited->myDbLink->prepSelect($Conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

                $MembersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $nameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $SqlConds . ' ' .
                        'ORDER BY ' . $nameColumn, $IDColumn);

                $AddNewItem = $fieldBlock->addItem(new EaseHtmlInputSearchTag($fieldName . '-remote', $this->ObjectEdited->getDataValue($fieldName . '-remote'), array('class' => 'search-input', 'title' => _('vzdálený test'))));
                $AddNewItem->setDataSource('jsoncommand.php?maxRows=10');

                $fieldBlock->addItem(new EaseLabeledTextInput($fieldName . '-params', $this->ObjectEdited->getDataValue($fieldName . '-params'), _('Parametry'), array('style' => 'width: 100%')));

                break;
            case 'USER':
                $fieldBlock->addItem(new IEUserSelect($fieldName, null, $this->ObjectEdited->getDataValue($fieldName), null, array('style' => 'width: 100%')));
                break;
            default:
                $fieldBlock->addItem(new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('title' => $fieldName)));
                $this->addStatusMessage(sprintf(_('Neznámý typ %s pro sloupec %s'), $fType, $fieldName), 'warning');
                break;
        }
    }

    public function fullEditor()
    {
        if (EaseShared::user()->getSettingValue('admin')) {
            $this->ObjectEdited->keywordsInfo[$this->ObjectEdited->userColumn] = array('title' => 'vlastník');
            $this->ObjectEdited->useKeywords[$this->ObjectEdited->userColumn] = 'USER';
        }

        if ($this->ObjectEdited->AllowTemplating) {
            if (!(int) $this->ObjectEdited->getDataValue('register')) {
                $this->addStatusMessage('toto je pouze předloha');
                foreach ($this->ObjectEdited->keywordsInfo as $Kw => $Props) {
                    unset($this->ObjectEdited->keywordsInfo[$Kw]['required']);
                }
                $this->ObjectEdited->keywordsInfo['name']['required'] = true;
                $this->ObjectEdited->keywordsInfo['register']['required'] = true;
            } else {
                $this->ObjectEdited->keywordsInfo['name']['required'] = false;
                $this->ObjectEdited->keywordsInfo['register']['required'] = false;
            }
        }
        if (!(int) $this->ObjectEdited->getDataValue('generate')) {
            $this->addStatusMessage(_('tento záznam se nebude generovat do konfigurace'));
        }
        if ($this->ObjectEdited->publicRecords) {
            if ((int) $this->ObjectEdited->getDataValue('public')) {
                $this->addStatusMessage(_('tento záznam je veřejný'));
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $Use = $this->ObjectEdited->getDataValue('use');
        if (!is_null($Use)) {
            $template = clone $this->ObjectEdited;
            $template->loadFromMySQL((int) $Use);
        }

        foreach ($this->ObjectEdited->useKeywords as $fieldName => $FieldType) {

            $KeywordInfo = $this->ObjectEdited->keywordsInfo[$fieldName];

            if (!count($KeywordInfo)) {
                continue;
            }

            if (isset($KeywordInfo['hidden'])) {
                continue;
            }

            if (!isset($KeywordInfo)) {
                $this->addStatusMessage(_('Info Chybí') . '   ' . $FieldType . ' ' . $fieldName, 'warning');
                continue;
            }

            if (!isset($KeywordInfo['title'])) {
                $this->addStatusMessage(_('sloupec bez popisku') . ' ' . $fieldName, 'warning');
            }

            if (!strlen($KeywordInfo['title'])) {
                continue;
            }

            if (isset($KeywordInfo['required']) && $KeywordInfo['required']) {
                $this->ReqFields[$fieldName] = $FieldType;
                $required = true;
            } else {
                $required = false;
            }

            $value = $this->ObjectEdited->getDataValue($fieldName);
            if ($value == 'NULL') {
                $value = null;
            }

            if ($this->ObjectEdited->AllowTemplating) {
                if ($this->ObjectEdited->isTemplate()) {
                    if (EaseShared::webPage()->isPosted() && is_null($value) && $required) {
                        $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $KeywordInfo['title'], 'warning');
                    }
                }
            } else {
                if (EaseShared::webPage()->isPosted() && is_null($value) && $required) {
                    $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $KeywordInfo['title'], 'warning');
                }
            }

            $mainFieldBlock = $this->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));

            $fieldLabel = $mainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $fieldName . '">' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));

            if (!$required || !(int) $this->ObjectEdited->getDataValue('register')) {
                $fieldLabel->addItem(new EaseHtmlATag('#', EaseTWBPart::GlyphIcon('remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
                $fieldLabel->setTagClass('FieldLabel');
            } else {
                $mainFieldBlock->setTagClass('fieldblock req');
            }

            $fieldBlock = $mainFieldBlock->addItem(new EaseHtmlDivTag($fieldName . '-controls'));

            if (!$this->ObjectEdited->isOwnedBy() && !EaseShared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
                if ($this->ObjectEdited->getId()) {
                    if (substr($value, 0, 2) == 'a:') {
                        $value = unserialize($value);
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                    }
                    $fieldBlock->addItem($value);
                    continue;
                }
            }

            if (isset($template)) {
                $TempValue = $template->getDataValue($fieldName);
                if (!is_null($TempValue) && ($fieldName != $this->ObjectEdited->nameColumn) && !$required) { //Skrýt nedůležité položky
                    EaseShared::webPage()->addJavaScript("$('#" . $fieldName . "-controls').hide();", null, true);
                }
            }

            $this->insertWidget($fieldBlock, $fieldName, $value);
        }
    }

    /**
     * jQuery pro vyžadované políčka
     */
    public function finalize()
    {
        EaseShared::webPage()->includeJavaScript('js/jquery.validate.js');
        if (isset($this->ReqFields) && count($this->ReqFields)) {

            $Rules = ' $("#' . $this->parentObject->getTagProperty('name') . '").validate({
        invalidHandler: function (e, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                    ? \'' . _('Jedno povinné políčko zůstalo nevyplněné') . '\'
                    : \'' . _('Není vyplněno \' + errors + \' políček. Tyto byly označeny') . '\';
                $("div.error span").html(message);
                $("div.error").show();
            } else {
                $("div.error").hide();
            }
        },
  rules: {';
            foreach ($this->ReqFields as $FieldName => $FieldType) {
                $FRules[] = "\n\"$FieldName\": \"required\"";
            }
            $Rules.= implode(',', $FRules) . "\n}});\n";

            //$Rules = ' $("#' . $this->parentObject->getTagProperty('name') . '").validate();';

            EaseShared::webPage()->addJavaScript($Rules, NULL, true);
        }
    }

    public function lightEditor()
    {

        if (!(int) $this->ObjectEdited->getDataValue('generate')) {
            $this->addStatusMessage('tento záznam se nebude generovat do konfigurace');
        }
        if ($this->ObjectEdited->publicRecords) {
            if ((int) $this->ObjectEdited->getDataValue('public')) {
                $this->addStatusMessage('tento záznam je veřejný');
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $use = $this->ObjectEdited->getDataValue('use');
        if (!is_null($use)) {
            $template = clone $this->ObjectEdited;
            $template->loadFromMySQL((int) $use);
        }

        foreach ($this->ObjectEdited->useKeywords as $fieldName => $FieldType) {

            $keywordInfo = $this->ObjectEdited->keywordsInfo[$fieldName];

            if (!count($keywordInfo)) {
                continue;
            }

            if (isset($keywordInfo['hidden'])) {
                continue;
            }

            if (!strlen($keywordInfo['title'])) {
                continue;
            }

            if (isset($keywordInfo['required']) && $keywordInfo['required']) {
                $this->ReqFields[$fieldName] = $FieldType;
                $Required = true;
            } else {
                if (!isset($keywordInfo['mandatory']) || !$keywordInfo['mandatory']) {
                    $Required = false;
                    continue;
                }
            }

            $value = $this->ObjectEdited->getDataValue($fieldName);
            if ($value == 'NULL') {
                $value = null;
            }

            if ($this->ObjectEdited->AllowTemplating) {
                if ($this->ObjectEdited->isTemplate()) {
                    if (EaseShared::webPage()->isPosted() && is_null($value) && $Required) {
                        $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $keywordInfo['title'], 'warning');
                    }
                }
            } else {
                if (EaseShared::webPage()->isPosted() && is_null($value) && $Required) {
                    $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $keywordInfo['title'], 'warning');
                }
            }

            $mainFieldBlock = $this->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));

            $fieldLabel = $mainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $fieldName . '">' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));

            if (!$Required || !(int) $this->ObjectEdited->getDataValue('register')) {
                $fieldLabel->addItem(new EaseHtmlATag('#', EaseTWBPart::GlyphIcon('icon-remove">'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
                $fieldLabel->setTagClass('FieldLabel');
            } else {
                $mainFieldBlock->setTagClass('fieldblock req');
            }

            $fieldBlock = $mainFieldBlock->addItem(new EaseHtmlDivTag($fieldName . '-controls'));

            if (!$this->ObjectEdited->isOwnedBy() && !EaseShared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
                if ($this->ObjectEdited->getId()) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $fieldBlock->addItem($value);
                    continue;
                }
            }

            if (isset($template)) {
                $tempValue = $template->getDataValue($fiedlName);
                if (!is_null($tempValue) && ($fieldName != $this->ObjectEdited->nameColumn) && !$Required) { //Skrýt nedůležité položky
                    EaseShared::webPage()->addJavaScript("$('#" . $fieldName . "-controls').hide();", null, true);
                }
            }
            $this->insertWidget($fieldBlock, $fieldName, $value);
        }
    }

}
