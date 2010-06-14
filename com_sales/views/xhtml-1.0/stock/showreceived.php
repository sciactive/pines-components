<?php
/**
 * Shows the result of received inventory processing.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Received Inventory';
$i = 1;
?>
<?php if (empty($this->success)) { ?>
<p>No inventory was received.</p>
<?php return; } ?>

<div class="pf-form">
	<div class="pf-element">
		<span class="pf-label">Location</span>
		<span class="pf-feild"><?php echo "[{$this->location->guid}] {$this->location->name}"; ?></span>
	</div>
	<?php foreach($this->success as $cur_success) { ?>
	<div class="pf-element pf-heading">
		<h1>Item <?php echo $i; $i++; ?></h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">Product</span>
		<span class="pf-field"><?php echo $cur_success[0]->product->name; ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Vendor</span>
		<span class="pf-field"><?php echo $cur_success[0]->vendor->name; ?></span>
	</div>
	<?php if (isset($cur_success[0]->serial)) { ?>
	<div class="pf-element">
		<span class="pf-label">Serial</span>
		<span class="pf-field"><?php echo $cur_success[0]->serial; ?></span>
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Received On</span>
		<?php if ($cur_success[1]->has_tag('po')) { ?>
		<span class="pf-field"><?php echo 'PO: '.$cur_success[1]->po_number; ?></span>
		<?php } elseif($cur_success[1]->has_tag('transfer')) { ?>
		<span class="pf-field"><?php echo 'Transfer: '.$cur_success[1]->guid; ?></span>
		<?php } ?>
	</div>
	<?php } ?>
</div>