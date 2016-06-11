<?php

namespace Icinga\Editor\UI;

/**
 * Description of IEInfoBox
 *
 * @author vitex
 */
class InfoBox extends \Ease\TWB\Panel
{

    /**
     * Infopanel objektu
     *
     * @param IEcfg $subject
     */
    public function __construct($subject)
    {
        parent::__construct(_('informace'), 'info', $subject->getInfoBlock());
    }
}