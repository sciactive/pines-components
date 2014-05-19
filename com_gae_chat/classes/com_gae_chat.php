<?php
/**
 * gae chat's component class
 *
 * @package Components\gae_chat
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_gae_chat's main class.
 *
 * A JavaScript Library to handle sending and receiving messages from
 * 
 * Also includes an HTML file for the main chat client and chat windows
 *
 * @package Components\gae_chat
 */
class com_gae_chat extends component {
    
    private $js_loaded = false;
    
    /**
     * Loads the Customer JS Files for GAE Chat
     *
     * This will place the required scripts into the document's bottom section.
     * 
     */
    function load_customer_js() {
        global $pines;
        if (!$this->js_loaded) {
                if ($pines->config->compress_cssjs) {
                        $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
                        $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                        // Add Css and JS
                        $js[] =  $file_root.'components/com_gae_chat/includes/'.($pines->config->debug_mode ? 'customer_chat.js' : 'customer_chat.min.js');
                        $js[] =  $pines->config->com_gae_chat->channels_js_url;
                        $pines->config->loadcompressedjs = $js;
                        
                        $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
                        $css[] = $file_root.'components/com_gae_chat/includes/customer_chat.css';
                        $pines->config->loadcompressedcss = $css;
                } else {
                        $module = new module('com_gae_chat', 'chat_customer_js', 'bottom');
                        $module->render();
                }
                $this->js_loaded = true;
        }
    }
    
    /**
     * Loads the Employee JS Files for GAE Chat
     */
    function load_employee_js() {
        global $pines;
        if (!$this->js_loaded) {
                if ($pines->config->compress_cssjs) {
                        $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
                        $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                        // Add Css, and JS
                        $js[] =  $file_root.'components/com_gae_chat/includes/'.($pines->config->debug_mode ? 'employee_chat.js' : 'employee_chat.min.js');
                        $js[] =  $pines->config->com_gae_chat->channels_js_url;
                        $pines->config->loadcompressedjs = $js;
                        
                        $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
                        $css[] = $file_root.'components/com_gae_chat/includes/employee_chat.css';
                        $pines->config->loadcompressedcss = $css;
                } else {
                        $module = new module('com_gae_chat', 'chat_js', 'bottom');
                        $module->render();
                }
                $this->js_loaded = true;
        }
    }
    
    
    /*
     *  Load the gae_chat HTML
     */
    function load_html() {
        $module = new module('com_gae_chat', 'chat_html', 'bottom');
        return $module;
    }
    
    
    /**
     * Retrieves an App Engine Channels Token to use to connect to Channels
     * 
     * @param int $guid The guid of the user to get a token for
     * @param string $username The username of the user to get a token for
     * @param bool $employee Whether the user is an employee or not
     * @param bool $distinguish Whether to distingish a guest as an employee
     * @return array JSON Response from App Engine
     */
    function get_channel_token($guid = 0, $username = '', $email = '', $employee = false, $distinguish = false) {
        global $pines;
        pines_log('Value of distinguish: '.json_encode($distinguish), 'notice');
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $pines->config->com_gae_chat->token_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'password'  => $pines->config->com_gae_chat->password,
                'guid'      => $guid,
                'employee'  => $employee,
                'username'  => $username,
                'email'     => $email,
                'distinguished'   => $distinguish
            )
        ));
        
        // Expect the response to have a status, token, channel_id variables
        $response = curl_exec($ch);
        $message = json_decode($response);
        
        return $message;
    }
    
    /**
     * Renews a token for a given channel id
     * 
     * @param string $channel_id The channel id of the user to renew their token
     * @return array The response from App Engine
     */
    function renew_chat_token($channel_id) {
        global $pines;
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $pines->config->com_gae_chat->renew_token_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'password'  => $pines->config->com_gae_chat->password,
                'channel_id'     => $channel_id
            )
        ));
        
        $response = curl_exec($ch);
        $message = json_decode($response);
        
        return $message;
    }
}
?>