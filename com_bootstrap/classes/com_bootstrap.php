<?php
/**
 * com_bootstrap class.
 *
 * @package Components\bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_bootstrap main class.
 *
 * @package Components\bootstrap
 */
class com_bootstrap extends component {
	/**
	 * Whether the Bootstrap scripts have been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $scripts_loaded = false;

	/**
	 * Whether the Template Bootstrap scripts have been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $tpl_bootstrap_loaded = false;
	
	/**
	 * Load the Bootstrap scripts.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
                global $pines;
		if (!$this->scripts_loaded) {
                        if ($pines->config->compress_cssjs) {
                            $file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'].$pines->config->location);
                            // Build CSS
                            $css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
                            $css[] = $file_root.'components/com_bootstrap/includes/themes/'.htmlspecialchars(clean_filename($pines->config->com_bootstrap->theme)).'/css/'.($pines->config->debug_mode ? 'bootstrap.css' : 'bootstrap.min.css');
                            if ($pines->config->com_bootstrap->responsive && file_exists('components/com_bootstrap/includes/themes/'.clean_filename($pines->config->com_bootstrap->theme).'/css/'.($pines->config->debug_mode ? 'bootstrap-responsive.css' : 'bootstrap-responsive.min.css'))) {
                               $css[] = $file_root.'components/com_bootstrap/includes/themes/'.htmlspecialchars(clean_filename($pines->config->com_bootstrap->theme)).'/css/'.($pines->config->debug_mode ? 'bootstrap-responsive.css' : 'bootstrap-responsive.min.css');
                            }
                            $css[] = $file_root.'components/com_bootstrap/includes/fontawesome/css/font-awesome.css';
                            $pines->config->loadcompressedcss = $css;
                            
                            $js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
                            $js[] =  $file_root.'components/com_bootstrap/includes/themes/'.htmlspecialchars(clean_filename($pines->config->com_bootstrap->theme)).'/js/'.($pines->config->debug_mode ? 'bootstrap.js' : 'bootstrap.min.js');
                            $js[] =  $file_root.'components/com_bootstrap/includes/'.($pines->config->debug_mode ? 'pines.get_columns.js' : 'pines.get_columns.min.js');
                        
                            $pines->config->loadcompressedjs = $js;
                        } else
                            $module = new module('com_bootstrap', 'bootstrap', 'head');
			// Not needed since no other libraries are loaded.
			//$module->render();
			$this->scripts_loaded = true;
		}
	}
	
	/**
	 * Load the tpl_bootstrap CSS/JS. Use com_template in the future.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load_js_css() {
		if (!$this->tpl_bootstrap_loaded) {
			$module = new module('com_bootstrap', 'load_template', 'head');
			// Not needed since no other libraries are loaded.
			//$module->render();
			$this->tpl_bootstrap_loaded = true;
		}
	}
	
}

?>