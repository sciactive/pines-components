<?php
/**
 * Provides a form to register a new user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'New User Registration';
$this->note = 'Please fill in your account details.';
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_user', 'registeruser')); ?>">
	<div id="p_muid_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<?php if (in_array('address', $pines->config->com_user->reg_fields)) { ?>
			<li><a href="#p_muid_tab_location">Address</a></li>
			<?php } ?>
		</ul>
		<div id="p_muid_tab_general">
			<div style="float: right;"><span class="pf-required">*</span> Required Field</div>
			<?php if (in_array('name', $pines->config->com_user->reg_fields)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">First Name <span class="pf-required">*</span></span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_first" size="24" value="<?php echo htmlspecialchars($this->entity->name_first); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Middle Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_middle" size="24" value="<?php echo htmlspecialchars($this->entity->name_middle); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Last Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_last" size="24" value="<?php echo htmlspecialchars($this->entity->name_last); ?>" /></label>
			</div>
			<?php } if (in_array('email', $pines->config->com_user->reg_fields)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Email <span class="pf-required">*</span></span>
					<input class="pf-field ui-widget-content ui-corner-all" type="email" name="email" size="24" value="<?php echo htmlspecialchars($this->entity->email); ?>" /></label>
			</div>
			<?php } if (in_array('phone', $pines->config->com_user->reg_fields)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="phone" size="24" value="<?php echo format_phone($this->entity->phone); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<?php } if (in_array('fax', $pines->config->com_user->reg_fields)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="fax" size="24" value="<?php echo format_phone($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<?php } if (in_array('timezone', $pines->config->com_user->reg_fields)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Timezone</span>
					<span class="pf-note">This overrides the primary group's timezone.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="timezone" size="1">
						<option value="">--Default--</option>
						<?php $tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) { ?>
						<option value="<?php echo htmlspecialchars($cur_tz); ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_tz); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<?php if (in_array('address', $pines->config->com_user->reg_fields)) { ?>
		<div id="p_muid_tab_location">
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var address_us = $("#p_muid_address_us");
						var address_international = $("#p_muid_address_international");
						$("#p_muid_form [name=address_type]").change(function(){
							var address_type = $(this);
							if (address_type.is(":checked") && address_type.val() == "us") {
								address_us.show();
								address_international.hide();
							} else if (address_type.is(":checked") && address_type.val() == "international") {
								address_international.show();
								address_us.hide();
							}
						}).change();
					});
					// ]]>
				</script>
				<span class="pf-label">Address Type</span>
				<label><input class="pf-field" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="p_muid_address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_1" size="24" value="<?php echo htmlspecialchars($this->entity->address_1); ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_2" size="24" value="<?php echo htmlspecialchars($this->entity->address_2); ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="city" size="15" value="<?php echo htmlspecialchars($this->entity->city); ?>" />
					<select class="pf-field ui-widget-content ui-corner-all" name="state">
						<option value="">None</option>
						<?php foreach (array(
								'AL' => 'Alabama',
								'AK' => 'Alaska',
								'AZ' => 'Arizona',
								'AR' => 'Arkansas',
								'CA' => 'California',
								'CO' => 'Colorado',
								'CT' => 'Connecticut',
								'DE' => 'Delaware',
								'DC' => 'DC',
								'FL' => 'Florida',
								'GA' => 'Georgia',
								'HI' => 'Hawaii',
								'ID' => 'Idaho',
								'IL' => 'Illinois',
								'IN' => 'Indiana',
								'IA' => 'Iowa',
								'KS' => 'Kansas',
								'KY' => 'Kentucky',
								'LA' => 'Louisiana',
								'ME' => 'Maine',
								'MD' => 'Maryland',
								'MA' => 'Massachusetts',
								'MI' => 'Michigan',
								'MN' => 'Minnesota',
								'MS' => 'Mississippi',
								'MO' => 'Missouri',
								'MT' => 'Montana',
								'NE' => 'Nebraska',
								'NV' => 'Nevada',
								'NH' => 'New Hampshire',
								'NJ' => 'New Jersey',
								'NM' => 'New Mexico',
								'NY' => 'New York',
								'NC' => 'North Carolina',
								'ND' => 'North Dakota',
								'OH' => 'Ohio',
								'OK' => 'Oklahoma',
								'OR' => 'Oregon',
								'PA' => 'Pennsylvania',
								'RI' => 'Rhode Island',
								'SC' => 'South Carolina',
								'SD' => 'South Dakota',
								'TN' => 'Tennessee',
								'TX' => 'Texas',
								'UT' => 'Utah',
								'VT' => 'Vermont',
								'VA' => 'Virginia',
								'WA' => 'Washington',
								'WV' => 'West Virginia',
								'WI' => 'Wisconsin',
								'WY' => 'Wyoming',
								'AA' => 'Armed Forces (AA)',
								'AE' => 'Armed Forces (AE)',
								'AP' => 'Armed Forces (AP)'
							) as $key => $cur_state) { ?>
						<option value="<?php echo $key; ?>"<?php echo $this->entity->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="zip" size="24" value="<?php echo htmlspecialchars($this->entity->zip); ?>" /></label>
				</div>
			</div>
			<div id="p_muid_address_international" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
	</div>

	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->url) ) { ?>
		<input type="hidden" name="url" value="<?php echo htmlspecialchars($this->url); ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url()); ?>');" value="Cancel" />
	</div>
</form>