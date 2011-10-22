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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Received Inventory';
$comments = array();
$i = 1;
?>
<?php if (empty($this->success)) { ?>
<p>No inventory was received.</p>
<?php return; } ?>

<div class="pf-form">
	<div class="pf-element">
		<span class="pf-label">Location</span>
		<span class="pf-field"><?php echo htmlspecialchars("[{$this->location->guid}] {$this->location->name}"); ?></span>
	</div>
	<?php foreach($this->success as $cur_success) {
		if ($cur_success[1]->has_tag('po')) {
			$success_id = $cur_success[1]->po_number;
			$success_name = 'PO: '.$success_id;
		} else if ($cur_success[1]->has_tag('transfer')) {
			$success_id = $cur_success[1]->guid;
			$success_name = 'Transfer: '.$success_id;
		}
		if (!empty($cur_success[1]->comments) && !in_array($cur_success[1]->comments, $comments))
			$comments[] = array($success_name, $cur_success[1]->comments);
	?>
	<div class="pf-element pf-heading">
		<h1>Item <?php echo $i; $i++; ?></h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">Product</span>
		<span class="pf-field"><?php echo htmlspecialchars($cur_success[0]->product->name); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Vendor</span>
		<span class="pf-field"><?php echo htmlspecialchars($cur_success[0]->vendor->name); ?></span>
	</div>
	<?php if (isset($cur_success[0]->serial)) { ?>
	<div class="pf-element">
		<span class="pf-label">Serial</span>
		<span class="pf-field"><?php echo htmlspecialchars($cur_success[0]->serial); ?></span>
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Received On</span>
		<span class="pf-field"><?php echo htmlspecialchars($success_name); ?></span>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<ul>
			<?php foreach($comments as $cur_comment) {
				echo '<li>'.htmlspecialchars($cur_comment[0].' - '.$cur_comment[1]).'</li>';
			} ?>
		</ul>
	</div>
</div>