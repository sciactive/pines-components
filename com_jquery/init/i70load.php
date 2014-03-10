<?php
/**
 * Load jQuery.
 *
 * @package Components\jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->compress_cssjs) {
    $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
    $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
    // jQuery
    $js[] =  $file_root.'components/com_jquery/includes/'.($pines->config->debug_mode ? 'jquery-1.7.2.js' : 'jquery-1.7.2.min.js');
    
    //jQuery-ui
    $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
    $css[] = $file_root.'components/com_jquery/includes/jquery-ui/'.htmlspecialchars($pines->config->com_jquery->theme).'/jquery-ui.css';
    $pines->config->loadcompressedcss = $css;
    
    $js[] =  $file_root.'components/com_jquery/includes/'.($pines->config->debug_mode ? 'jquery-ui-1.8.21.js' : 'jquery-ui-1.8.21.min.js');
    if (isset($pines->com_bootstrap)) {
        $js[] =  $file_root.'components/com_jquery/includes/jquery-ui/bootstrap-compatibility.js';
    }
    $pines->config->loadcompressedjs = $js;
} else {
    $module = new module('com_jquery', 'jquery', 'head');
    unset ($module);
    $module = new module('com_jquery', 'jquery-ui', 'head');
    unset ($module);
}
?>