<?php
/**
 * Load Pines Form.
 *
 * @package Components\pform
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
    $css[] = $file_root.'components/com_pform/includes/'.(($pines->config->debug_mode) ? 'pform.css' : 'pform.min.css');
    $css[] = $file_root.'components/com_pform/includes/'.(($pines->config->debug_mode) ? 'pform-bootstrap.css' : 'pform-bootstrap.min.css');
    $pines->config->loadcompressedcss = $css;
} else {
    $module = new module('com_pform', 'pform', 'head');
    unset ($module);
}
?>