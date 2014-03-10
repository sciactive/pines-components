<?php
/**
 * com_pgrid class.
 *
 * @package Components\pgrid
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_pgrid main class.
 *
 * A JavaScript data grid.
 *
 * @package Components\pgrid
 */
class com_pgrid extends component {
	/**
	 * Whether the pgrid JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the grid.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			global $pines;
			if ($pines->config->compress_cssjs) {
                            $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
                            
                            $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
                            $css[] = $file_root.'components/com_pgrid/includes/jquery.pgrid.'.htmlspecialchars($pines->config->com_pgrid->styling).'.css';
                            $css[] = $file_root.'components/com_pgrid/includes/jquery.pgrid.'.htmlspecialchars($pines->config->com_pgrid->styling).'.icons.css';
                            $pines->config->loadcompressedcss = $css;
                            
                            $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                            $js[] =  $file_root.'components/com_pgrid/includes/'.(($pines->config->debug_mode) ? 'jquery.pgrid.js' : 'jquery.pgrid.min.js');
                            if ($pines->config->com_pgrid->toolbar_target == '_self') {
                                $js[] =  $file_root.'components/com_pgrid/includes/jquery.pgrid.self.js';
                            } else if ($pines->config->com_pgrid->toolbar_target == '_blank') {
                                $js[] =  $file_root.'components/com_pgrid/includes/jquery.pgrid.blank.js';
                            } else if ($pines->config->com_pgrid->toolbar_target == 'popup') {
                                $js[] =  $file_root.'components/com_pgrid/includes/jquery.pgrid.popup.js';
                            }
                            $pines->config->loadcompressedjs = $js;
                        } else {
                            $module = new module('com_pgrid', 'pgrid', 'head');
                            $module->render();
                        }
			$this->js_loaded = true;
		}
	}
}

?>