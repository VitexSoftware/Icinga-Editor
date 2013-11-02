<?php

require_once 'IEUser.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class EaseOAuth extends EaseAtom {
    static public $RequestTokenURL = '';
    static public $AuthorizeURL = '';
    static public $AccessTokenURL = '';
    
    static public $OAuthVersion = '1.0';
    static public $OAuthSignatureMethod = 'HMAC-SHA1';

    static public $ConsumerKey = '';
    static public $ConsumerSecret = '';
    
    static public $OAuthCallback = '';


    public $Curl = NULL;

    function __construct() {
        $this->InitCurl();
    }

    function InitCurl(){
        $this->Curl = curl_init();
        curl_setopt($this->Curl,CURLOPT_POST , TRUE);
//        curl_setopt($this->Curl,CURLOPT_POST , TRUE);
//        curl_setopt($this->Curl,CURLOPT_POST , TRUE);
    }
    
    function AuthRequest(){
        curl_setopt($this->Curl, CURLOPT_URL, self::$RequestTokenURL);        
        $AuthHeader = 'OAuth oauth_nonce="K7ny27JTpKVsTgdyLdDfmQQWVLERj2zAK5BslRsqyw", oauth_callback="'.self::$OAuthCallback.'", oauth_signature_method="'.self::$OAuthSignatureMethod.'", oauth_timestamp="'.time().'", oauth_consumer_key="'.self::$ConsumerKey.'", oauth_signature="'.$this->OAuthSignature().'", oauth_version="'.self::$OauthVersion.'"';
    }

    function OAuthSignature(){
        
    }

    /**
     * Je již uživatel přihlášen k twitteru ?
     * @return boolean 
     */
    static function IsAuthenticated(){
        return isset ($_SESSION['access_token']['user_id']);
    }
    
    /**
     *
     * @param type $Base
     * @return EaseTWBLinkButton 
     */
    static function AuthButton($Base = ''){
        if(!self::IsAuthenticated()){
            return new EaseTWBLinkButton($Base.'?authenticate=1', _('přihlásit přez Twitter'));
        } else {
            return new EaseTWBLinkButton($Base.'?wipe=1', _('odhlasit twitter'));
        }
    }

    function __destruct() {
        curl_close($this->Curl);
    }
    
}

/**
 * Description of LQTwitter
 *
 * @author vitex
 */
class IETwitter extends EaseOAuth {
    static public $RequestTokenURL = 'https://api.twitter.com/oauth/request_token';
    static public $AuthorizeURL = 'https://api.twitter.com/oauth/authorize';
    static public $AccessTokenURL = 'https://api.twitter.com/oauth/access_token';
    
    static public $AccessToken = '859815788-WT6brHrctTOBF0D0k5nu9v3onvciVcH4O1ZImCNi';
    static public $AccessTokenSecret = '859815788-WT6brHrctTOBF0D0k5nu9v3onvciVcH4O1ZImCNi';

    static public $ConsumerKey = 'Lrqd33jGa1NWbBi0fbN0Q';
    static public $ConsumerSecret = 'k0tsQ0P5suWd0u6mXrAp0qhXJr4vEnTjWbWUBzuBs';
}

?>
