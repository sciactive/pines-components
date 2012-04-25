<?php
/**
 * jtemplate_adapter class.
 *
 * @package Templates
 * @subpackage joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Joomla! template adapter.
 *
 * @package Templates
 * @subpackage joomlatemplates
 */
class jtemplate_adapter {
	/**
	 * The adapted template.
	 * @var string
	 */
	public $template;
	/**
	 * The template directory.
	 * @var string
	 */
	public $template_dir;
	/**
	 * The template language.
	 * @var string
	 */
	public $language;
	/**
	 * The template direction.
	 * @var string
	 */
	public $direction;
	/**
	 * The Joomla base URL.
	 * @var string
	 */
	public $baseurl;
	/**
	 * Template params.
	 * @var jtemplate_params
	 */
	public $params;

	/**
	 * Set up a Joomla! Template Adapter.
	 * @param string $template The template to adapt.
	 * @param string $template_dir The template's directory.
	 */
	public function __construct($template, $template_dir) {
		global $pines;
		/**
		 * Joomla! template parameters class.
		 */
		include('templates/tpl_joomlatemplates/classes/jtemplate_params.php');
		/**
		 * Fake Joomla! module classes.
		 */
		include('templates/tpl_joomlatemplates/classes/jmodule_classes.php');
		/**
		 * Fake Joomla! classes.
		 */
		include('templates/tpl_joomlatemplates/classes/fake_joomla_classes.php');

		// Set up the fake environment.
		$this->template = htmlspecialchars($template);
		$this->template_dir = $template_dir;
		$this->language = htmlspecialchars($pines->config->tpl_joomlatemplates->language);
		$this->direction = htmlspecialchars($pines->config->tpl_joomlatemplates->direction);
		$this->baseurl = htmlspecialchars($pines->config->location.'templates/tpl_joomlatemplates');
		$this->params = new jtemplate_params($template, $template_dir);
	}
	
	public function translate_position($position) {
		global $pines;
		if ($position == $pines->config->tpl_joomlatemplates->main_menu_position)
			return 'main_menu';
		if ($position == 'breadcrumb')
			return 'breadcrumbs';
		return $position;
	}
	
	/**
	 * Count the modules in a position.
	 * @param string $position The position of the modules.
	 * @return int The number of modules in that position.
	 */
	public function countModules($position) {
		global $pines;
		$parts = explode(' ', $position);
		$cur_pos = $this->translate_position(current($parts));
		if (!$pines->page->modules[$cur_pos])
			$result = 0;
		$result = count($pines->page->modules[$cur_pos]);
		while (($cur_op = next($parts)) !== false) {
			$cur_pos = $this->translate_position(next($parts));
			if (!$pines->page->modules[$cur_pos])
				$cur_result = 0;
			$cur_result = count($pines->page->modules[$cur_pos]);
			switch ($cur_op) {
				 case '+':
					 $result = $result + $cur_result;
					 break;
				 case '-':
					 $result = $result - $cur_result;
					 break;
				 case '*':
					 $result = $result * $cur_result;
					 break;
				 case '/':
					 $result = $result / $cur_result;
					 break;
				 case '==':
					 $result = $result == $cur_result;
					 break;
				 case '!=':
					 $result = $result != $cur_result;
					 break;
				 case '<>':
					 $result = $result <> $cur_result;
					 break;
				 case '<':
					 $result = $result < $cur_result;
					 break;
				 case '>':
					 $result = $result > $cur_result;
					 break;
				 case '<=':
					 $result = $result <= $cur_result;
					 break;
				 case '>=':
					 $result = $result >= $cur_result;
					 break;
				 case 'and':
					 $result = $result and $cur_result;
					 break;
				 case 'or':
					 $result = $result or $cur_result;
					 break;
				 case 'xor':
					 $result = $result xor $cur_result;
					 break;
			}
		}
		return $result;
	}

