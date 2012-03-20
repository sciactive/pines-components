<?php
/**
 * Shows category products.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if ($this->entity->show_title)
	$this->title = empty($this->entity->replace_title) ? htmlspecialchars($this->entity->name) : htmlspecialchars($this->entity->replace_title);

// Get the products in this category.
if ($this->entity->show_products)
	$products = $pines->com_storefront->get_cat_products($this->entity, $this->page, $this->products_per_page, $offset, $count, $pages, $this->sort_var, $this->sort_reverse);

foreach ((array) $this->show_page_modules as $cur_module) {
	echo $cur_module->render();
} if ($this->entity->show_children) { ?>
<div id="p_muid_children" class="com_storefront_children">
	In this category:
	<ul>
		<?php foreach ($this->entity->children as $cur_child) {
			if (!isset($cur_child) || !$cur_child->enabled)
				continue;
			?>
		<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $cur_child->alias))); ?>"><?php echo htmlspecialchars($cur_child->name); ?></a></li>
		<?php } ?>
	</ul>
</div>
<?php } if ($this->entity->show_products) {
	if (!$products) { ?>
<div class="com_storefront_no_products">
	No matching products were found.
</div>
<?php } else {
		ob_start(); ?>
<div class="com_storefront_paginate">
	<?php if ($pages != 1) { ?>
	<div style="float: left; margin: 1em 1em 1em 0;" class="pagination">
		<ul>
			<?php if ($this->page - 1 >= 1) { if ($this->page - 1 != 1) { ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => 1, 'sort' => $this->sort))); ?>">&#8676;</a></li>
			<?php } ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page - 1, 'sort' => $this->sort))); ?>">&larr;</a></li>
			<?php } if ($this->page - 2 >= 1) {
				if ($this->page - 2 > 1) { ?>
			<li class="disabled">&hellip;</li>
				<?php } ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page - 2, 'sort' => $this->sort))); ?>"><?php echo $this->page - 2; ?></a></li>
			<?php } if ($this->page - 1 >= 1) { ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page - 1, 'sort' => $this->sort))); ?>"><?php echo $this->page - 1; ?></a></li>
			<?php } ?>
			<li class="active"><a href="javascript:void(0);"><?php echo htmlspecialchars($this->page); ?></a></li>
			<?php if ($this->page + 1 <= $pages) { ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page + 1, 'sort' => $this->sort))); ?>"><?php echo $this->page + 1; ?></a></li>
			<?php } ?>
			<?php if ($this->page + 2 <= $pages) { ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page + 2, 'sort' => $this->sort))); ?>"><?php echo $this->page + 2; ?></a></li>
			<?php if ($this->page + 2 < $pages) { ?>
			<li class="disabled">&hellip;</li>
				<?php }
				} ?>
			<?php if ($this->page + 1 <= $pages) { ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page + 1, 'sort' => $this->sort))); ?>">&rarr;</a></li>
			<?php if ($this->page + 1 != $pages) { ?>
			<li><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $pages, 'sort' => $this->sort))); ?>">&#8677;</a></li>
			<?php } } ?>
		</ul>
	</div>
	<?php } ?>
	<div style="float: right; margin: 1em 0 1em 1em;">
		Showing <?php echo $offset + 1; ?>-<?php echo $offset + count($products); ?> of <?php echo (int) $count; ?>.
		Sorted by <select name="sort" onchange="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page)))); ?>, {sort: $(this).val()})">
			<option value="name"<?php echo ($this->sort == 'name') ? ' selected="selected"' : '' ?>>Name (A to Z)</option>
			<option value="name_r"<?php echo ($this->sort == 'name_r') ? ' selected="selected"' : '' ?>>Name (Z to A)</option>
			<option value="unit_price"<?php echo ($this->sort == 'unit_price') ? ' selected="selected"' : '' ?>>Price (Low to High)</option>
			<option value="unit_price_r"<?php echo ($this->sort == 'unit_price_r') ? ' selected="selected"' : '' ?>>Price (High to Low)</option>
			<option value="p_cdate"<?php echo ($this->sort == 'p_cdate') ? ' selected="selected"' : '' ?>>Date (Oldest First)</option>
			<option value="p_cdate_r"<?php echo ($this->sort == 'p_cdate_r') ? ' selected="selected"' : '' ?>>Date (Newest First)</option>
		</select>
	</div>
</div>
<?php $header = ob_get_clean(); echo $header; ?>
<div id="p_muid_products" class="com_storefront_products" style="clear: both;">
	<?php
	/**
	 * Include the category template.
	 */
	include(__DIR__.'/templates/'.clean_filename($pines->config->com_storefront->category_template).'.php'); ?>
</div>
<?php echo $header; } } ?>