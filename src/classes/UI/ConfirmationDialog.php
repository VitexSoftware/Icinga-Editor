<?php
namespace Icinga\Editor\UI;

/**
 * Description of ABConfirmationDialog
 *
 * @author vitex
 */
class ConfirmationDialog extends \Ease\Html\Div
{

    /**
     * Potvrzovací dialog Twitter Bootstrapu
     *
     * @param string $name
     * @param string $url
     * @param string $title
     * @param mixed  $content
     * @param array  $properties
     */
    function __construct($name = null, $url, $title, $content = null, $properties = null)
    {
        if (isset($properties['class'])) {
            $properties['class'] .= ' modal fade';
        } else {
            $properties['class'] = 'modal fade';
        }
        parent::__construct($name, null, $properties);

        $modalDialog = $this->addItem(new \Ease\Html\Div( null, array('class' => 'modal-dialog')));
        $modalContent = $modalDialog->addItem(new \Ease\Html\Div( null, array('class' => 'modal-content')));


        $modalContent->addItem(new \Ease\Html\Div( array(
          new \Ease\Html\ButtonTag('<span aria-hidden="true">&times;</span>', array('class' => 'close', 'data-dismiss' => 'modal', 'aria-label' => _('Zavřít'))),
          new \Ease\Html\H4Tag($title, array('class' => 'modal-title'))
            ), array('class' => 'modal-header')));
        $modalContent->addItem(new \Ease\Html\Div( $content, array('class' => 'modal-body')));
        $modalContent->addItem(new \Ease\Html\Div( array(
          new \Ease\Html\ButtonTag(_('Ne'), array('class' => "btn btn-default", 'data-dismiss' => "modal")),
          new \Ease\TWB\LinkButton($url, _('Ano'), 'danger'),
            ), array('class' => 'modal-footer')));
    }

    function finalize()
    {
        $this->addJavaScript("
$('#" . $this->getTagID() . "').on('show', function() {
    var id = $(this).data('id'),
        removeBtn = $(this).find('.danger');
})

$('#trigger" . $this->getTagID() . "').on('click', function(e) {
    var id = $(this).data('id');
    $('#" . $this->getTagID() . "').data('id', id).modal('show');
    e.preventDefault();
});

$('#" . $this->getTagID() . "btnYes').click(function() {
    // handle deletion here
  	var id = $('#" . $this->getTagID() . "').data('id');
  	//$('[data-id='+id+']').remove();
  	$('#" . $this->getTagID() . "').modal('hide');
});

            ", null, true);
    }

}
