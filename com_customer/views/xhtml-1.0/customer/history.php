<?php
/**
 * Provides a homepage for the customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer History ['.$this->entity->name.']';
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_interactions a, #p_muid_sales a, #p_muid_returns a {
		text-decoration: underline;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_interactions, #p_muid_sales, #p_muid_returns").pgrid({
			pgrid_toolbar: false,
			pgrid_footer: false,
			pgrid_view_height: 'auto',
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'desc'
		});
	});
	// ]]>
</script>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h1>Customer Interaction</h1>
	</div>
	<div class="pf-element pf-full-width">
		<?php if (!empty($this->interactions)) { ?>
		<table id="p_muid_interactions">
			<thead>
				<tr>
					<th>ID</th>
					<th>Date</th>
					<th>Employee</th>
					<th>Interaction</th>
					<th>Comments</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->interactions as $cur_interaction) { ?>
				<tr title="<?php echo $cur_interaction->guid; ?>">
					<td><?php echo $cur_interaction->guid; ?></td>
					<td><?php echo format_date($cur_interaction->action_date, 'full_sort'); ?></td>
					<td><?php echo htmlspecialchars($cur_interaction->user->name); ?></td>
					<td><?php echo htmlspecialchars($cur_interaction->type); ?></td>
					<td><?php echo htmlspecialchars($cur_interaction->comments); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php } else { ?>
		No customer interactions were documented for <?php echo htmlspecialchars($this->entity->name); ?>
		<?php } ?>
	</div>
	<?php if ($this->com_sales) { ?>
		<div class="pf-element pf-heading">
			<h1>Purchases</h1>
		</div>
		<div class="pf-element pf-full-width">
			<?php if (!empty($this->sales)) { ?>
			<table id="p_muid_sales">
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>Item(s)</th>
						<th>Price</th>
						<th>Status</th>
						<th>Location</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->sales as $cur_sale) {
					$item_count = count($cur_sale->products); ?>
					<tr title="<?php echo $cur_sale->guid; ?>">
						<td><a href="<?php echo pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid)); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->id); ?></a></td>
						<td><?php echo format_date($cur_sale->p_cdate); ?></td>
						<td><a href="<?php echo pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid)); ?>" onclick="window.open(this.href); return false;"><?php echo ($item_count == 1) ? htmlspecialchars($cur_sale->products[0]['entity']->name . ' x ' . $cur_sale->products[0]['quantity']) : $item_count.' products'; ?></a></td>
						<td>$<?php echo htmlspecialchars($cur_sale->total); ?></td>
						<td><?php switch ($cur_sale->status) {
							case 'invoiced':
								echo 'Invoiced';
								break;
							case 'paid':
								echo 'Paid';
								break;
							default:
								echo 'Unrecognized';
								break;
						} ?></td>
						<td><?php echo htmlspecialchars($cur_sale->group->name); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php } else {
				echo htmlspecialchars($this->entity->name).' has not made any purchases from '.$pines->config->system_name;
			} ?>
		</div>
		<div class="pf-element pf-heading">
			<h1>Returns</h1>
		</div>
		<div class="pf-element pf-full-width">
			<?php if (!empty($this->returns)) { ?>
			<table id="p_muid_returns">
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>Item(s)</th>
						<th>Total</th>
						<th>Status</th>
						<th>Location</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->returns as $cur_return) {
					$item_count = count($cur_return->products); ?>
					<tr title="<?php echo $cur_return->guid; ?>">
						<td><a href="<?php echo pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid)); ?>" target="receipt"><?php echo htmlspecialchars($cur_return->id); ?></a></td>
						<td><?php echo format_date($cur_return->p_cdate); ?></td>
						<td><a href="<?php echo pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid)); ?>" target="receipt"><?php echo ($item_count == 1) ? htmlspecialchars($cur_return->products[0]['entity']->name) : $item_count.' items'; ?></a></td>
						<td>$<?php echo htmlspecialchars($cur_return->total); ?></td>
						<td><?php echo htmlspecialchars(ucwords($cur_return->status)); ?></td>
						<td><?php echo htmlspecialchars($cur_return->group->name); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php } else {
				echo htmlspecialchars($this->entity->name).' has not returned any items to '.$pines->config->system_name;
			} ?>
		</div>
	<?php } ?>
	<div class="pf-element pf-buttons">
		<button class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_customer', 'customer/list')); ?>');">Close</button>
	</div>
</div>