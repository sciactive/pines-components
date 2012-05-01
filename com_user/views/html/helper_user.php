<?php
/**
 * User entity helper.
 *
 * @package Components\user
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

if ($this->render == 'body' && gatekeeper('com_user/listusers')) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">GUID</td>
				<td><?php echo htmlspecialchars($this->entity->guid); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Username</td>
				<td><?php echo htmlspecialchars($this->entity->username); ?></td>
			</tr>
			<?php if (in_array('name', $pines->config->com_user->user_fields)) { ?>
			<tr>
				<td style="font-weight:bold;">Real Name</td>
				<td><?php echo htmlspecialchars($this->entity->name); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td style="font-weight:bold;">Enabled</td>
				<td><?php echo $this->entity->has_tag('enabled') ? 'Yes' : 'No'; ?></td>
			</tr>
			<?php if (in_array('email', $pines->config->com_user->user_fields)) { ?>
			<tr>
				<td style="font-weight:bold;">Email</td>
				<td><a href="mailto:<?php echo htmlspecialchars($this->entity->email); ?>"><?php echo htmlspecialchars($this->entity->email); ?></a></td>
			</tr>
			<?php } if (in_array('phone', $pines->config->com_user->user_fields)) { ?>
			<tr>
				<td style="font-weight:bold;">Phone</td>
				<td><a href="tel:<?php echo htmlspecialchars($this->entity->phone); ?>"><?php echo htmlspecialchars(format_phone($this->entity->phone)); ?></a></td>
			</tr>
			<?php } if (in_array('fax', $pines->config->com_user->user_fields)) { ?>
			<tr>
				<td style="font-weight:bold;">Fax</td>
				<td><a href="tel:<?php echo htmlspecialchars($this->entity->fax); ?>"><?php echo htmlspecialchars(format_phone($this->entity->fax)); ?></a></td>
			</tr>
			<?php } if (in_array('timezone', $pines->config->com_user->user_fields)) { ?>
			<tr>
				<td style="font-weight:bold;">Timezone</td>
				<td><?php echo htmlspecialchars($this->entity->get_timezone()).(empty($this->entity->timezone) ? ' (Inherited)' : ' (Assigned)'); ?></td>
			</tr>
			<?php } ?>
			<tr>
				<td style="font-weight:bold;">Primary Group</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($this->entity->group->guid ? $this->entity->group->info('name') : ''); ?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Groups</td>
				<td>
					<ul>
						<?php
						$names = array();
						foreach ((array) $this->entity->groups as $cur_group) {
							if (!isset($cur_group->guid))
								continue;
							$names[] = '<li><a data-entity="'.htmlspecialchars($cur_group->guid).'" data-entity-context="group">'.htmlspecialchars($cur_group->info('name')).'</a></li>';
						}
						echo implode("\n", $names);
						?>
					</ul>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Inherit Abilities</td>
				<td><?php echo $this->entity->inherit_abilities ? 'Yes' : 'No'; ?></td>
			</tr>
			<?php if ($pines->config->com_user->referral_codes) { ?>
			<tr>
				<td style="font-weight:bold;">Referral Code</td>
				<td><?php echo htmlspecialchars($this->entity->referral_code); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php if (in_array('address', $pines->config->com_user->user_fields)) { ?>
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
	<?php if (in_array('additional_addresses', $pines->config->com_user->user_fields) && $this->entity->addresses) { ?>
	<h3 style="margin:10px 0;">Additional Addresses</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<tr>
				<th>Type</th>
				<th>Address 1</th>
				<th>Address 2</th>
				<th>City</th>
				<th>State</th>
				<th>Zip</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->entity->addresses as $cur_address) { ?>
			<tr>
				<td><?php echo htmlspecialchars($cur_address['type']); ?></td>
				<td><?php echo htmlspecialchars($cur_address['address_1']); ?></td>
				<td><?php echo htmlspecialchars($cur_address['address_2']); ?></td>
				<td><?php echo htmlspecialchars($cur_address['city']); ?></td>
				<td><?php echo htmlspecialchars($cur_address['state']); ?></td>
				<td><?php echo htmlspecialchars($cur_address['zip']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } ?>
</div>
<?php } if (in_array('attributes', $pines->config->com_user->user_fields) && $this->entity->attributes) { ?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Attributes</h3>
	<table class="table table-bordered" style="clear:both;">
		<thead>
			<tr><th>Name</th><th>Value</th></tr>
		</thead>
		<tbody>
			<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
			<tr><td><?php echo htmlspecialchars($cur_attribute['name']); ?></td><td><?php echo htmlspecialchars($cur_attribute['value']); ?></td></tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php } } ?>