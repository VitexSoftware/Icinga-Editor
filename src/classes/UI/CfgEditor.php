<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEcfgEditor
 *
 * @author vitex
 */
class CfgEditor extends \Ease\Container
{
    /**
     * Current edited object
     * @var \Icinga\Editor\Engine\Configurator Objekt konfigurace
     */
    public $objectEdited = null;

    /**
     * Form required columns names
     * @var array
     */
    public $reqFields = [];

    /**
     * Vytvoří editační formulář podle CFG objektu
     *
     * @param \Icinga\Editor\Engine\Configurator  $this->ObjectEdited
     * @param string $onlyColumn         Vrací editační pole jen pro sloupec daného jména
     */
    public function __construct($cfgObject, $onlyColumn = null)
    {
        parent::__construct();
        $this->objectEdited = &$cfgObject;

        if ($onlyColumn) {
            $this->insertWidget($this, $onlyColumn,
                $this->objectEdited->getDataValue($onlyColumn));
        } else {

            if (\Ease\Shared::user()->getSettingValue('admin')) {
                $this->fullEditor();
            } else {
                $this->lightEditor();
            }

            if ($cfgObject->getId()) {
                $this->addItem(new \Ease\Html\InputHiddenTag($cfgObject->getmyKeyColumn(),
                    $cfgObject->getMyKey(), ['class' => 'keyId']));
            }

            $this->addItem(new \Ease\Html\InputHiddenTag('class',
                get_class($cfgObject)));
        }
        \Ease\Shared::webPage()->includeJavaScript('js/datasaver.js');
    }

