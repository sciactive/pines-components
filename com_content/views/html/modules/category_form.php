<?php
/**
 * Provides a form for the user to choose a category.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category')));
$pines->entity_manager->sort($categories, 'name');
?>
<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">Category</span>
			<select class="pf-field" name="id">
				<?php foreach ($categories as $cur_category) { ?>
				<option value="<?php echo htmlspecialchars($cur_category->guid); ?>"<?php echo $this->id == "$cur_category->guid" ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_category->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
</div>