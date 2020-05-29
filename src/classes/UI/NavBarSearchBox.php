<?php

namespace Icinga\Editor\UI;

use Ease\Html\ButtonTag;
use Ease\Html\DivTag;
use Ease\Html\InputTextTag;
use Ease\Html\Span;
use Ease\Shared;
use Ease\TWB\Form;
use Ease\WebPage;

/**
 * Description of NavBarSearchBox
 *
 * @author vitex
 */
class NavBarSearchBox extends Form {

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
    function __construct($formName, $formAction = null, $formMethod = 'post',
            $formContents = null, $tagProperties = null) {
        parent::__construct(['name' => $formName, 'action' => $formAction, 'method' => $formMethod], $formContents,
                $tagProperties);
        $term = WebPage::getRequestValue('search', 'string');

        $this->setTagProperties(['class' => 'navbar-form', 'role' => 'search']);
        $group = $this->addItem(
                new DivTag(new InputTextTag(
                                'search', $term,
                                [
                            'class' => 'form-control pull-right typeahead',
                            'style' => 'width: 150px; margin-right: 35px, border: 1px solid black; background-color: #e5e5e5;',
                            'placeholder' => _('Search'),
                                ]), ['class' => 'input-group'])
        );
        $buttons = $group->addItem(new Span(null,
                        ['class' => 'input-group-btn']));
        $buttons->addItem(new ButtonTag(new Span(
                                new Span(_('Close'), ['class' => 'sr-only']),
                                ['class' => 'glyphicon glyphicon-remove']),
                        ['type' => 'reset', 'class' => 'btn btn-default']));
        $buttons->addItem(new ButtonTag(new Span(
                                new Span(_('Search'), ['class' => 'sr-only']),
                                ['class' => 'glyphicon glyphicon-search']),
                        ['type' => 'submit', 'class' => 'btn btn-default']));
    }

    function finalize() {
        $this->includeJavaScript('js/handlebars.js');
        $this->includeJavaScript('js/typeahead.bundle.js');
        $this->addCss('

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
        $this->addJavaScript('


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
