<?php
/**
 * Provides a form for the user to edit a category.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Category' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide category details in this form.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'category/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlentities("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlentities("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlentities($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Parent</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="parent">
				<option value="null">-- No Parent --</option>
				<?php
				/**
				 * Print children of a category into the select box.
				 * @param com_sales_category $parent The parent category.
				 * @param com_sales_category|null $entity The current category.
				 * @param string $prefix The prefix to insert before names.
				 */
				function com_sales__category_form_children($parent, $entity, $prefix = '->') {
					foreach ($parent->children as $category) {
						if ($category->is($entity))
							continue;
						?>
						<option value="<?php echo $category->guid; ?>"<?php echo $category->is($entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlentities("{$prefix} {$category->name}"); ?></option>
						<?php
						if ($category->children)
							com_sales__category_form_children($category, $entity, "{$prefix}->");
					}
				}
				foreach ($this->categories as $category) {
					if ($category->is($this->entity))
						continue;
					?>
					<option value="<?php echo $category->guid; ?>"<?php echo $category->is($this->entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlentities($category->name); ?></option>
					<?php
					if ($category->children)
						com_sales__category_form_children($category, $this->entity);
				} ?>
			</select>
		</label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Products</span>
		<span class="pf-note">These products are assigned to this category.</span>
		<div class="pf-group">
			<?php foreach ($this->entity->products as $cur_product) { ?>
			<div class="pf-field"><a href="<?php echo htmlentities(pines_url('com_sales', 'product/edit', array('id' => $cur_product->guid))); ?>"><?php echo htmlentities("[{$cur_product->guid}] {$cur_product->name}"); ?></a></div>
			<?php } ?>
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'category/list')); ?>');" value="Cancel" />
	</div>
</form>