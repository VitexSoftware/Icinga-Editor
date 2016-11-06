<?php
/**
 * ConfigFile textarea
 *
 * @todo dodělat
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Icinga\Editor\UI;

/**
 * Description of CfgTextarea
 *
 * @author vitex
 */
class CfgTextarea extends \Ease\TWB\Textarea
{
    public function finalize()
    {
        $this->addCSS('div.numberedtextarea-wrapper { position: relative; }

div.numberedtextarea-wrapper textarea {
  display: block;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

div.numberedtextarea-line-numbers {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 50px;
  border-right: 1px solid rgba(0, 0, 0, 0.15);
  color: rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

div.numberedtextarea-number {
  padding-right: 6px;
  text-align: right;
}


');
        $this->addJavaScript('function auto_grow(element) {
    element.style.height = "5px";
    element.style.height = (element.scrollHeight)+"px";
}
', null, false);

        $this->addJavaScript('function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  } else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd(\'character\', selectionEnd);
    range.moveStart(\'character\', selectionStart);
    range.select();
  }
}

function setCaretToPos(input, pos) {
  setSelectionRange(input, pos, pos);
}

$("textarea").click(function() {
  setCaretToPos($("textarea")[0], '.intval($this->getTagProperty('data-lineno')).')
});
');

        $this->includeJavaScript('js/jquery.numberedtextarea.js');
        $this->addJavaScript('$(\'textarea\').numberedtextarea();');
        $this->setTagProperties(['onkeyup' => 'auto_grow(this)']);
        parent::finalize();
    }
}
