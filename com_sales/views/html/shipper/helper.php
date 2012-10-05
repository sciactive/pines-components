<?php
/**
 * Shipper entity helper.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$module = new module('com_entityhelper', 'default_helper');
$module->render = $this->render;
$module->entity = $this->entity;
echo $module->render();

if ($this->render == 'body' && gatekeeper('com_sales/listshippers')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;" colspan="2">GUID</td>
				<td><?php echo htmlspecialchars($this->entity->guid); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;" colspan="2">Name</td>
				<td><?php echo htmlspecialchars($this->entity->name); ?></td>
			</tr>
			<?php if (!empty($this->entity->email)) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Email</td>
				<td><a href="mailto:<?php echo htmlspecialchars($this->entity->email); ?>"><?php echo htmlspecialchars($this->entity->email); ?></a></td>
			</tr>
			<?php } if (!empty($this->entity->phone_work)) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Phone</td>
				<td><a href="tel:<?php echo htmlspecialchars($this->entity->phone_work); ?>"><?php echo htmlspecialchars(format_phone($this->entity->phone_work)); ?></a></td>
			</tr>
			<?php } if (!empty($this->entity->fax)) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Fax</td>
				<td><a href="tel:<?php echo htmlspecialchars($this->entity->fax); ?>"><?php echo htmlspecialchars(format_phone($this->entity->fax)); ?></a></td>
			</tr>
			<?php } if (!empty($this->entity->account_number)) { ?>
			<tr>
				<td style="font-weight:bold;" colspan="2">Account Number</td>
				<td><?php echo htmlspecialchars($this->entity->account_number); ?></td>
			</tr>
			<?php } ?>
			<tr >
				<td style="font-weight:bold; vertical-align: middle;" rowspan="4">Tracking</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Supported</td>
				<td><?php echo $this->entity->can_track() ? 'Yes' : 'No'; ?></td>
			</tr>
			<?php if (isset($this->entity->tracking) && ($this->entity->tracking != 'custom' || ($this->entity->tracking == 'custom' && !empty($this->entity->tracking_url)))) { ?>
			<tr>
				<td style="font-weight:bold;">Setup</td>
				<td><?php echo htmlspecialchars($this->entity->tracking); ?></td>
			</tr>
			<?php } if (!empty($this->entity->tracking_url)) { ?>
			<tr>
				<td style="font-weight:bold;">URL</td>
				<td><?php echo htmlspecialchars($this->entity->tracking_url); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php if (!empty($this->entity->address_1) || !empty($this->entity->address_international)) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Address</h3>
	<address>
		<?php if ($this->entity->address_type == 'us') {
			echo htmlspecialchars($this->entity->address_1).'<br />';
			if (!empty($this->entity->address_2))
				echo htmlspecialchars($this->entity->address_2).'<br />';
			echo htmlspecialchars($this->entity->city).', ';
			echo htmlspecialchars($this->entity->state).' ';
			echo htmlspecialchars($this->entity->zip);
		} else {
			echo '<pre>'.htmlspecialchars($this->entity->address_international).'</pre>';
		} ?>
	</address>
</div>
<?php } if (!empty($this->entity->terms)) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Terms</h3>
	<pre><?php echo htmlspecialchars($this->entity->terms); ?></pre>
</div>
<?php } if (!empty($this->entity->comments)) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Comments</h3>
	<pre><?php echo htmlspecialchars($this->entity->comments); ?></pre>
</div>
<?php } } ?>