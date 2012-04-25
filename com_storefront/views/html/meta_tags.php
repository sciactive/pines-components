<?php
/**
 * Print meta tags.
 *
 * @package Components
 * @subpackage storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

foreach ((array) $this->entity->meta_tags as $cur_meta_tag) { ?>

<meta name="<?php echo htmlspecialchars($cur_meta_tag['name']); ?>" content="<?php echo htmlspecialchars(format_content($cur_meta_tag['content'])); ?>" />
<?php } ?>