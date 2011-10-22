<?php
/**
 * Provides a form for the user to choose a category.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category')));
$pines->entity_manager->sort($categories, 'name');
?>
<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">Category</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="id">
				<?php foreach ($categories as $cur_category) { ?>
				<option value="<?php echo $cur_category->guid; ?>"><?php echo htmlspecialchars($cur_category->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Page Limit</span>
			<span class="pf-note">Only show this many pages. Leave blank for unlimited.</span>
			<input class="pf-field ui-widget-content ui-corner-all" style="text-align: right;" type="text" name="page_limit" size="5" value="" /></label>
	</div>
</div>