	/**
	 * Output the code of the Joomla! template.
	 * 
	 * @todo Handle notices and errors.
	 */
	public function render() {
		/**
		 * Pretend to be running Joomla!.
		 */
		define('_JEXEC', true);
		/**
		 * They use a directory separator constant.
		 */
		define('DS', '/');

		/**
		 * Now include the "system" chrome functions for module styling.
		 */
		include('templates/tpl_joomlatemplates/templates/system/html/modules.php');
		if (file_exists("{$this->template_dir}html/modules.php")) {
			/**
			 * Now include the template/s chrome functions for module styling.
			 */
			include("{$this->template_dir}html/modules.php");
		}

		// Start an output buffer to capture the template output.
		ob_start();
		/**
		 * Include the template's index file.
		 */
		include("{$this->template_dir}index.php");
		// Get the output.
		$output = ob_get_clean();
		
		if (preg_match_all('/<jdoc:include (\w+="[^"]+" ?)+\/>/', $output, $matches, PREG_OFFSET_CAPTURE)) {
			$matches = $matches[0];
			// First render all the modules in the right order.
			usort($matches, array($this, 'sort_first_to_last'));
			foreach ($matches as &$cur_match) {
				$cur_match['string'] = $cur_match[0];
				$cur_match['offset'] = $cur_match[1];
				$cur_match['options'] = array();
				if (preg_match_all('/(\w+)="([^"]+)"/', $cur_match['string'], $option_matches, PREG_SET_ORDER)) {
					foreach ($option_matches as $cur_option_match)
						$cur_match['options'][$cur_option_match[1]] = $cur_option_match[2];
				}
				$cur_match['content'] = $this->render_modules($cur_match['options']);
			}
			unset($cur_match);
			// Now reverse order and replace all the JDoc includes.
			usort($matches, array($this, 'sort_last_to_first'));
			foreach ($matches as &$cur_match) {
				$output = substr_replace($output, $cur_match['content'], $cur_match['offset'], strlen($cur_match['string']));
			}
			unset($cur_match);
		}
		echo $output;
	}
	
	/**
	 * Render modules for replacing a JDoc include.
	 * @param array $options The options on the JDoc include.
	 * @return string The rendered content.
	 */
	public function render_modules($options) {
		global $pines;
		$pines->template->cur_module_options = $options;
		switch ($options['type']) {
			case 'head':
				$content = "\n<title>".htmlspecialchars($pines->page->get_title())."</title>\n".
				'<link href="'.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />'."\n".
				'<link href="'.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />'."\n".
				'<link href="'.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />'."\n".
				'<link href="'.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/css/dropdown_fix.css" media="all" rel="stylesheet" type="text/css" />'."\n".
				'<script type="text/javascript" src="'.htmlspecialchars($pines->config->rela_location).'system/includes/js.php"></script>'."\n".
//				'<script type="text/javascript" src="'.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/js/template.js"></script>'."\n".
				'<!--[if lt IE 7]>'."\n".
				'<script type="text/javascript" src="'.htmlspecialchars($pines->config->location).'templates/'.htmlspecialchars($pines->current_template).'/js/jquery/jquery.dropdown.js"></script>'."\n".
				'<![endif]-->'."\n".
				$pines->page->render_modules('head', 'module_head')."\n";
				break;
			case 'modules':
				$position = $this->translate_position($options['name']);
				$content = $pines->page->render_modules($position, $position == 'main_menu' ? 'module_head' : 'module');
				break;
			case 'component':
				$pines->template->cur_module_options['style'] = $pines->config->tpl_joomlatemplates->content_style;
				$content = $pines->page->render_modules('content', 'module');
				break;
			default:
				return '';
				break;
		}
		return $content;
	}

	/**
	 * Sort based on offset.
	 * @param array $a First value.
	 * @param array $b Second value.
	 * @return int Sort order.
	 */
	public function sort_first_to_last($a, $b) {
		if ($a['offset'] < $b['offset'])
			return -1;
		if ($a['offset'] > $b['offset'])
			return 1;
		return 0;
	}

	/**
	 * Sort based on offset.
	 * @param array $a First value.
	 * @param array $b Second value.
	 * @return int Sort order.
	 */
	public function sort_last_to_first($a, $b) {
		if ($a['offset'] < $b['offset'])
			return 1;
		if ($a['offset'] > $b['offset'])
			return -1;
		return 0;
	}
}

?>