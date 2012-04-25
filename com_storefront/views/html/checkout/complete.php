<?php
/**
 * Shows a completed sale.
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
$this->title = 'Order Completed';
?>
<div class="pf-form">
	<div class="pf-element">
		Congratulations, your order is complete.
		<?php if (!empty($this->entity->customer->email)) { ?>
		Your receipt has been emailed to <?php echo htmlspecialchars($this->entity->customer->email); ?>.
		<?php } ?>
		Now you can
	</div>
	<div class="pf-element pf-buttons">
		<button class="pf-button btn btn-primary" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/receipt', array('id' => $this->entity->guid)))); ?>);">View Your Receipt</button>
		<button class="pf-button btn btn-primary" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url())); ?>);">Go Back Home</button>
	</div>
</div>