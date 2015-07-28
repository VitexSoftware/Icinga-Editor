<?php

/**
 * Description of IEInfoBox
 *
 * @author vitex
 */
class IEInfoBox extends EaseTWBPanel
{

    /**
     * Infopanel objektu
     *
     * @param IECfg $subject
     */
    public function __construct($subject)
    {
        parent::__construct(_('informace'), 'info', $subject->getInfoBlock());
    }

}
