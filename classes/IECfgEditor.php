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
    public $objectEdited = null;

    /**
     * Vyžadované položky formuláře
     * @var array
     */
    public $reqFields = array();

    /**
     * Vytvoří editační formulář podle CFG objektu
     *
     * @param IECfg $this->ObjectEdited
     */
    public function __construct($cfgObject)
    {
        parent::__construct();
        $this->objectEdited = &$cfgObject;
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
        $keywordInfo = $this->objectEdited->keywordsInfo[$fieldName];
        $fieldType = $this->objectEdited->useKeywords[$fieldName];
        $required = (isset($keywordInfo['requeired']) && ($keywordInfo['requeired'] === true));
        $fType = preg_replace('/\(.*\)/', '', $fieldType);

        switch ($fType) {
            case 'INT':
            case 'STRING':
            case 'VARCHAR':
                if ($required) {
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
                $fieldBlock->addItem(new IEGroupMembersEditor($fieldName, $keywordInfo['title'], $this->objectEdited, $value));
                break;
            case 'SLIDER':
                $SliderField = $fieldBlock->addItem(new EaseHtmlFieldSet($keywordInfo['title'], new EaseJQuerySlider($fieldName, (int) $value)));
                $SliderField->setTagCss(array('width' => '100%'));
                break;
            case 'TEXT':
                $FB = $fieldBlock->addItem(new EaseLabeledTextarea($fieldName, $value, $keywordInfo['title']));
                $FB->enclosedElement->setTagCss(array('width' => '100%'));
                break;
            case 'ENUM':
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));
                $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                $selector->addItems(array_combine($flags, $flags));
                $selector->enclosedElement->setTagCss(array('width' => '100%'));
                break;
            case 'RADIO':
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));
                if (is_array($flags)) {
                    foreach (array_values($flags) as $flag) {
                        $InfoFlags[$flag] = '&nbsp;' . $keywordInfo[$flag] . '<br>';
                    }
                    $buttons = new EaseHtmlRadiobuttonGroup($fieldName, $InfoFlags);
                    $buttons->setValue($value);
                    $FB = $fieldBlock->addItem(new EaseHtmlFieldSet($keywordInfo['title'], $buttons));
                    $FB->setTagCss(array('width' => '100%'));
                }
                break;
            case 'SELECT':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $STable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = array();
                }

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array($this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('public' => 1))) . ")  ";

                $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $nameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $sqlConds . ' ' .
                        'ORDER BY ' . $nameColumn, $IDColumn);

                $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                if (!$required) {
                    $selector->addItems(array('' => ''));
                }
                if (count($membersAviableArray)) {
                    $selector->addItems(array_combine($membersAviableArray, $membersAviableArray));
                }
                break;

            case 'SELECT+PARAMS':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $STable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = array();
                }

                $conditions['command_type'] = 'check';

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('command_local' => true, $this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect($conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

                $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $nameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $sqlConds . ' ' .
                        'ORDER BY ' . $nameColumn, $IDColumn);

                $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                if (!$required) {
                    $selector->addItems(array('' => ''));
                }
                if (count($membersAviableArray)) {
                    $selector->addItems(array_combine($membersAviableArray, $membersAviableArray));
                }

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('command_remote' => true, $this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect($conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

                $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $nameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $sqlConds . ' ' .
                        'ORDER BY ' . $nameColumn, $IDColumn);

                $addNewItem = $fieldBlock->addItem(new EaseHtmlInputSearchTag($fieldName . '-remote', $this->objectEdited->getDataValue($fieldName . '-remote'), array('class' => 'search-input', 'title' => _('vzdálený test'))));
                $addNewItem->setDataSource('jsoncommand.php?maxRows=10');

                $fieldBlock->addItem(new EaseLabeledTextInput($fieldName . '-params', $this->objectEdited->getDataValue($fieldName . '-params'), _('Parametry'), array('style' => 'width: 100%')));

                break;
            case 'USER':
                $fieldBlock->addItem(new IEUserSelect($fieldName, null, $this->objectEdited->getDataValue($fieldName), null, array('style' => 'width: 100%')));
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
            $this->objectEdited->keywordsInfo[$this->objectEdited->userColumn] = array('title' => 'vlastník');
            $this->objectEdited->useKeywords[$this->objectEdited->userColumn] = 'USER';
        }

        if ($this->objectEdited->allowTemplating) {
            if (!(int) $this->objectEdited->getDataValue('register')) {
                $this->addStatusMessage('toto je pouze předloha');
                foreach ($this->objectEdited->keywordsInfo as $Kw => $Props) {
                    unset($this->objectEdited->keywordsInfo[$Kw]['required']);
                }
                $this->objectEdited->keywordsInfo['name']['required'] = true;
                $this->objectEdited->keywordsInfo['register']['required'] = true;
            } else {
                $this->objectEdited->keywordsInfo['name']['required'] = false;
                $this->objectEdited->keywordsInfo['register']['required'] = false;
            }
        }
        if (!(int) $this->objectEdited->getDataValue('generate')) {
            $this->addStatusMessage(_('tento záznam se nebude generovat do konfigurace'));
        }
        if ($this->objectEdited->publicRecords) {
            if ((int) $this->objectEdited->getDataValue('public')) {
                $this->addStatusMessage(_('tento záznam je veřejný'));
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $use = $this->objectEdited->getDataValue('use');
        if (!is_null($use)) {
            $template = clone $this->objectEdited;
            $template->loadFromMySQL((int) $use);
        }

        foreach ($this->objectEdited->useKeywords as $fieldName => $fieldType) {

            $keywordInfo = $this->objectEdited->keywordsInfo[$fieldName];

            if (!count($keywordInfo)) {
                continue;
            }

            if (isset($keywordInfo['hidden'])) {
                continue;
            }

            if (!isset($keywordInfo)) {
                $this->addStatusMessage(_('Info Chybí') . '   ' . $fieldType . ' ' . $fieldName, 'warning');
                continue;
            }

            if (!isset($keywordInfo['title'])) {
                $this->addStatusMessage(_('sloupec bez popisku') . ' ' . $fieldName, 'warning');
            }

            if (!strlen($keywordInfo['title'])) {
                continue;
            }

            if (isset($keywordInfo['required']) && $keywordInfo['required']) {
                $this->reqFields[$fieldName] = $fieldType;
                $required = true;
            } else {
                $required = false;
            }

            $value = $this->objectEdited->getDataValue($fieldName);

            if (is_null($value)) {
                if (!EaseShared::webPage()->isPosted()) {
                    $value = '';
                } else {
                    continue;
                }
            }
            if ($value == 'NULL') {
                $value = null;
            }

            if ($this->objectEdited->allowTemplating) {
                if ($this->objectEdited->isTemplate()) {
                    if (EaseShared::webPage()->isPosted() && is_null($value) && $required) {
                        $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $keywordInfo['title'], 'warning');
                    }
                }
            } else {
                if (EaseShared::webPage()->isPosted() && is_null($value) && $required) {
                    $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $keywordInfo['title'], 'warning');
                }
            }

            $mainFieldBlock = $this->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));

            $fieldLabel = $mainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $fieldName . '">' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));

            if (!$required || !(int) $this->objectEdited->getDataValue('register')) {
                $fieldLabel->addItem(new EaseHtmlATag('#', EaseTWBPart::GlyphIcon('remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
                $fieldLabel->setTagClass('FieldLabel');
            } else {
                $mainFieldBlock->setTagClass('fieldblock req');
            }

            $fieldBlock = $mainFieldBlock->addItem(new EaseHtmlDivTag($fieldName . '-controls'));

            if (!$this->objectEdited->isOwnedBy() && !EaseShared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
                if ($this->objectEdited->getId()) {
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
                if (!is_null($TempValue) && ($fieldName != $this->objectEdited->nameColumn) && !$required) { //Skrýt nedůležité položky
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
        if (isset($this->reqFields) && count($this->reqFields)) {

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
            foreach ($this->reqFields as $FieldName => $FieldType) {
                $FRules[] = "\n\"$FieldName\": \"required\"";
            }
            $Rules.= implode(',', $FRules) . "\n}});\n";

            //$Rules = ' $("#' . $this->parentObject->getTagProperty('name') . '").validate();';

            EaseShared::webPage()->addJavaScript($Rules, NULL, true);
        }
    }

    /**
     * Editor běžného uživatele
     */
    public function lightEditor()
    {

        if (!(int) $this->objectEdited->getDataValue('generate')) {
            $this->addStatusMessage(_('tento záznam se nebude generovat do konfigurace'));
        }
        if ($this->objectEdited->publicRecords) {
            if ((int) $this->objectEdited->getDataValue('public')) {
                $this->addStatusMessage(_('tento záznam je veřejný'));
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $use = $this->objectEdited->getDataValue('use');
        if (!is_null($use)) {
            $template = clone $this->objectEdited;
            $template->loadFromMySQL((int) $use);
        }

        foreach ($this->objectEdited->useKeywords as $fieldName => $fieldType) {

            $keywordInfo = $this->objectEdited->keywordsInfo[$fieldName];

            if ($fieldName == $this->objectEdited->nameColumn) {
                continue;
            }

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
                $this->reqFields[$fieldName] = $fieldType;
                $required = true;
            } else {
                if (!isset($keywordInfo['mandatory']) || !$keywordInfo['mandatory']) {
                    $required = false;
                    continue;
                }
            }

            $value = $this->objectEdited->getDataValue($fieldName);

            if (is_null($value)) {
                if (!EaseShared::webPage()->isPosted()) {
                    $value = '';
                } else {
                    continue;
                }
            }
            if ($value == 'NULL') {
                $value = null;
            }

            if ($this->objectEdited->allowTemplating) {
                if ($this->objectEdited->isTemplate()) {
                    if (EaseShared::webPage()->isPosted() && is_null($value) && $required) {
                        $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $keywordInfo['title'], 'warning');
                    }
                }
            } else {
                if (EaseShared::webPage()->isPosted() && is_null($value) && $required) {
                    $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $keywordInfo['title'], 'warning');
                }
            }

            $mainFieldBlock = $this->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));

            $fieldLabel = $mainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $fieldName . '">' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));

            if (!$required || !(int) $this->objectEdited->getDataValue('register')) {
                $fieldLabel->addItem(new EaseHtmlATag('#', EaseTWBPart::GlyphIcon('icon-remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
                $fieldLabel->setTagClass('FieldLabel');
            } else {
                $mainFieldBlock->setTagClass('fieldblock req');
            }

            $fieldBlock = $mainFieldBlock->addItem(new EaseHtmlDivTag($fieldName . '-controls'));

            if (!$this->objectEdited->isOwnedBy() && !EaseShared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
                if ($this->objectEdited->getId()) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $fieldBlock->addItem($value);
                    continue;
                }
            }

            if (isset($template)) {
                $tempValue = $template->getDataValue($fieldName);
                if (!is_null($tempValue) && ($fieldName != $this->objectEdited->nameColumn) && !$required) { //Skrýt nedůležité položky
                    EaseShared::webPage()->addJavaScript("$('#" . $fieldName . "-controls').hide();", null, true);
                }
            }
            $this->insertWidget($fieldBlock, $fieldName, $value);
        }
    }

}
