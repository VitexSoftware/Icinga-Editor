<?php

namespace Icinga\Editor;

/**
 * Přihlašovací stránka
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2009,2011
 * @package IcingaEditor
 */
require_once 'includes/IEInit.php';

require 'classes/tmhOAuth.php';
require 'classes/tmhUtilities.php';

$tmhOAuth = new tmhOAuth([
    'consumer_key' => IETwitter::$ConsumerKey,
    'consumer_secret' => IETwitter::$ConsumerSecret,
    ]);

$here = tmhUtilities::php_self();

function outputError($tmhOAuth)
{
    echo 'Error: '.$tmhOAuth->response['response'].PHP_EOL;
    tmhUtilities::pr($tmhOAuth);
}
// reset request?
if (isset($_REQUEST['wipe'])) {
    unset($_SESSION['access_token']);
    $oPage->redirect('logout.php');

// already got some credentials stored?
} elseif (isset($_SESSION['access_token'])) {
    $tmhOAuth->config['user_token']  = $_SESSION['access_token']['oauth_token'];
    $tmhOAuth->config['user_secret'] = $_SESSION['access_token']['oauth_token_secret'];

    $code = $tmhOAuth->request('GET',
        $tmhOAuth->url('1/account/verify_credentials'));
    if ($code == 200) {
        $resp = json_decode($tmhOAuth->response['response']);

        $CurrentUser = \Ease\Shared::user();

        if ($CurrentUser->getUserID()) {
            $CurrentUser->addStatusMessage(_('Účet twitteru, bayl přiřazen'),
                'success');
            $CurrentUser->setDataValue('twitter_id', $resp->id);
            if (!$CurrentUser->getSettingValue('icon')) {
                $CurrentUser->setSettingValue('icon', $resp->profile_image_url);
                $CurrentUser->UserLogin = $resp->screen_name;
            }

            $CurrentUser->save();
        } else {
            \Ease\Shared::user(new IETwitterUser($resp));
        }
        \Ease\Shared::user()->loginSuccess();
        \Ease\Shared::webPage()->redirect('index.php');
        exit();
    } else {
        outputError($tmhOAuth);
    }
// we're being called back by Twitter
} elseif (isset($_REQUEST['oauth_verifier'])) {
    $tmhOAuth->config['user_token']  = $_SESSION['oauth']['oauth_token'];
    $tmhOAuth->config['user_secret'] = $_SESSION['oauth']['oauth_token_secret'];

    $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''),
        [
        'oauth_verifier' => $_REQUEST['oauth_verifier']
    ]);

    if ($code == 200) {
        $_SESSION['access_token'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
        unset($_SESSION['oauth']);
        header("Location: {$here}");
        exit;
    } else {
        outputError($tmhOAuth);
    }
// start the OAuth dance
} elseif (isset($_REQUEST['authenticate']) || isset($_REQUEST['authorize'])) {
    $callback = isset($_REQUEST['oob']) ? 'oob' : $here;

    $params = [
        'oauth_callback' => $callback
    ];

    if (isset($_REQUEST['force_write'])) :
        $params['x_auth_access_type'] = 'write';
    elseif (isset($_REQUEST['force_read'])) :
        $params['x_auth_access_type'] = 'read';
    endif;

    $code = $tmhOAuth->request('POST',
        $tmhOAuth->url('oauth/request_token', ''), $params);

    if ($code == 200) {
        $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
        $method            = isset($_REQUEST['authenticate']) ? 'authenticate' : 'authorize';
        $force             = isset($_REQUEST['force']) ? '&force_login=1' : '';
        $authurl           = $tmhOAuth->url("oauth/{$method}", '')."?oauth_token={$_SESSION['oauth']['oauth_token']}{$force}";
        $oPage->redirect($authurl);
        exit;
    } else {
        outputError($tmhOAuth);
    }
}
$oPage->Redirect('index.php');

/*
  <ul>
  <li><a href="?authenticate=1">Sign in with Twitter</a></li>
  <li><a href="?authenticate=1&amp;force=1">Sign in with Twitter (force login)</a></li>
  <li><a href="?authorize=1">Authorize Application (with callback)</a></li>
  <li><a href="?authorize=1&amp;oob=1">Authorize Application (oob - pincode flow)</a></li>
  <li><a href="?authorize=1&amp;force_read=1">Authorize Application (with callback) (force read-only permissions)</a></li>
  <li><a href="?authorize=1&amp;force_write=1">Authorize Application (with callback) (force read-write permissions)</a></li>
  <li><a href="?authorize=1&amp;force=1">Authorize Application (with callback) (force login)</a></li>
  <li><a href="?wipe=1">Start Over and delete stored tokens</a></li>
  </ul>

 */
