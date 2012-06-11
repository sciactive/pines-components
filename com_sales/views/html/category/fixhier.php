<?php
/**
 * Lists categories which should be removed from products.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Product Category Fixes';
$this->note = 'These categories are assigned, but don\'t need to be.';

if (!$this->fixes) { ?>
<p>
	Congratulations, there were no unnecessary categories found.
</p>
<?php } else { ?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'category/fixhiersave')); ?>">
	<div class="pf-element">
		When a product is in a category, it is listed in the storefront in all the
		parent categories as well. Therefore, a product doesn't need to be placed in
		the ancestor of a category it is already in. The following table lists the
		categories that are assigned to products, but don't need to be. You can
		uncheck any of these suggestions to skip it, or leave it checked and it will
		be removed for you.
	</div>
	<div class="pf-element pf-full-width">
		<table class="table table-bordered table-condensed">
			<caption>Suggested Category Removals</caption>
			<thead>
				<tr>
					<th>Remove</th>
					<th>The Category</th>
					<th>From The Product</th>
					<th>Because It's Already In</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($this->fixes as $cur_key => $cur_fix) { ?>
				<tr>
					<td><input type="checkbox" checked="checked" name="fixes[]" value="<?php echo htmlspecialchars($cur_key); ?>" /></td>
					<td><a data-entity="<?php echo htmlspecialchars($cur_fix['category']->guid); ?>" data-entity-context="com_sales_category"><?php echo htmlspecialchars($cur_fix['category']->name); ?></a></td>
					<td><a data-entity="<?php echo htmlspecialchars($cur_fix['product']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_fix['product']->name); ?></a></td>
					<td>
						<?php
						$links = array();
						foreach ($cur_fix['reasons'] as $cur_reason)
							$links[] = '<a data-entity="'.htmlspecialchars($cur_reason->guid).'" data-entity-context="com_sales_category">'.htmlspecialchars($cur_reason->name).'</a>';
						echo implode(', ', $links);
						?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button btn btn-primary" type="submit" value="Make Changes" />
	</div>
</form>
<?php } ?>