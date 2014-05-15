<?php

/**
 * The Chat action for com_gae_chat
 * 
 * Used to determine if the person is eligible for chat and retrives a token
 * Also checks to see if we are already have a valid token and reuses it
 * 
 * @package com_gae_chat
 * @license none
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright Smart Industries, LLC
 * @link http://smart108.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * We should check to see if we have a session user
 * 
 * If we have a session user
 *      - If we have already saved their chat info in the session
 *          - If token has expired OR forcing a new token
 *              - Request new token from App Engine
 *          - Token has not expired
 *              - Return back their info
 *      - We don't have their chat info in the session
 *          - Get user info from $_SESSION['user']
 *          - Request new token from App Engine
 *          - Save channels info to session
 *          - Return back their info
 * 
 * We don't have a session user
 *      - Check if we have a cookie from the user
 *      - We have a cookie from the user
 *          - Check if their token is expired
 *          - If Token is expired
 *              - Request a new token from App Engine
 *              - Resave their session info
 *              - Return back their info
 *          - Token is not expired
 *              - Return back their info
 *      - We don't have a cookie from the user
 *          - Make sure this person is requesting a token from a specified domain
 *          - Request a new token from App Engine
 *          - Save channels info to the session
 *          - Save a cookie with either the token (Token is more secretive)
 * 
 */

$pines->page->override = true;
header('Content-Type: application/json');

if ($pines->config->com_gae_chat->chat_disabled) {
    $pines->page->override_doc(json_encode(array('status' => false)));
    return;
}

$have_user = isset($_SESSION['user']);

if ($have_user) {
    
    // We have a session user
    if (isset($_SESSION['gae_channels'])) {
        // This means we have a revisiting user
        $now = new Datetime();
        $expires = $_SESSION['channel_token_expire_date'];
        
        // Need to check if the token has expired or the user requested us to force a new token
        if (($expires < $now) || ($_REQUEST['force_new_token'] == 'true')) {
            $username = $_SESSION['channel_username'];
            $guid = $_SESSION['channel_guid'];
            $email = $_SESSION['channel_email'];
            $employee = $_SESSION['channel_employee'];
            $token = $_SESSION['channel_token'];
            $channel_id = $_SESSION['channel_id'];
            
            $response = $pines->com_gae_chat->renew_chat_token($channel_id);
            
            $params = array(
                'username'  => $response->username,
                'guid'      => $guid,
                'channel_id'    => $response->channel_id,
                'channel_token' => $response->token,
                'existing'  => 'expired',
                'expires'   => new Datetime($response->expires),
                'gae_channels'  => 'set'
            );
            
            $pines->session('write');
            $_SESSION['channel_token'] = $response->token;
            $_SESSION['channel_token_expire_date'] = $response->expires;
            $_SESSION['channel_username'] = $response->username;
            $_SESSION['channel_guid'] = $guid;
            $_SESSION['channel_employee'] = $employee;
            $_SESSION['channel_id'] = $response->channel_id;
            $pines->session('close');
            
            
        } else {
            $params = array(
                'username'  => $_SESSION['channel_username'],
                'guid'      => $_SESSION['channel_guid'],
                'channel_id'    => $_SESSION['channel_id'],
                'channel_token' => $_SESSION['channel_token'],
                'existing'  => 'true',
                'expires'   => $expires
            );
        }
        
    } else {
        // A logged in user who doesn't have their channels info saved
        // Not sure if we have an employee or not
        $user = $_SESSION['user'];
        $employee = $_SESSION['user']->employee;
        
        // We are checking to seee if the person is and employee and has priliveges or is not an employee
        if (($employee && gatekeeper('com_gae_chat/employeechat')) || !$employee) {
            // Might want to remove any non-digits from uniqid. Don't really need it to be the most unique thing
            $username = $user->name_first;
            $guid = $user->guid;
            $email = $user->email;
            $response = $pines->com_gae_chat->get_channel_token($guid, $username, $email, $employee);

            $date = new DateTime($response->expires);

            $params = array(
                'username' => $response->username, 
                'guid' => $guid,
                'channel_id' => $response->channel_id,
                'channel_token' => $response->token,
                'existing'  => false,
                'expires'   => $date
            );

            $pines->session('write');
            $_SESSION['gae_channels'] = true;
            $_SESSION['channel_username'] = $response->username;
            $_SESSION['channel_guid'] = $guid;
            $_SESSION['channel_email'] = $email;
            $_SESSION['channel_employee'] = $employee;
            $_SESSION['channel_token_expire_date'] = $date;
            $_SESSION['channel_id'] = $response->channel_id;
            $_SESSION['channel_token'] = $response->token;
            $pines->session('close');
            
        } else {
            $params = array('status' => false);
        }
        
    }
    
    $pines->page->override_doc(json_encode($params));
    
} else {
    // We don't have a session User, check if we have a token cookie
    if (isset($_COOKIE['gae_channels_id'])) {
        // We have a previous cookie
        // Let's renew using that channel_id
        $channel_id = $_COOKIE['gae_channels_id'];
        $response = $pines->com_gae_chat->renew_chat_token($channel_id);
        $expires = new Datetime($response->expires);
        $token = $response->token;
        
        $params = array(
                'username' => $response->username, 
                'guid' => 0,
                'channel_id' => $response->channel_id,
                'channel_token' => $token,
                'existing'  => true,
                'expires'   => $expires
            );
        
        // Save to their session and save a cookie
        $pines->session('write');
        $_SESSION['gae_channels'] = true;
        $_SESSION['channel_username'] = $response->username;
        $_SESSION['channel_guid'] = 0;
        $_SESSION['channel_email'] = '';
        $_SESSION['channel_employee'] = false;
        $_SESSION['channel_token_expire_date'] = $expires;
        $_SESSION['channel_id'] = $response->channel_id;
        $_SESSION['channel_token'] = $token;
        $pines->session('close');
        
        setcookie('gae_channels_id', $channel_id, (time() + (60 * 60 * 24 * 365)), '/');
        
    } else {
        // We don't have a returning guest
        // We need to establish their channel for the first time
        $username = 'Guest '. substr(mt_rand(), 0, 5);
        $guid = 0;
        $email = '';
        $response = $pines->com_gae_chat->get_channel_token(0, $username, '', false);
        
        $expires = new DateTime($response->expires);
        $token = $response->token;

        $params = array(
            'username' => $username, 
            'guid' => $guid,
            'channel_id' => $response->channel_id,
            'channel_token' => $token,
            'existing'  => false,
            'expires'   => $expires,
        );
        
        $pines->session('write');
        $_SESSION['gae_channels'] = true;
        $_SESSION['channel_username'] = $username;
        $_SESSION['channel_guid'] = $guid;
        $_SESSION['channel_email'] = $email;
        $_SESSION['channel_employee'] = $employee;
        $_SESSION['channel_token_expire_date'] = $date;
        $_SESSION['channel_id'] = $response->channel_id;
        $_SESSION['channel_token'] = $token;
        $pines->session('close');
        
        
        setcookie('gae_channels_id', $response->channel_id, (time() + (60 * 60 * 24 * 365)), '/');
    }
    
    $pines->page->override_doc(json_encode($params));
}
?>