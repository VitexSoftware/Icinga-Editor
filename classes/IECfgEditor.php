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
    function __construct($CFGObject)
    {
        parent::__construct();
        $this->ObjectEdited = &$CFGObject;
        if (EaseShared::user()->getSettingValue('admin')) {
            $this->fullEditor();
        } else {
            $this->lightEditor();
        }
    }

    /**
     * Vloží do stránky widget pro editaci hodnoty
     * 
     * @param EaseHtmlDivTag $FieldBlock
     * @param string $FieldName
     * @param mixed $Value
     */
    function insertWidget($FieldBlock, $FieldName, $Value)
    {
        $KeywordInfo = $this->ObjectEdited->KeywordsInfo[$FieldName];
        $FieldType = $this->ObjectEdited->UseKeywords[$FieldName];
        $Required = (isset($KeywordInfo['requeired']) && ($KeywordInfo['requeired'] === true));
        $FType = preg_replace('/\(.*\)/', '', $FieldType);

        switch ($FType) {
            case 'INT':
            case 'STRING':
            case 'VARCHAR':
                if ($Required) {
                    $FieldBlock->addItem(new EaseHtmlDivTag(null, new EaseLabeledTextInput($FieldName, $Value, $KeywordInfo['title'], array('class' => 'required', 'title' => $FieldName))));
                } else {
                    $FieldBlock->addItem(new EaseLabeledTextInput($FieldName, $Value, $KeywordInfo['title'], array('title' => $FieldName)));
                }
                break;
            case 'TINYINT':
            case 'BOOL':
                $FieldBlock->addItem(new EaseLabeledCheckbox($FieldName, $Value, $KeywordInfo['title'], array('title' => $FieldName)));
                break;
            case 'FLAGS':
                $Values = array();
                $Checkboxes = array();
                $Flags = explode(',', str_replace(array($FType, "'", '(', ')'), '', $FieldType));

                foreach ($Flags as $Flag) {
                    if (isset($KeywordInfo[$Flag])) {
                        $Checkboxes[$Flag] = $KeywordInfo[$Flag];
                    } else {
                        $this->addStatusMessage(_('Chybi definice') . ' ' . $FieldName . ' ' . $Flag, 'error');
                    }
                }
                foreach ($Checkboxes as $ChKey => $ChTopic) {
                    $Checkboxes[$ChKey] = '&nbsp;' . $ChTopic . '</br>';
                    if (strchr($Value, $ChKey)) {
                        $Values[$ChKey] = true;
                    } else {
                        $Values[$ChKey] = false;
                    }
                }
                $SliderField = $FieldBlock->addItem(new EaseHtmlFieldSet($KeywordInfo['title'], new EaseHtmlCheckboxGroup($FieldName, $Checkboxes, $Values)));
                $SliderField->setTagCss(array('width' => '100%'));
                break;
            case 'IDLIST':
                if (!is_array($Value)) {
                    $Values = array();
                }
                $FieldBlock->addItem(new IEGroupMembersEditor($FieldName, $KeywordInfo['title'], $this->ObjectEdited, $Value));
                break;
            case 'SLIDER':
                $SliderField = $FieldBlock->addItem(new EaseHtmlFieldSet($KeywordInfo['title'], new EaseJQuerySlider($FieldName, (int) $Value)));
                $SliderField->setTagCss(array('width' => '100%'));
                break;
            case 'TEXT':
                $FB = $FieldBlock->addItem(new EaseLabeledTextarea($FieldName, $Value, $KeywordInfo['title']));
                $FB->EnclosedElement->setTagCss(array('width' => '100%'));
                break;
            case 'ENUM':
                $Flags = explode(',', str_replace(array($FType, "'", '(', ')'), '', $FieldType));
                $Selector = $FieldBlock->addItem(new EaseLabeledSelect($FieldName, $Value, $KeywordInfo['title']));
                $Selector->addItems(array_combine($Flags, $Flags));
                $Selector->EnclosedElement->setTagCss(array('width' => '100%'));
                break;
            case 'RADIO':
                $Flags = explode(',', str_replace(array($FType, "'", '(', ')'), '', $FieldType));
                if (is_array($Flags)) {
                    foreach (array_values($Flags) as $Flag) {
                        $InfoFlags[$Flag] = '&nbsp;' . $KeywordInfo[$Flag] . '<br>';
                    }
                    $Buttons = new EaseHtmlRadiobuttonGroup($FieldName, $InfoFlags);
                    $Buttons->setValue($Value);
                    $FB = $FieldBlock->addItem(new EaseHtmlFieldSet($KeywordInfo['title'], $Buttons));
                    $FB->setTagCss(array('width' => '100%'));
                }
                break;
            case 'SELECT':
                $IDColumn = $KeywordInfo['refdata']['idcolumn'];
                $NameColumn = $KeywordInfo['refdata']['captioncolumn'];
                $STable = $KeywordInfo['refdata']['table'];
                if (isset($KeywordInfo['refdata']['condition'])) {
                    $Conditions = $KeywordInfo['refdata']['condition'];
                } else {
                    $Conditions = array();
                }

                $SqlConds = " ( " . $this->ObjectEdited->MyDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->UserColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->ObjectEdited->MyDbLink->prepSelect(array_merge($Conditions, array('public' => 1))) . ")  ";

                $MembersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $NameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $SqlConds . ' ' .
                        'ORDER BY ' . $NameColumn, $IDColumn);

                $Selector = $FieldBlock->addItem(new EaseLabeledSelect($FieldName, $Value, $KeywordInfo['title']));
                if (!$Required) {
                    $Selector->addItems(array('' => ''));
                }
                if (count($MembersAviableArray)) {
                    $Selector->addItems(array_combine($MembersAviableArray, $MembersAviableArray));
                }
                break;

            case 'SELECT+PARAMS':
                $IDColumn = $KeywordInfo['refdata']['idcolumn'];
                $NameColumn = $KeywordInfo['refdata']['captioncolumn'];
                $STable = $KeywordInfo['refdata']['table'];
                if (isset($KeywordInfo['refdata']['condition'])) {
                    $Conditions = $KeywordInfo['refdata']['condition'];
                } else {
                    $Conditions = array();
                }

                $Conditions['command_type'] = 'check';

                $SqlConds = " ( " . $this->ObjectEdited->MyDbLink->prepSelect(array_merge($Conditions, array('command_local' => true, $this->ObjectEdited->UserColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->ObjectEdited->MyDbLink->prepSelect($Conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->MyDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->UserColumn => EaseShared::user()->getUserID())));

                $MembersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $NameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $SqlConds . ' ' .
                        'ORDER BY ' . $NameColumn, $IDColumn);

                $Selector = $FieldBlock->addItem(new EaseLabeledSelect($FieldName, $Value, $KeywordInfo['title']));
                if (!$Required) {
                    $Selector->addItems(array('' => ''));
                }
                if (count($MembersAviableArray)) {
                    $Selector->addItems(array_combine($MembersAviableArray, $MembersAviableArray));
                }

                $SqlConds = " ( " . $this->ObjectEdited->MyDbLink->prepSelect(array_merge($Conditions, array('command_remote' => true, $this->ObjectEdited->UserColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->ObjectEdited->MyDbLink->prepSelect($Conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->MyDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->UserColumn => EaseShared::user()->getUserID())));

                $MembersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                        'SELECT ' . $NameColumn . ' ' .
                        'FROM `' . DB_PREFIX . $STable . '` ' .
                        'WHERE ' . $SqlConds . ' ' .
                        'ORDER BY ' . $NameColumn, $IDColumn);


                $AddNewItem = $FieldBlock->addItem(new EaseHtmlInputSearchTag($FieldName . '-remote', $this->ObjectEdited->getDataValue($FieldName . '-remote'), array('class' => 'search-input', 'title' => _('vzdálený test'))));
                $AddNewItem->setDataSource('jsoncommand.php?maxRows=10');



                $FieldBlock->addItem(new EaseLabeledTextInput($FieldName . '-params', $this->ObjectEdited->getDataValue($FieldName . '-params'), _('Parametry'), array('style' => 'width: 100%')));



                break;
            case 'USER':
                $FieldBlock->addItem(new IEUserSelect($FieldName, null, $this->ObjectEdited->getDataValue($FieldName), null, array('style' => 'width: 100%')));
                break;
            default:
                $FieldBlock->addItem(new EaseLabeledTextInput($FieldName, $Value, $KeywordInfo['title'], array('title' => $FieldName)));
                $this->addStatusMessage(sprintf(_('Neznámý typ %s pro sloupec %s'), $FType, $FieldName), 'warning');
                break;
        }
    }

    function fullEditor()
    {
        if (EaseShared::user()->getSettingValue('admin')) {
            $this->ObjectEdited->KeywordsInfo[$this->ObjectEdited->UserColumn] = array('title' => 'vlastník');
            $this->ObjectEdited->UseKeywords[$this->ObjectEdited->UserColumn] = 'USER';
        }


        if ($this->ObjectEdited->AllowTemplating) {
            if (!(int) $this->ObjectEdited->getDataValue('register')) {
                $this->addStatusMessage('toto je pouze předloha');
                foreach ($this->ObjectEdited->KeywordsInfo as $Kw => $Props) {
                    unset($this->ObjectEdited->KeywordsInfo[$Kw]['required']);
                }
                $this->ObjectEdited->KeywordsInfo['name']['required'] = true;
                $this->ObjectEdited->KeywordsInfo['register']['required'] = true;
            } else {
                $this->ObjectEdited->KeywordsInfo['name']['required'] = false;
                $this->ObjectEdited->KeywordsInfo['register']['required'] = false;
            }
        }
        if (!(int) $this->ObjectEdited->getDataValue('generate')) {
            $this->addStatusMessage(_('tento záznam se nebude generovat do konfigurace'));
        }
        if ($this->ObjectEdited->PublicRecords) {
            if ((int) $this->ObjectEdited->getDataValue('public')) {
                $this->addStatusMessage(_('tento záznam je veřejný'));
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $Use = $this->ObjectEdited->getDataValue('use');
        if (!is_null($Use)) {
            $Template = clone $this->ObjectEdited;
            $Template->loadFromMySQL((int) $Use);
        }

        foreach ($this->ObjectEdited->UseKeywords as $FieldName => $FieldType) {

            $KeywordInfo = $this->ObjectEdited->KeywordsInfo[$FieldName];

            if (!count($KeywordInfo)) {
                continue;
            }

            if (isset($KeywordInfo['hidden'])) {
                continue;
            }

            if (!isset($KeywordInfo)) {
                $this->addStatusMessage(_('Info Chybí') . '   ' . $FieldType . ' ' . $FieldName, 'warning');
                continue;
            }

            if (!isset($KeywordInfo['title'])) {
                $this->addStatusMessage(_('sloupec bez popisku') . ' ' . $FieldName, 'warning');
            }

            if (!strlen($KeywordInfo['title'])) {
                continue;
            }

            if (isset($KeywordInfo['required']) && $KeywordInfo['required']) {
                $this->ReqFields[$FieldName] = $FieldType;
                $Required = true;
            } else {
                $Required = false;
            }

            $Value = $this->ObjectEdited->getDataValue($FieldName);
            if ($Value == 'NULL') {
                $Value = null;
            }

            if ($this->ObjectEdited->AllowTemplating) {
                if ($this->ObjectEdited->isTemplate()) {
                    if (EaseShared::webPage()->isPosted() && is_null($Value) && $Required) {
                        $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $KeywordInfo['title'], 'warning');
                    }
                }
            } else {
                if (EaseShared::webPage()->isPosted() && is_null($Value) && $Required) {
                    $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $KeywordInfo['title'], 'warning');
                }
            }


            $MainFieldBlock = $this->addItem(new EaseHtmlDivTag($FieldName . '-block', null, array('class' => 'fieldblock')));

            $FieldLabel = $MainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $FieldName . '">' . $FieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $FieldName . "-controls').toggle('slow');")));

            if (!$Required || !(int) $this->ObjectEdited->getDataValue('register')) {
                $FieldLabel->addItem(new EaseHtmlATag('#', '<i title="' . _('Ignorovat') . '" class="icon-remove"></i>', array('onClick' => '$(\'#' . $FieldName . '-block\').empty().html(\'<input type=hidden name=' . $FieldName . ' value=NULL><div class=FieldLabel>' . $FieldName . '</div>\'); return false;')));
                $FieldLabel->setTagClass('FieldLabel');
            } else {
                $MainFieldBlock->setTagClass('fieldblock req');
            }

            $FieldBlock = $MainFieldBlock->addItem(new EaseHtmlDivTag($FieldName . '-controls'));

            if (!$this->ObjectEdited->isOwnedBy() && !EaseShared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
                if ($this->ObjectEdited->getId()) {
                    if (substr($Value, 0, 2) == 'a:') {
                        $Value = unserialize($Value);
                        if (is_array($Value)) {
                            $Value = implode(',', $Value);
                        }
                    }
                    $FieldBlock->addItem($Value);
                    continue;
                }
            }

            if (isset($Template)) {
                $TempValue = $Template->getDataValue($FieldName);
                if (!is_null($TempValue) && ($FieldName != $this->ObjectEdited->NameColumn) && !$Required) { //Skrýt nedůležité položky
                    EaseShared::webPage()->addJavaScript("$('#" . $FieldName . "-controls').hide();", null, true);
                }
            }


            $this->insertWidget($FieldBlock, $FieldName, $Value);
        }
    }

    /**
     * jQuery pro vyžadované políčka 
     */
    function finalize()
    {
        EaseShared::webPage()->includeJavaScript('js/jquery.validate.js');
        if (isset($this->ReqFields) && count($this->ReqFields)) {

            $Rules = ' $("#' . $this->ParentObject->getTagProperty('name') . '").validate({
		invalidHandler: function(e, validator) {
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

            //$Rules = ' $("#' . $this->ParentObject->getTagProperty('name') . '").validate();';

            EaseShared::webPage()->addJavaScript($Rules, NULL, true);
        }
    }

    function lightEditor()
    {

        if (!(int) $this->ObjectEdited->getDataValue('generate')) {
            $this->addStatusMessage('tento záznam se nebude generovat do konfigurace');
        }
        if ($this->ObjectEdited->PublicRecords) {
            if ((int) $this->ObjectEdited->getDataValue('public')) {
                $this->addStatusMessage('tento záznam je veřejný');
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $Use = $this->ObjectEdited->getDataValue('use');
        if (!is_null($Use)) {
            $Template = clone $this->ObjectEdited;
            $Template->loadFromMySQL((int) $Use);
        }

        foreach ($this->ObjectEdited->UseKeywords as $FieldName => $FieldType) {

            $KeywordInfo = $this->ObjectEdited->KeywordsInfo[$FieldName];

            if (!count($KeywordInfo)) {
                continue;
            }

            if (isset($KeywordInfo['hidden'])) {
                continue;
            }

            if (!strlen($KeywordInfo['title'])) {
                continue;
            }

            if (isset($KeywordInfo['required']) && $KeywordInfo['required']) {
                $this->ReqFields[$FieldName] = $FieldType;
                $Required = true;
            } else {
                if (!isset($KeywordInfo['mandatory']) || !$KeywordInfo['mandatory']) {
                    $Required = false;
                    continue;
                }
            }

            $Value = $this->ObjectEdited->getDataValue($FieldName);
            if ($Value == 'NULL') {
                $Value = null;
            }

            if ($this->ObjectEdited->AllowTemplating) {
                if ($this->ObjectEdited->isTemplate()) {
                    if (EaseShared::webPage()->isPosted() && is_null($Value) && $Required) {
                        $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $KeywordInfo['title'], 'warning');
                    }
                }
            } else {
                if (EaseShared::webPage()->isPosted() && is_null($Value) && $Required) {
                    $this->addStatusMessage(_('Není vyplněna povinná položka') . ' ' . $KeywordInfo['title'], 'warning');
                }
            }


            $MainFieldBlock = $this->addItem(new EaseHtmlDivTag($FieldName . '-block', null, array('class' => 'fieldblock')));

            $FieldLabel = $MainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $FieldName . '">' . $FieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $FieldName . "-controls').toggle('slow');")));

            if (!$Required || !(int) $this->ObjectEdited->getDataValue('register')) {
                $FieldLabel->addItem(new EaseHtmlATag('#', '<i title="' . _('Ignorovat') . '" class="icon-remove"></i>', array('onClick' => '$(\'#' . $FieldName . '-block\').empty().html(\'<input type=hidden name=' . $FieldName . ' value=NULL><div class=FieldLabel>' . $FieldName . '</div>\'); return false;')));
                $FieldLabel->setTagClass('FieldLabel');
            } else {
                $MainFieldBlock->setTagClass('fieldblock req');
            }

            $FieldBlock = $MainFieldBlock->addItem(new EaseHtmlDivTag($FieldName . '-controls'));

            if (!$this->ObjectEdited->isOwnedBy() && !EaseShared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
                if ($this->ObjectEdited->getId()) {
                    if (is_array($Value)) {
                        $Value = implode(',', $Value);
                    }
                    $FieldBlock->addItem($Value);
                    continue;
                }
            }

            if (isset($Template)) {
                $TempValue = $Template->getDataValue($FieldName);
                if (!is_null($TempValue) && ($FieldName != $this->ObjectEdited->NameColumn) && !$Required) { //Skrýt nedůležité položky
                    EaseShared::webPage()->addJavaScript("$('#" . $FieldName . "-controls').hide();", null, true);
                }
            }
            $this->insertWidget($FieldBlock, $FieldName, $Value);
        }
    }

}

?>
