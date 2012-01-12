<?php
/**
 * Print front page meta tags.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

foreach ((array) $pines->config->com_content->front_page_meta_tags as $cur_meta_tag) {
	list ($name, $content) = explode(':', $cur_meta_tag, 2);
	if (empty($name) || empty($content))
		continue;
	?>

<meta name="<?php echo htmlspecialchars($name); ?>" content="<?php echo htmlspecialchars($content); ?>" />
<?php } ?>
