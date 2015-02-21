<?php

/**
 * Description of IENavBarSearchBox
 *
 * @author vitex
 */
class IENavBarSearchBox extends EaseTWBForm
{

    /**
     * Formulář Bootstrapu
     *
     * @param string $formName      jméno formuláře
     * @param string $formAction    cíl formulář např login.php
     * @param string $formMethod    metoda odesílání POST|GET
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                              array('enctype' => 'multipart/form-data')
     */
    function __construct($formName, $formAction = null, $formMethod = 'post', $formContents = null, $tagProperties = null)
    {
        parent::__construct($formName, $formAction, $formMethod, $formContents, $tagProperties);
        $term = EaseShared::webPage()->getRequestValue('search', 'string');

        $this->setTagProperties(array('class' => 'navbar-form', 'role' => 'search'));
        $group = $this->addItem(
            new EaseHtmlDivTag(null, new EaseHtmlInputTextTag(
            'search', $term, array(
          'class' => 'form-control pull-right typeahead',
          'style' => 'width: 150px; margin-right: 35px, border: 1px solid black; background-color: #e5e5e5;',
          'placeholder' => _('Hledání'),
            )), array('class' => 'input-group'))
        );
        $buttons = $group->addItem(new EaseHtmlSpanTag(null, null, array('class' => 'input-group-btn')));
        $buttons->addItem(new EaseHtmlButtonTag(new EaseHtmlSpanTag(null, new EaseHtmlSpanTag(NULL, _('Zavřít'), array('class' => 'sr-only')), array('class' => 'glyphicon glyphicon-remove')), array('type' => 'reset', 'class' => 'btn btn-default')));
        $buttons->addItem(new EaseHtmlButtonTag(new EaseHtmlSpanTag(null, new EaseHtmlSpanTag(NULL, _('Hledat'), array('class' => 'sr-only')), array('class' => 'glyphicon glyphicon-search')), array('type' => 'submit', 'class' => 'btn btn-default')));
    }

    function finalize()
    {

        EaseShared::webPage()->includeJavaScript('js/handlebars.js');
        EaseShared::webPage()->includeJavaScript('js/typeahead.bundle.js');
        EaseShared::webPage()->addCss('

.tt-hint {
}

.tt-input {
}

.tt-hint {
    color: #999
}

.tt-dropdown-menu {
    width: 422px;
    margin-top: 12px;
    padding: 8px 0;
    background-color: #fff;
    border: 1px solid #ccc;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    box-shadow: 0 5px 10px rgba(0,0,0,.2);
    overflow-y: auto;
    max-height: 500px;
}

.tt-suggestion {
    padding: 3px 20px;
}

.tt-suggestion.tt-cursor {
    color: #fff;
    background-color: #0097cf;

}

.tt-suggestion.tt-cursor a {
    color: black;
}

.tt-suggestion p {
    margin: 0;
}
');
        EaseShared::webPage()->addJavaScript('


var bestPictures = new Bloodhound({
    limit: 1000,
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace(\'value\'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: \'searcher.php?q=%QUERY\'
});

bestPictures.initialize();

$(\'input[name="search"]\').css("top","2px").typeahead(null, {
    name: \'best-pictures\',
    displayKey: \'name\',
    source: bestPictures.ttAdapter(),
     templates: {
        suggestion: Handlebars.compile(\'<p><small>{{type}}</small><br><a href="{{url}}"><strong>{{name}}</strong> – {{what}}</a></p>\')
}
});

            ', null, true);
    }

}