    /**
     * Vloží do stránky widget pro editaci hodnoty
     *
     * @param \Ease\Html\DivTag $fieldBlock
     * @param string         $fieldName
     * @param mixed          $value
     */
    public function insertWidget($fieldBlock, $fieldName, $value)
    {
        $disabled    = false;
        $hint        = '';
        $keywordInfo = $this->objectEdited->keywordsInfo[$fieldName];
        $fieldType   = $this->objectEdited->useKeywords[$fieldName];
        $fType       = preg_replace('/\(.*\)/', '', $fieldType);
        $required    = (isset($keywordInfo['requeired']) && ($keywordInfo['requeired']
            === true));

        if ($this->objectEdited->allowTemplating) {
            $effective     = $this->objectEdited->getCfg($fieldName, true);
            $templateName  = key($effective);
            $templateValue = current($effective);

            if ($fType == 'BOOL') {
                \Ease\Shared::webPage()->addJavaScript("$(\"#useTpl$fieldName\").change(function(){
    if( this.checked ){
        $(\"[name='$fieldName']\").prop('disabled', true);
        $(\"input[name='$fieldName']\").bootstrapSwitch('toggleDisabled', true);

    } else {
        $(\"[name='$fieldName']\").prop('disabled', false);
        $(\"input[name='$fieldName']\").bootstrapSwitch('toggleDisabled', false);
    }

});", null, true);
            } else {

                \Ease\Shared::webPage()->addJavaScript("$(\"#useTpl$fieldName\").change(function(){
    if( this.checked ){
        $(\"[name='$fieldName']\").prop('disabled', true);
    } else {
        $(\"[name='$fieldName']\").prop('disabled', false);
    }

});", null, true);
            }

            $templateCheckBoxName = "useFromTemplate[$fieldName]";

            if ($templateName && !is_null($templateValue)) {
                $fieldBlock->addItem(new \Ease\Html\CheckboxTag($templateCheckBoxName,
                    true, 1, ['id' => 'useTpl'.$fieldName]));
                $hint     = current($effective);
                $disabled = true;
            } else {
                $fieldBlock->addItem(new \Ease\Html\CheckboxTag($templateCheckBoxName,
                    false, 1, ['id' => 'useTpl'.$fieldName]));
                $hint = $value;
            }
            $fieldBlock->addItem(' '._('Value from template').':');
            $fieldBlock->addItem(new \Ease\Html\ATag('search.php?search='.key($effective),
                key($effective)));
            $fieldBlock->addItem(': '.current($effective));
        }

        if ($disabled) {
            \Ease\Shared::webPage()->addJavaScript("$(\"[name='$fieldName']\").prop('disabled', true);",
                null, true);
        }

        switch ($fType) {
            case 'INT':
            case 'STRING':
            case 'VARCHAR':
//$fieldBlock->addItem($this->optionEnabler($fieldName));


                if ($required) {
                    $fieldBlock->addItem(new \Ease\TWB\FormGroup($fieldName,
                        new \Ease\Html\InputTextTag($fieldName, $value,
                        ['class' => 'required form-control', 'title' => $fieldName,
                        'OnChange' => $this->onChangeCode($fieldName)]), $hint,
                        $keywordInfo['title']));
//                    $fieldBlock->addItem(new \Ease\Html\Div( new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('class' => 'required form-control', 'title' => $fieldName))));
                } else {
                    $fieldBlock->addItem(new \Ease\TWB\FormGroup($fieldName,
                        new \Ease\Html\InputTextTag($fieldName, $value,
                        ['title' => $fieldName, 'class' => 'form-control', 'OnChange' => $this->onChangeCode($fieldName)]),
                        $hint, $keywordInfo['title']));
//                    $fieldBlock->addItem(new EaseLabeledTextInput($fieldName, $value, $keywordInfo['title'], array('title' => $fieldName, 'class' => 'form-control')));
                }
                break;
            case 'TINYINT':
            case 'BOOL':
                $fieldBlock->addItem(new \Ease\TWB\FormGroup($keywordInfo['title'],
                    new YesNoSwitch($fieldName, $value, 'on',
                    ['id' => $keywordInfo['title'].'sw'])));

                break;
            case 'FLAGS':
                $values     = [];
                $checkboxes = [];
                $flags      = explode(',',
                    str_replace([$fType, "'", '(', ')'], '', $fieldType));

                foreach ($flags as $flag) {
                    if (isset($keywordInfo[$flag])) {
                        $checkboxes[$flag] = $keywordInfo[$flag];
                    } else {
                        $this->addStatusMessage(_('Missing definiton').' '.$fieldName.' '.$flag,
                            'error');
                    }
                }
                foreach ($checkboxes as $chKey => $chTopic) {
                    $checkboxes[$chKey] = '&nbsp;'.$chTopic.'</br>';
                    if (strchr($value, $chKey)) {
                        $values[$chKey] = true;
                    } else {
                        $values[$chKey] = false;
                    }
                }
                $sliderField = $fieldBlock->addItem(new \Ease\TWB\Panel($keywordInfo['title'],
                    'default',
                    new \Ease\Html\CheckboxGroup($fieldName, $checkboxes,
                    $values)));
                $sliderField->setTagCss(['width' => '100%']);
                break;
            case 'IDLIST':
                if (!is_array($value)) {
                    $values = [];
                }
                $fieldBlock->addItem(new GroupMembersEditor($fieldName,
                    $keywordInfo['title'], $this->objectEdited, $value));
                break;
            case 'SLIDER':
                $sliderField = $fieldBlock->addItem(new \Ease\TWB\Panel($keywordInfo['title'],
                    'default',
                    new Slider($fieldName, (int) $value,
                    ['data-slider-min' => 0])));
                $sliderField->setTagCss(['width' => '100%']);
                break;
            case 'TEXT':
                $fieldBlock->addItem(new \Ease\TWB\FormGroup($keywordInfo['title'],
                    new \Ease\TWB\Textarea($fieldName, $value,
                    ['style' => 'width:100%', 'OnChange' => $this->onChangeCode($fieldName)])));
                break;
            case 'ENUM':
                $flags       = explode(',',
                    str_replace([$fType, "'", '(', ')'], '', $fieldType));
                $selector    = $fieldBlock->addItem(
                    new \Ease\TWB\FormGroup($keywordInfo['title'],
                    new \Ease\Html\Select($fieldName,
                    array_combine($flags, $flags), null, null,
                    ['OnChange' => $this->onChangeCode($fieldName)]))
                );
                break;
            case 'PLATFORM':
                $fieldBlock->addItem(new \Ease\TWB\FormGroup($keywordInfo['title'],
                    new PlatformSelector($fieldName, null, $value)));
                break;
            case 'RADIO':
                $flags       = explode(',',
                    str_replace([$fType, "'", '(', ')'], '', $fieldType));
                if (is_array($flags)) {
                    foreach (array_values($flags) as $flag) {
                        $infoFlags[$flag] = '&nbsp;'.$keywordInfo[$flag];
                    }
                    $buttons = new \Ease\Html\RadiobuttonGroup($fieldName,
                        $infoFlags);
                    $buttons->setValue($value);
                    $FB      = $fieldBlock->addItem(new \Ease\TWB\Panel($keywordInfo['title'],
                        'default',
                        new \Ease\TWB\RadioButtonGroup($fieldName, $infoFlags,
                        $value)));
                    $FB->setTagCss(['width' => '100%']);
                }
                break;

            case 'SELECT':
                $IDColumn   = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $sTable     = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = [];
                }

                $sqlConds = " ( ".$this->objectEdited->dblink->prepSelect(array_merge($conditions,
                            [$this->objectEdited->userColumn => \Ease\Shared::user()->getUserID()]))." ) OR ( ".$this->objectEdited->dblink->prepSelect(array_merge($conditions,
                            ['public' => 1])).")  ";

                $membersAviableArray = \Ease\Shared::db()->queryTo2DArray(
                    'SELECT '.$nameColumn.' '.
                    'FROM `'.$sTable.'` '.
                    'WHERE '.$sqlConds.' '.
                    'ORDER BY '.$nameColumn, $IDColumn);

                $selector = $fieldBlock->addItem(new \Ease\Html\Select($fieldName,
                    $value, $keywordInfo['title'],
                    ['OnChange' => $this->onChangeCode($fieldName)]));

                if (!$required) {
                    $selector->addItems(['NULL' => _('Default')]);
                }
                if (count($membersAviableArray)) {
                    $selector->addItems(array_combine($membersAviableArray,
                            $membersAviableArray));
                }
                break;

            case 'SELECTID':
                $IDColumn   = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $sTable     = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = [];
                }

                $sqlConds = " ( ".$this->objectEdited->dblink->prepSelect(array_merge($conditions,
                            [$this->objectEdited->userColumn => \Ease\Shared::user()->getUserID()]))." ) OR ( ".$this->objectEdited->dblink->prepSelect(array_merge($conditions,
                            ['public' => 1])).")  ";

                $membersAviableArray = \Ease\Shared::db()->queryToArray(
                    'SELECT '.$nameColumn.','.$IDColumn.' '.
                    'FROM `'.$sTable.'` '.
                    'WHERE '.$sqlConds.' '.
                    'ORDER BY '.$nameColumn, $IDColumn);

                $select = new \Ease\Html\Select($fieldName, null, $value, null,
                    ['OnChange' => $this->onChangeCode($fieldName)]);

                $selector = $fieldBlock->addItem(
                    new \Ease\TWB\FormGroup($keywordInfo['title'], $select)
                );


                if (!$required) {
                    $select->addItems(['NULL' => _('Default')]);
                }
                if (count($membersAviableArray)) {
                    foreach ($membersAviableArray as $option) {
                        $options[$option[$IDColumn]] = $option[$nameColumn];
                    }
                    $select->addItems($options);
                }
                break;


            case 'SELECT+PARAMS':
                $IDColumn   = $keywordInfo['refdata']['idcolumn'];
                $nameColumn = $keywordInfo['refdata']['captioncolumn'];
                $sTable     = $keywordInfo['refdata']['table'];
                if (isset($keywordInfo['refdata']['condition'])) {
                    $conditions = $keywordInfo['refdata']['condition'];
                } else {
                    $conditions = [];
                }

                $conditions['command_type'] = 'check';

                $sqlConds = " ( ".$this->objectEdited->dblink->prepSelect(array_merge($conditions,
                            ['command_local' => true, $this->objectEdited->userColumn => \Ease\Shared::user()->getUserID()]))." ) OR ( ".$this->objectEdited->dblink->prepSelect($conditions)." AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->dblink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => \Ease\Shared::user()->getUserID())));

                if (isset($this->objectEdited->keywordsInfo['platform'])) {
                    $platform = $this->objectEdited->getDataValue('platform');
                    $sqlConds .= " AND ((`platform` =  '".$platform."') OR (`platform` = 'generic') OR (`platform` IS NULL) OR (`platform`='') ) ";
                }

                $membersAviableArray = \Ease\Shared::db()->queryTo2DArray(
                    'SELECT '.$nameColumn.' '.
                    'FROM `'.$sTable.'` '.
                    'WHERE '.$sqlConds.' '.
                    'ORDER BY '.$nameColumn, $IDColumn);

                $select = new \Ease\Html\Select($fieldName, $value);
                $fieldBlock->addItem(new \Ease\TWB\FormGroup($keywordInfo['title'],
                    $select));

                if (!$required) {
                    $select->addItems(['' => '']);
                }
                if (count($membersAviableArray)) {
                    $select->addItems(array_combine($membersAviableArray,
                            $membersAviableArray));
                }

                $sqlConds = " ( ".$this->objectEdited->dblink->prepSelect(array_merge($conditions,
                            ['command_remote' => true, $this->objectEdited->userColumn => \Ease\Shared::user()->getUserID()]))." ) OR ( ".$this->objectEdited->dblink->prepSelect($conditions)." AND public=1 )  ";
//                    $SqlConds = $this->ObjectEdited->dblink->prepSelect(array_merge($Conditions, array($this->ObjectEdited->userColumn => \Ease\Shared::user()->getUserID())));

                $membersAviableArray = \Ease\Shared::db()->queryTo2DArray(
                    'SELECT '.$nameColumn.' '.
                    'FROM `'.$sTable.'` '.
                    'WHERE '.$sqlConds.' '.
                    'ORDER BY '.$nameColumn, $IDColumn);

                $addNewItem = $fieldBlock->addItem(new \Ease\Html\InputSearchTag($fieldName.'-remote',
                    $this->objectEdited->getDataValue($fieldName.'-remote'),
                    ['class' => 'search-input', 'title' => _('Remote Test')]));
                $addNewItem->setDataSource('jsoncommand.php?maxRows=10');

                $fieldBlock->addItem(new \Ease\TWB\FormGroup(_('Parameters'),
                        new \Ease\Html\InputTextTag($fieldName.'-params',
                    $this->objectEdited->getDataValue($fieldName.'-params')
                    , ['style' => 'width: 100%'])));

                break;
            case 'USER':
                $fieldBlock->addItem(new UserSelect($fieldName, null,
                    $this->objectEdited->getDataValue($fieldName), null,
                    ['style' => 'width: 100%', 'OnChange' => $this->onChangeCode($fieldName)]));
                break;
            default :
                $fieldBlock->addItem(new EaseLabeledTextInput($fieldName,
                    $value, $keywordInfo['title'], ['title' => $fieldName]));
                $this->addStatusMessage(sprintf(_('Unknown type of %s for column %s'),
                        $fType, $fieldName), 'warning');
                break;
        }
    }

    public function fullEditor()
    {
        $tabs = new \Ease\TWB\Tabs('editorTabs');
        $tabs->addTab(_('More Options:'));

        if (\Ease\Shared::user()->getSettingValue('admin')) {
            $this->objectEdited->keywordsInfo[$this->objectEdited->userColumn] = [
                'severity' => 'advanced',
                'title' => 'vlastník'];
            $this->objectEdited->useKeywords[$this->objectEdited->userColumn]  = 'USER';
        }

        if ($this->objectEdited->allowTemplating) {
            if (!$this->objectEdited->getCfgValue('register')) {
                $this->addStatusMessage('this is template only');
                foreach ($this->objectEdited->keywordsInfo as $Kw => $Props) {
                    unset($this->objectEdited->keywordsInfo[$Kw]['required']);
                }
                $this->objectEdited->keywordsInfo['name']['required']     = true;
                $this->objectEdited->keywordsInfo['register']['required'] = true;
            } else {
                $this->objectEdited->keywordsInfo['name']['required']     = false;
                $this->objectEdited->keywordsInfo['register']['required'] = false;
            }
        }
        if (isset($this->objectEdited->useKeywords['generate']) && !(int) $this->objectEdited->getDataValue('generate')) {
            $this->addStatusMessage(_('this record is not generated to icinga config file'),
                'warning');
        }
        if ($this->objectEdited->publicRecords) {
            if ((int) $this->objectEdited->getDataValue('public')) {
                $this->addStatusMessage(_('this record is public'));
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $use = $this->objectEdited->getDataValue('use');
        if (!is_null($use)) {
            $template = clone $this->objectEdited;
            $template->loadFromSQL((int) $use);
        }

        foreach ($this->objectEdited->useKeywords as $fieldName => $fieldType) {

            $keywordInfo = $this->objectEdited->keywordsInfo[$fieldName];
            if (!isset($keywordInfo['severity'])) {
                $this->addStatusMessage(sprintf(_('Column %s/%s without known severity'),
                        $fieldName, get_class($this->objectEdited)), 'warning');
                $keywordInfo['severity'] = null;
            }

            if (!count($keywordInfo)) {
                continue;
            }

            if (isset($keywordInfo['hidden'])) {
                continue;
            }

            if (!isset($keywordInfo)) {
                $this->addStatusMessage(_('Info missing').'   '.$fieldType.' '.$fieldName,
                    'warning');
                continue;
            }

            if (!isset($keywordInfo['title'])) {
                $this->addStatusMessage(_('column without caption').' '.$fieldName,
                    'warning');
            }

            if (!strlen($keywordInfo['title'])) {
                continue;
            }

            if (isset($keywordInfo['required']) && $keywordInfo['required']) {
                $this->reqFields[$fieldName] = $fieldType;
                $required                    = true;
            } else {
                $required = false;
            }

            $value = $this->objectEdited->getDataValue($fieldName);

//            if (is_null($value)) {
//                if (!\Ease\Shared::webPage()->isPosted()) {
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
                    if (\Ease\Shared::webPage()->isPosted() && is_null($value) && $required) {
                        $this->addStatusMessage(_('Requied value is not set').' '.$keywordInfo['title'],
                            'warning');
                    }
                }
            } else {
                if (\Ease\Shared::webPage()->isPosted() && is_null($value) && $required) {
                    $this->addStatusMessage(_('Requied value is not set').' '.$keywordInfo['title'],
                        'warning');
                }
            }



            switch ($keywordInfo['severity']) {
                case 'advanced':
                    if (!isset($advancedTab)) {
                        $advancedTab = $tabs->addTab(_('Advanced'));
                    }
                    $mainFieldBlock = $advancedTab->addItem(new \Ease\Html\Div(
                        null,
                        ['class' => 'fieldblock', 'id' => $fieldName.'-block']));
                    break;
                case 'optional':
                    if (!isset($optionalTab)) {
                        $optionalTab = $tabs->addTab(_('Optional'));
                    }
                    $mainFieldBlock = $optionalTab->addItem(new \Ease\Html\Div(null,
                        ['class' => 'fieldblock', 'id' => $fieldName.'-block']));
                    break;
                default :
                    $mainFieldBlock = $this->addItem(new \Ease\Html\Div(
                        null,
                        ['class' => 'fieldblock', 'id' => $fieldName.'-block']));
                    break;
            }


            $fieldLabel = $mainFieldBlock->addItem(new \Ease\Html\Div('<a>'.$fieldName.'</a>&nbsp;',
                ['class' => 'FieldLabel', 'onClick' => "$('#".$fieldName."-controls').toggle('slow');"]));
            /**

              if (!$required || !(int) $this->objectEdited->getDataValue('register')) {
              $fieldLabel->addItem(new \Ease\Html\ATag('#', \Ease\TWB\Part::GlyphIcon('remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
              $fieldLabel->setTagClass('FieldLabel');
              } else {
              $mainFieldBlock->setTagClass('fieldblock req');
              }
             */
            $fieldBlock = $mainFieldBlock->addItem(new \Ease\Html\Div(null,
                ['id' => $fieldName.'-controls']));


            if (!$this->objectEdited->isOwnedBy() && !\Ease\Shared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
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
                if (!is_null($tempValue) && ($fieldName != $this->objectEdited->nameColumn)
                    && !$required) { //Skrýt nedůležité položky
// \Ease\Shared::webPage()->addJavaScript("$('#" . $fieldName . "-controls').hide();", null, true);
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
        \Ease\Shared::webPage()->includeJavaScript('js/jquery.validate.js');
        if (isset($this->reqFields) && count($this->reqFields)) {

            $Rules = ' $("#'.$this->parentObject->getTagProperty('name').'").validate({
        invalidHandler: function (e, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                    ? \''._('One mandatory field left blank').'\'
                    : \''._('Requied ').' + errors + '._(' fields').'\';
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
            $Rules.= implode(',', $fRules)."\n}});\n";

//$Rules = ' $("#' . $this->parentObject->getTagProperty('name') . '").validate();';

            \Ease\Shared::webPage()->addJavaScript($Rules, NULL, true);
        }
    }

    /**
     * Editor běžného uživatele
     */
    public function lightEditor()
    {

        if (!(int) $this->objectEdited->getDataValue('generate')) {
            $this->addStatusMessage(_('this record is not generated to icinga config file'));
        }
        if ($this->objectEdited->publicRecords) {
            if ((int) $this->objectEdited->getDataValue('public')) {
                $this->addStatusMessage(_('this record is public'));
            }
        }
        $this->addItem('<div class="error" style=""><span></span><br clear="all"></div>');

        $use = $this->objectEdited->getDataValue('use');
        if (!is_null($use)) {
            $template = clone $this->objectEdited;
            $template->loadFromSQL((int) $use);
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
                $required                    = true;
            } else {
                if (!isset($keywordInfo['mandatory']) || !$keywordInfo['mandatory']) {
                    $required = false;
                    continue;
                }
            }

            $value = $this->objectEdited->getDataValue($fieldName);

            if (is_null($value)) {
                if (!\Ease\Shared::webPage()->isPosted()) {
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
                    if (\Ease\Shared::webPage()->isPosted() && is_null($value) && $required) {
                        $this->addStatusMessage(_('Requied value is not set').' '.$keywordInfo['title'],
                            'warning');
                    }
                }
            } else {
                if (\Ease\Shared::webPage()->isPosted() && is_null($value) && $required) {
                    $this->addStatusMessage(_('Requied value is not set').' '.$keywordInfo['title'],
                        'warning');
                }
            }

            $mainFieldBlock = $this->addItem(new \Ease\Html\Div(
                null, ['class' => 'fieldblock', 'id' => $fieldName.'-block']));
            /*

              $fieldLabel = $mainFieldBlock->addItem(new \Ease\Html\Div( '<a name="' . $fieldName . '">' . $fieldName . '</a>&nbsp;', array('class' => 'FieldLabel mandatory', 'onClick' => "$('#" . $fieldName . "-controls').toggle('slow');")));

              if (!$required || !(int) $this->objectEdited->getDataValue('register')) {
              $fieldLabel->addItem(new \Ease\Html\ATag('#', \Ease\TWB\Part::GlyphIcon('icon-remove'), array('onClick' => '$(\'#' . $fieldName . '-block\').empty().html(\'<input type=hidden name=' . $fieldName . ' value=NULL><div class=FieldLabel>' . $fieldName . '</div>\'); return false;')));
              $fieldLabel->setTagClass('FieldLabel');
              } else {
              $mainFieldBlock->setTagClass('fieldblock req');
              }
             */
            $fieldBlock     = $mainFieldBlock->addItem(new \Ease\Html\Div(null,
                ['id' => $fieldName.'-controls']));

            if (!$this->objectEdited->isOwnedBy() && !\Ease\Shared::user()->getSettingValue('admin')) { //Editovat může pouze vlastník
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
                if (!is_null($tempValue) && ($fieldName != $this->objectEdited->nameColumn)
                    && !$required) { //Skrýt nedůležité položky
                    \Ease\Shared::webPage()->addJavaScript("$('#".$fieldName."-controls').hide();",
                        null, true);
                }
            }
            $this->insertWidget($fieldBlock, $fieldName, $value);
        }
    }

    /**
     * Zaškrtávací políčko pro NULL
     *
     * @param string $name
     * @return \\Ease\Html\CheckboxTag
     */
    public function optionEnabler($name)
    {
        return new \Ease\Html\CheckboxTag(null, false, 1,
            ['id' => 'useTpl'.$name]);
    }

    /**
     * Vraci kod pro ukladani policka formulare po editaci
     *
     * @param string $fieldName
     * @return string javascript
     */
    public function onChangeCode($fieldName)
    {
        $chCode = '';
        $id     = $this->objectEdited->getMyKey();
        if (!is_null($id)) {
            $chCode = 'saveColumnData(\''.str_replace('\\', '-',
                    get_class($this->objectEdited)).'\', \''.$id.'\', \''.$fieldName.'\')';
        }
        return $chCode;
    }

}
