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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($this->entity->show_title)
	$this->title = htmlspecialchars($this->entity->name);

// Get the products in this category.
if ($this->entity->show_products)
	$products = $pines->com_storefront->get_cat_products($this->entity, $this->page, $this->products_per_page, $offset, $count, $pages, $this->sort_var, $this->sort_reverse);

?>
<style type="text/css">
	/* <![CDATA[ */
	.p_muid_paginate {
		text-align: center;
		margin: 1em;
	}
	/* ]]> */
</style>
<?php foreach ((array) $this->show_page_modules as $cur_module) {
	echo $cur_module->render();
} if ($this->entity->show_children) { ?>
<div id="p_muid_children">
	In this category:
	<ul>
		<?php foreach ($this->entity->children as $cur_child) {
			if (!isset($cur_child) || !$cur_child->enabled)
				continue;
			?>
		<li><?php echo htmlspecialchars($cur_child->name); ?></li>
		<?php } ?>
	</ul>
</div>
<?php } if ($this->entity->show_products) { ob_start(); ?>
<div class="p_muid_paginate">
	<?php if ($pages != 1) { ?>
	<div style="float: left; margin-right: 1em;">
		<?php if ($this->page - 1 >= 1) { if ($this->page - 1 != 1) { ?>
		<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => 1, 'sort' => $this->sort))); ?>">First</a>&nbsp;
		<?php } ?>
		<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page - 1, 'sort' => $this->sort))); ?>">Prev</a>
		<?php } ?>
	</div>
	<div style="float: left; margin-right: 1em;">
		<?php if ($this->page - 2 >= 1) { ?>
			<?php if ($this->page - 2 > 1) { ?>...&nbsp;<?php } ?>
			<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page - 2, 'sort' => $this->sort))); ?>"><?php echo $this->page - 2; ?></a>&nbsp;
		<?php } ?>
		<?php if ($this->page - 1 >= 1) { ?>
			<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page - 1, 'sort' => $this->sort))); ?>"><?php echo $this->page - 1; ?></a>&nbsp;
		<?php } ?>
		<span class="ui-state-default" style="border: none;"><?php echo $this->page; ?></span>
		<?php if ($this->page + 1 <= $pages) { ?>
			&nbsp;<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page + 1, 'sort' => $this->sort))); ?>"><?php echo $this->page + 1; ?></a>
		<?php } ?>
		<?php if ($this->page + 2 <= $pages) { ?>
			&nbsp;<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page + 2, 'sort' => $this->sort))); ?>"><?php echo $this->page + 2; ?></a>
			<?php if ($this->page + 2 < $pages) { ?>&nbsp;...<?php } ?>
		<?php } ?>
	</div>
	<div style="float: left; margin-right: 1em;">
		<?php if ($this->page + 1 <= $pages) { ?>
		<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page + 1, 'sort' => $this->sort))); ?>">Next</a>
		<?php if ($this->page + 1 != $pages) { ?>
		&nbsp;<a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $pages, 'sort' => $this->sort))); ?>">Last</a>
		<?php } } ?>
	</div>
	<?php } ?>
	<div style="float: right; margin-left: 1em;">
		Showing <?php echo $offset + 1; ?>-<?php echo $offset + count($products); ?> of <?php echo (int) $count; ?>.
		Sorted by <select class="ui-widget-content ui-corner-all" name="sort" onchange="pines.get('<?php echo htmlspecialchars(pines_url('com_storefront', 'category/browse', array('a' => $this->entity->alias, 'page' => $this->page))); ?>', {sort: $(this).val()})">
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
<div id="p_muid_products" style="clear: both;">
	<?php include(__DIR__.'/templates/'.clean_filename($pines->config->com_storefront->category_template).'.php'); ?>
</div>
<?php echo $header; } ?>