<?php
/**
 * Load Pines Notify.
 *
 * @package Components\pnotify
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($pines->config->compress_cssjs) {
    $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
    
    $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
    $css[] = $file_root.'components/com_pnotify/includes/jquery.pnotify.default.css';
    $css[] = $file_root.'components/com_pnotify/includes/jquery.pnotify.default.icons.css';
    $pines->config->loadcompressedcss = $css;
    
    $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
    $js[] =  $file_root.'components/com_pnotify/includes/'.(($pines->config->debug_mode) ? 'jquery.pnotify.js' : 'jquery.pnotify.min.js');
    $js[] =  $file_root.'components/com_pnotify/includes/jquery.pnotify.pines.js';
    $pines->config->loadcompressedjs = $js;
} else {
    $module = new module('com_pnotify', 'pnotify', 'head');
    unset ($module);
}
?>