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
    
    private $customer_js_loaded = false;
    private $employee_js_loaded = false;
    
    /**
     * Loads the Customer JS Files for GAE Chat
     *
     * This will place the required scripts into the document's bottom section.
     * 
     */
    function load_customer_js() {
        global $pines;
        if (!$this->customer_js_loaded) {
                if ($pines->config->compress_cssjs) {
                        $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
                        $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                        // Add Css and JS
                        $js[] =  $file_root.'components/com_gae_chat/includes/'.($pines->config->debug_mode ? 'customer_chat.js' : 'customer_chat.min.js');
                        $js[] =  $file_root.'components/com_gae_chat/includes/channel.js';
                        $pines->config->loadcompressedjs = $js;
                        
                        $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
                        $css[] = $file_root.'components/com_gae_chat/includes/customer_chat.css';
                        $pines->config->loadcompressedcss = $css;
                } else {
                        $module = new module('com_gae_chat', 'chat_customer_js', 'bottom');
                        $module->render();
                }
                $this->customer_js_loaded = true;
        }
    }
    
    /**
     * Loads the Employee JS Files for GAE Chat
     */
    function load_employee_js() {
        global $pines;
        if (!$this->employee_js_loaded) {
                if ($pines->config->compress_cssjs) {
                        $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
                        $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                        // Add Css, and JS
                        $js[] =  $file_root.'components/com_gae_chat/includes/'.($pines->config->debug_mode ? 'employee_chat.js' : 'employee_chat.min.js');
                        $js[] =  $file_root.'components/com_gae_chat/includes/channel.js';
                        $pines->config->loadcompressedjs = $js;
                        
                        $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
                        $css[] = $file_root.'components/com_gae_chat/includes/employee_chat.css';
                        $pines->config->loadcompressedcss = $css;
                } else {
                        $module = new module('com_gae_chat', 'chat_js', 'bottom');
                        $module->render();
                }
                $this->employee_js_loaded = true;
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
     * @param string $username_link The link to use for the person's chat window title
     * @param string $page_url The current page the user is requesting the token from
     * @return array JSON Response from App Engine
     */
    function get_channel_token($guid = 0, $username = '', $email = '', $employee = false, $distinguish = false, $username_link = '', $page_url = '') {
        global $pines;
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
                'distinguished'   => $distinguish,
                'username_link' => $username_link,
                'page_url'      => $page_url
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
    
    /**
     * Get Base Link for Usernames in Chat Windows
     */
    function getBaseLink() {
        global $pines;
        
        $link_type = $pines->config->com_gae_chat->user_link_type;
        
        if ($link_type == 'constructed_url') {
            $class = $pines->config->com_gae_chat->user_link_class;
            $link_variable = $pines->config->com_gae_chat->user_link_variable;
            $baselink = $pines->config->com_gae_chat->user_link_base;
            
            $skip_factory = array('user', 'com_customer_customer', 'com_hrm_employee');
            $com_customer_installed = $pines->depend->check('component', 'com_customer');
            $using_cust_safe = ($class == 'com_customer_customer' && $com_customer_installed);
            
            $user_base_link = pines_url('com_user', 'user/edit');
            $symbol = (preg_match('#\?#', $baselink)) ? '&' : '?';
            $user_base_link = $user_base_link.$symbol.'id=';
            
            // Checking here if we can safely use com_customer_link and if not, check if we still specified com_customer
            // If we still specified com_customer, it will go to the com_user link and if it's not com_customer, it will use that the person specified
            $baselink = ($using_cust_safe) ? $baselink : (($class == 'com_customer_customer') ? $user_base_link : $baselink);
            
            $class = ($using_cust_safe) ? $class : (($class == 'com_customer_customer') ? 'user' : $class);
            
            if (!in_array($class, $skip_factory)) {
                $customer_guid = $_SESSION['user']->guid;
                $entity = $pines->entity_manager->get_entity(
                        array('class' => $class),
                        array('&', 
                            'ref' => array('customer', $customer_guid))
                        );
                
                if (!isset($entity->guid)) {
                    // We don't have an actual entity
                    // Default back to Session User
                    $entity = $_SESSION['user'];
                    
                }
                
            } else {
                $entity = $_SESSION['user'];
            }
            
            $var_value = $entity->{$link_variable};
            $link = $baselink.$var_value;
            
        } else if ($link_type == 'mail_to') {
            $link = 'mailto:' . (isset($_SESSION['user']->email)) ? $_SESSION['user']->email : $_SESSION['user']->verify_email;
            
        } else if ($link_type == 'no_link') {
            $link = $_SESSION['user']->name_first;
            
        }
        
        return $link;
    }
}
?>