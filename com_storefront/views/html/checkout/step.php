<?php
/**
 * Shows checkout step image.
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

// Is this module even necessary?
if ($pines->config->com_storefront->skip_shipping && $pines->config->com_storefront->review_in_payment_page) {
	$this->detach();
	return;
}
?>
<script type="text/javascript">
	pines(function(){
		$("#p_muid_buttons")
		.find(".step_<?php echo (int) $this->step; ?>").addClass("btn-primary").attr("disabled", "disabled")<?php
		if (!isset($_SESSION['user'])) {
			echo '.end().find(".step_2, .step_3, .step_4").addClass("disabled").attr("disabled", "disabled")';
		} elseif (
				!isset($_SESSION['com_storefront_sale']->shipping_address->address_type) ||
				(
					$_SESSION['com_storefront_sale']->shipping_address->address_type == 'us' &&
					(
						empty($_SESSION['com_storefront_sale']->shipping_address->address_1) ||
						empty($_SESSION['com_storefront_sale']->shipping_address->city) ||
						empty($_SESSION['com_storefront_sale']->shipping_address->state) ||
						empty($_SESSION['com_storefront_sale']->shipping_address->zip)
					)
				) || (
					$_SESSION['com_storefront_sale']->shipping_address->address_type == 'international' &&
					empty($_SESSION['com_storefront_sale']->shipping_address->address_international)
				)
			) {
			echo '.end().find(".step_3, .step_4").addClass("disabled").attr("disabled", "disabled")';
		} elseif ($_SESSION['com_storefront_sale']->payments[0]['status'] != 'approved') {
			echo '.end().find(".step_4").addClass("disabled").attr("disabled", "disabled")';
		}
		?>;
	});
</script>
<div class="btn-toolbar" style="text-align: center;">
	<div id="p_muid_buttons" class="btn-group">
		<button class="step_1 btn btn-large" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/login'))); ?>);"><span style="font-size: 1.2em; vertical-align: middle; font-weight: bold;">1</span> Log-In</button>
		<?php if (!$pines->config->com_storefront->skip_shipping) { ?>
		<button class="step_2 btn btn-large" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/shipping'))); ?>);"><span style="font-size: 1.2em; vertical-align: middle; font-weight: bold;">2</span> Shipping</button>
		<?php } ?>
		<button class="step_3 btn btn-large" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/payment'))); ?>);"><span style="font-size: 1.2em; vertical-align: middle; font-weight: bold;"><?php echo ($pines->config->com_storefront->skip_shipping ? '2' : '3'); ?></span> Payment</button>
		<?php if (!$pines->config->com_storefront->review_in_payment_page) { ?>
		<button class="step_4 btn btn-large" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/review'))); ?>);"><span style="font-size: 1.2em; vertical-align: middle; font-weight: bold;"><?php echo ($pines->config->com_storefront->skip_shipping ? '3' : '4'); ?></span> Review</button>
		<?php } ?>
	</div>
</div>