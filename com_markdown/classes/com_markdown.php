<?php
/**
 * com_markdown class.
 *
 * @package Components\markdown
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_markdown main class.
 *
 * @package Components\markdown
 */
class com_markdown extends component {
	/**
	 * The markdown parser class.
	 * @access private
	 * @var MarkdownExtra_Parser $parser
	 */
	private $parser;

	/**
	 * Transform Markdown into HTML.
	 *
	 * @param string $text The markdown text.
	 * @return string The resulting HTML.
	 */
	public function transform($text) {
		if (!$this->parser) {
			if (!class_exists('MarkdownExtra_Parser')) {
				/**
				 * Include the Markdown classes. 
				 */
				include 'components/com_markdown/classes/markdown.php';
			}
			$this->parser = new MarkdownExtra_Parser;
		}
		// Transform text using parser.
		return $this->parser->transform($text);
	}
}

?>