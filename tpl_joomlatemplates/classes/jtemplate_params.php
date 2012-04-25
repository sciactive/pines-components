<?php
/**
 * jtemplate_params class.
 *
 * @package Templates\joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Joomla! template parameters class.
 *
 * @package Templates\joomlatemplates
 */
class jtemplate_params {
	/**
	 * The template.
	 * @var string
	 */
	public $template;
	/**
	 * The template directory.
	 * @var string
	 */
	public $template_dir;
	/**
	 * An array of params.
	 * @var array
	 */
	public $params = array();

	/**
	 * Set up a Joomla! template params class.
	 * @param string $template The Joomla template.
	 * @param string $template_dir The template's directory.
	 */
	public function __construct($template, $template_dir) {
		$this->template = $template;
		$this->template_dir = $template_dir;
		if (file_exists("{$template_dir}params.ini"))
			$this->params = parse_ini_file("{$template_dir}params.ini", false, INI_SCANNER_RAW);
	}

	/**
	 * Get a parameter value.
	 * @param string $param The parameter.
	 * @return mixed The value.
	 */
	public function get($param) {
		return $this->params[$param];
	}

	/**
	 * Get a parameter array.
	 * @return array The associative array of values.
	 */
	public function toArray() {
		return $this->params;
	}
}

?>