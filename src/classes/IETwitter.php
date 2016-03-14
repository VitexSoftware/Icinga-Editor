<?php

require_once 'IEUser.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class EaseOAuth extends EaseAtom
{
    public static $RequestTokenURL = '';
    public static $AuthorizeURL = '';
    public static $AccessTokenURL = '';

    public static $OAuthVersion = '1.0';
    public static $OAuthSignatureMethod = 'HMAC-SHA1';

    public static $ConsumerKey = '';
    public static $ConsumerSecret = '';

    public static $OAuthCallback = '';

    public $Curl = NULL;

    public function __construct()
    {
        $this->InitCurl();
    }

    public function InitCurl()
    {
        $this->Curl = curl_init();
        curl_setopt($this->Curl,CURLOPT_POST , TRUE);
//        curl_setopt($this->Curl,CURLOPT_POST , TRUE);
//        curl_setopt($this->Curl,CURLOPT_POST , TRUE);
    }

    public function AuthRequest()
    {
        curl_setopt($this->Curl, CURLOPT_URL, self::$RequestTokenURL);
        $AuthHeader = 'OAuth oauth_nonce="K7ny27JTpKVsTgdyLdDfmQQWVLERj2zAK5BslRsqyw", oauth_callback="'.self::$OAuthCallback.'", oauth_signature_method="'.self::$OAuthSignatureMethod.'", oauth_timestamp="'.time().'", oauth_consumer_key="'.self::$ConsumerKey.'", oauth_signature="'.$this->OAuthSignature().'", oauth_version="'.self::$OauthVersion.'"';
    }

    public function OAuthSignature()
    {
    }

    /**
     * Je již uživatel přihlášen k twitteru ?
     * @return boolean
     */
    public static function IsAuthenticated()
    {
        return isset ($_SESSION['access_token']['user_id']);
    }

    /**
     *
     * @param  type              $Base
     * @return \Ease\TWB\LinkButton
     */
    public static function AuthButton($Base = '')
    {
        if (!self::IsAuthenticated()) {
            return new \Ease\TWB\LinkButton($Base.'?authenticate=1', _('přihlásit přez Twitter'));
        } else {
            return new \Ease\TWB\LinkButton($Base.'?wipe=1', _('odhlasit twitter'));
        }
    }

    public function __destruct()
    {
        curl_close($this->Curl);
    }

}

/**
 * Description of LQTwitter
 *
 * @author vitex
 */
class IETwitter extends EaseOAuth
{
    public static $RequestTokenURL = 'https://api.twitter.com/oauth/request_token';
    public static $AuthorizeURL = 'https://api.twitter.com/oauth/authorize';
    public static $AccessTokenURL = 'https://api.twitter.com/oauth/access_token';

    public static $AccessToken = '859815788-WT6brHrctTOBF0D0k5nu9v3onvciVcH4O1ZImCNi';
    public static $AccessTokenSecret = '859815788-WT6brHrctTOBF0D0k5nu9v3onvciVcH4O1ZImCNi';

    public static $ConsumerKey = 'Lrqd33jGa1NWbBi0fbN0Q';
    public static $ConsumerSecret = 'k0tsQ0P5suWd0u6mXrAp0qhXJr4vEnTjWbWUBzuBs';
}
