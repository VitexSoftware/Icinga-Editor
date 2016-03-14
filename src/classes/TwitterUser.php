<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Icinga\Editor;

/**
 * Description of TwitterUser
 *
 * @author vitex
 */
class IETwitterUser extends IEUser
{
    /**
     * data z Twitteru
     * @var stdClass
     */
    public $twitter = null;

    /**
     * Uživatel autentifikující se vůči twitteru
     *
     * @param arrat  $twitter     id uživatele
     * @param string $TwitterName jméno uživatele
     */
    public function __construct($twitter = null)
    {
        parent::__construct();
        if (!is_null($twitter)) {
            $this->twitter = $twitter;
            $this->setmyKeyColumn('twitter_id');
            $this->setMyKey($twitter->id);
            if (!$this->loadFromMySQL()) {
                $this->restoreObjectIdentity();
                $this->setDataValue($this->LoginColumn, $twitter->screen_name);
                $this->setSettingValue('icon', $twitter->profile_image_url);
                if ($this->saveToMySQL()) {
                    $this->addStatusMessage(_(sprintf('Vytvořeno spojení s Twitterem',
                                $twitter->screen_name), 'success'));
                    $this->loginSuccess();
                }
            } else {
                $this->restoreObjectIdentity();
            }
            $this->setObjectName();
        }
    }
}