<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once 'IEGroupMembersEditor.php';
require_once 'IEUserSelect.php';
require_once 'EaseTWBSlider.php';
require_once 'IEPlatformSelector.php';
require_once 'IEYesNoSwitch.php';

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
     * @param IECfg  $this->ObjectEdited
     * @param string $onlyColumn         Vrací editační pole jen pro sloupec daného jména
     */
    public function __construct($cfgObject, $onlyColumn = null)
    {
        parent::__construct();
        $this->objectEdited = &$cfgObject;

        if ($onlyColumn) {
            $this->insertWidget($this, $onlyColumn, $this->objectEdited->getDataValue($onlyColumn));
        } else {

            if (EaseShared::user()->getSettingValue('admin')) {
                $this->fullEditor();
            } else {
                $this->lightEditor();
            }

            if ($cfgObject->getId()) {
                $this->addItem(new EaseHtmlInputHiddenTag($cfgObject->getmyKeyColumn(), $cfgObject->getMyKey(), array('class' => 'keyId')));
            }

            $this->addItem(new EaseHtmlInputHiddenTag('class', get_class($cfgObject)));
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
        $disabled = false;
        $hint = '';
        $keywordInfo = $this->objectEdited->keywordsInfo[$fieldName];
        $fieldType = $this->objectEdited->useKeywords[$fieldName];
        $fType = preg_replace('/\(.*\)/', '', $fieldType);
        $required = (isset($keywordInfo['requeired']) && ($keywordInfo['requeired'] === true));

        if ($this->objectEdited->allowTemplating) {
            $effective = $this->objectEdited->getCfg($fieldName, true);
            $templateName = key($effective);
            $templateValue = current($effective);

            EaseShared::webPage()->addJavaScript("$(\"#useTpl$fieldName\").change(function(){
    if( this.checked ){
        $(\"[name='$fieldName']\").prop('disabled', true);
    } else {
        $(\"[name='$fieldName']\").prop('disabled', false);
    }
    //$(\"input[name='$fieldName']\").bootstrapSwitch('toggleDisabled', true);

});", null, true);

            if ($templateName && !is_null($templateValue)) {
                $fieldBlock->addItem(new EaseHtmlCheckboxTag(null, true, 1, array('id' => 'useTpl' . $fieldName)));
                $hint = current($effective);
                $disabled = true;
            } else {
                $fieldBlock->addItem(new EaseHtmlCheckboxTag(null, false, 1, array('id' => 'useTpl' . $fieldName)));
                $hint = $value;
            }
            $fieldBlock->addItem(' ' . _('Hodnota z předlohy') . ':');
            $fieldBlock->addItem(new EaseHtmlATag('search.php?search=' . key($effective), key($effective)));
            $fieldBlock->addItem(': ' . current($effective));
        }

        if ($disabled) {
            EaseShared::webPage()->addJavaScript("$(\"[name='$fieldName']\").prop('disabled', true);", null, true);
        }


        switch ($fType) {
            case 'INT':
            case 'STRING':
            case 'VARCHAR':
//$fieldBlock->addItem($this->optionEnabler($fieldName));


                if ($required) {
                    $fieldBlock->addItem(new EaseTWBFormGroup($fieldName, new EaseHtmlInputTextTag($fieldName, $value, array('class' => 'required form-control', 'title' => $fieldName)), $hint, $keywordInfo['title']));
//                    $fieldBlock->addItem(new EaseHtmlDivTag(null, new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('class' => 'required form-control', 'title' => $fieldName))));
                } else {
                    $fieldBlock->addItem(new EaseTWBFormGroup($fieldName, new EaseHtmlInputTextTag($fieldName, $value, array('title' => $fieldName, 'class' => 'form-control')), $hint, $keywordInfo['title']));
//                    $fieldBlock->addItem(new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('title' => $fieldName, 'class' => 'form-control')));
                }
                break;
            case 'TINYINT':
            case 'BOOL':
                $fieldBlock->addItem(new EaseTWBFormGroup($keywordInfo['title'], new IEYesNoSwitch($fieldName, $value, 'on', array('id' => $keywordInfo['title'] . 'sw'))));

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
                $sliderField = $fieldBlock->addItem(new EaseTWBPanel($keywordInfo['title'], 'default', new EaseHtmlCheckboxGroup($fieldName, $checkboxes, $values)));
                $sliderField->setTagCss(array('width' => '100%'));
                break;
            case 'IDLIST':
                if (!is_array($value)) {
                    $values = array();
                }
                $fieldBlock->addItem(new IEGroupMembersEditor($fieldName, $keywordInfo['title'], $this->objectEdited, $value));
                break;
            case 'SLIDER':
                $sliderField = $fieldBlock->addItem(new EaseTWBPanel($keywordInfo['title'], 'default', new EaseTWBSlider($fieldName, (int) $value, array('data-slider-min' => 0))));
                $sliderField->setTagCss(array('width' => '100%'));
                break;
            case 'TEXT':
                $fieldBlock->addItem(new EaseTWBFormGroup($keywordInfo['title'], new EaseTWBTextarea($fieldName, $value, array('style' => 'width:100%'))));
                break;
            case 'ENUM':
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));
                $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                $selector->addItems(array_combine($flags, $flags));
                $selector->enclosedElement->setTagCss(array('width' => '100%'));
                $selector->enclosedElement->setTagClass('form-control');
                break;
            case 'PLATFORM':
                $fieldBlock->addItem(new EaseTWBFormGroup($keywordInfo['title'], new IEPlatformSelector($fieldName, null, $value)));
                break;
            case 'RADIO':
                $flags = explode(',', str_replace(array($fType, "'", '(', ')'), '', $fieldType));
                if (is_array($flags)) {
                    foreach (array_values($flags) as $flag) {
                        $infoFlags[$flag] = '&nbsp;' . $keywordInfo[$flag];
                    }
                    $buttons = new EaseHtmlRadiobuttonGroup($fieldName, $infoFlags);
                    $buttons->setValue($value);
                    $FB = $fieldBlock->addItem(new EaseTWBPanel($keywordInfo['title'], 'default', new EaseTWRadioButtonGroup($fieldName, $infoFlags, $value)));
                    $FB->setTagCss(array('width' => '100%'));
                }
                break;

            case 'SELECT':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $sTable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = array();
                }

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array($this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('public' => 1))) . ")  ";

                $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                    'SELECT ' . $nameColumn . ' ' .
                    'FROM `' . $sTable . '` ' .
                    'WHERE ' . $sqlConds . ' ' .
                    'ORDER BY ' . $nameColumn, $IDColumn);

                $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                $selector->enclosedElement->setTagClass('form-control');
                if (!$required) {
                    $selector->addItems(array('NULL' => _('Výchozí')));
                }
                if (count($membersAviableArray)) {
                    $selector->addItems(array_combine($membersAviableArray, $membersAviableArray));
                }
                break;

            case 'SELECTID':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $sTable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = array();
                }

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array($this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('public' => 1))) . ")  ";

                $membersAviableArray = EaseShared::myDbLink()->queryToArray(
                    'SELECT ' . $nameColumn . ',' . $IDColumn . ' ' .
                    'FROM `' . $sTable . '` ' .
                    'WHERE ' . $sqlConds . ' ' .
                    'ORDER BY ' . $nameColumn, $IDColumn);

                $selector = $fieldBlock->addItem(new EaseLabeledSelect($fieldName, $value, $keywordInfo['title']));
                $selector->enclosedElement->setTagClass('form-control');
                if (!$required) {
                    $selector->addItems(array('NULL' => _('Výchozí')));
                }
                if (count($membersAviableArray)) {
                    foreach ($membersAviableArray as $option) {
                        $options[$option[$IDColumn]] = $option[$nameColumn];
                    }
                    $selector->addItems($options);
                }
                break;


            case 'SELECT+PARAMS':
                $IDColumn = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $sTable = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = array();
                }

                $conditions['command_type'] = 'check';

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('command_local' => true, $this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect($conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

                if (isset($this->objectEdited->keywordsInfo['platform'])) {
                    $platform = $this->objectEdited->getDataValue('platform');
                    $sqlConds .= " AND ((`platform` =  '" . $platform . "') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";
                }

                $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                    'SELECT ' . $nameColumn . ' ' .
                    'FROM `' . $sTable . '` ' .
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

                $sqlConds = " ( " . $this->objectEdited->myDbLink->prepSelect(array_merge($conditions, array('command_remote' => true, $this->objectEdited->userColumn => EaseShared::user()->getUserID()))) . " ) OR ( " . $this->objectEdited->myDbLink->prepSelect($conditions) . " AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->myDbLink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => EaseShared::user()->getUserID())));

                $membersAviableArray = EaseShared::myDbLink()->queryTo2DArray(
                    'SELECT ' . $nameColumn . ' ' .
                    'FROM `' . $sTable . '` ' .
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
        $tabs = new EaseTWBTabs('editorTabs');
        $tabs->addTab(_('Více nastavení:'));

        if (EaseShared::user()->getSettingValue('admin')) {
            $this->objectEdited->keywordsInfo[$this->objectEdited->userColumn] = array(
              'severity' => 'advanced',
              'title' => 'vlastník');
            $this->objectEdited->useKeywords[$this->objectEdited->userColumn] = 'USER';
        }

        if ($this->objectEdited->allowTemplating) {
            if (!$this->objectEdited->getCfgValue('register')) {
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
        if (isset($this->objectEdited->useKeywords['generate']) && !(int) $this->objectEdited->getDataValue('generate')) {
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
            if (!isset($keywordInfo['severity'])) {
                $this->addStatusMessage(sprintf(_('Sloupeček %s/%s nemá uvedenou závažnost'), $fieldName, get_class($this->objectEdited)), 'warning');
                $keywordInfo['severity'] = null;
            }

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

//            if (is_null($value)) {
//                if (!EaseShared::webPage()->isPosted()) {
//                    $value = '';
//                } else {
//                    continue;
//                }
//            }
            if ($value === 'NULL') {
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



            switch ($keywordInfo['severity']) {
                case 'advanced':
                    if (!isset($advancedTab)) {
                        $advancedTab = $tabs->addTab(_('Rozšířené'));
                    }
                    $mainFieldBlock = $advancedTab->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));
                    break;
                case 'optional':
                    if (!isset($optionalTab)) {
                        $optionalTab = $tabs->addTab(_('Volitelné'));
                    }
                    $mainFieldBlock = $optionalTab->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));
                    break;
                default :
                    $mainFieldBlock = $this->addItem(new EaseHtmlDivTag($fieldName . '-block', null, array('class' => 'fieldblock')));
                    break;
            }


            $fieldLabel = $mainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a>' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));
            /**

              if (!$required || !(int) $this->objectEdited->getDataValue('register')) {
              $fieldLabel->addItem(new EaseHtmlATag('#', EaseTWBPart::GlyphIcon('remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
              $fieldLabel->setTagClass('FieldLabel');
              } else {
              $mainFieldBlock->setTagClass('fieldblock req');
              }
             */
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
                $tempValue = $template->getDataValue($fieldName);
                if (!is_null($tempValue) && ($fieldName != $this->objectEdited->nameColumn) && !$required) { //Skrýt nedůležité položky
// EaseShared::webPage()->addJavaScript("$('#" . $fieldName . "-controls').hide();", null, true);
                }
            }

            $this->insertWidget($fieldBlock, $fieldName, $value);
        }

        $this->addItem($tabs);
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
            foreach ($this->reqFields as $fieldName => $fieldType) {
                $fRules[] = "\n\"$fieldName\": \"required\"";
            }
            $Rules.= implode(',', $fRules) . "\n}});\n";

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
                if ($this->objectEdited->getId()) {
                    continue;
                }
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
            /*

              $fieldLabel = $mainFieldBlock->addItem(new EaseHtmlDivTag(null, '<a name="' . $fieldName . '">' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));

              if (!$required || !(int) $this->objectEdited->getDataValue('register')) {
              $fieldLabel->addItem(new EaseHtmlATag('#', EaseTWBPart::GlyphIcon('icon-remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
              $fieldLabel->setTagClass('FieldLabel');
              } else {
              $mainFieldBlock->setTagClass('fieldblock req');
              }
             */
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

    /**
     * Zaškrtávací políčko pro NULL
     *
     * @param string $name
     * @return \EaseHtmlCheckboxTag
     */
    public function optionEnabler($name)
    {
        return new EaseHtmlCheckboxTag(null, false, 1, array('id' => 'useTpl' . $name));
    }

}
