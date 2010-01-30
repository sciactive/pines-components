<?php
/**
 * Provides a form for the user to edit a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New User' : 'Editing ['.htmlentities($this->entity->username).']';
$this->note = 'Provide user details in this form.';
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var password = $("#user_details [name=password]");
		var password2 = $("#user_details [name=password2]");
		$("#user_details").submit(function(){
			if (password.val() != password2.val()) {
				alert("Your passwords do not match.");
				return false;
			}
			return true;
		});
	});
	// ]]>
</script>
<form class="pform" method="post" id="user_details" action="<?php echo pines_url('com_user', 'saveuser'); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
			<?php if (isset($this->entity->uid)) { ?>
		<span>Created By: <span class="date"><?php echo $config->user_manager->get_username($this->entity->uid); ?></span></span>
		<br />
			<?php } ?>
		<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
		<br />
		<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
	</div>
	<?php } ?>
	<div class="element">
		<label><span class="label">Username</span>
			<input class="field ui-widget-content" type="text" name="username" size="24" value="<?php echo $this->entity->username; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Name</span>
			<input class="field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Email</span>
			<input class="field ui-widget-content" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Phone</span>
			<input class="field ui-widget-content" type="text" name="phone" size="24" value="<?php echo pines_phone_format($this->entity->phone); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="element">
		<label><span class="label">Fax</span>
			<input class="field ui-widget-content" type="text" name="fax" size="24" value="<?php echo pines_phone_format($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<div class="element">
		<label><span class="label">Timezone</span>
			<span class="note">This overrides the primary group's timezone.</span>
			<select class="field ui-widget-content" name="timezone" size="1">
				<option value="">--Inherit From Group--</option>
				<?php $tz = DateTimeZone::listIdentifiers();
				sort($tz);
				foreach ($tz as $cur_tz) { ?>
				<option value="<?php echo $cur_tz; ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo $cur_tz; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label"><?php if (isset($this->entity->guid)) echo 'Update '; ?>Password</span>
			<?php if (is_null($this->entity->guid)) {
				echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
			} else {
				echo '<span class="note">Leave blank, if not changing.</span>';
			} ?>
			<input class="field ui-widget-content" type="password" name="password" size="24" /></label>
	</div>
	<div class="element">
		<label><span class="label">Repeat Password</span>
			<input class="field ui-widget-content" type="password" name="password2" size="24" /></label>
	</div>
	<?php if ( $this->display_default_components ) { ?>
		<div class="element">
			<label><span class="label">Default Component</span>
				<span class="note">This component will be responsible for the user's home page.</span>
				<select class="field ui-widget-content" name="default_component">
						<?php foreach ($this->default_components as $cur_component) { ?>
					<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->default_component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo $cur_component; ?></option>
						<?php } ?>
				</select></label>
		</div>
	<?php } ?>
	
	<div class="element heading">
		<h1>Location</h1>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				var addresses = $("#addresses");
				var address_us = $("#address_us");
				var address_international = $("#address_international");
				$("#user_details [name=address_type]").change(function(){
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
		<span class="label">Address Type</span>
		<label><input class="field ui-widget-content" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
		<label><input class="field ui-widget-content" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
	</div>
	<div id="address_us" style="display: none;">
		<div class="element">
			<label><span class="label">Address 1</span>
				<input class="field ui-widget-content" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Address 2</span>
				<input class="field ui-widget-content" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
		</div>
		<div class="element">
			<span class="label">City, State</span>
			<input class="field ui-widget-content" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
			<select name="state">
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
						'WY' => 'Wyoming'
					) as $key => $cur_state) { ?>
				<option value="<?php echo $key; ?>"<?php echo $this->entity->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="element">
			<label><span class="label">Zip</span>
				<input class="field ui-widget-content" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
		</div>
	</div>
	<div id="address_international" style="display: none;">
		<div class="element full_width">
		<label><span class="label">Address</span>
			<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
		</div>
	</div>

	<?php if ( $this->display_groups ) { ?>
		<div class="element heading">
			<h1>Groups</h1>
		</div>
		<?php if (empty($this->group_array)) { ?>
		<div class="element">
			<span class="label">There are no groups to display.</span>
		</div>
		<?php } else { ?>
		<div class="element">
			<label><span class="label">Primary Group</span>
				<select class="field ui-widget-content" name="gid" size="1">
					<option value="null">-- No Primary Group --</option>
							<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->gid); ?>
				</select></label>
		</div>
		<div class="element">
			<label><span class="label">Groups</span>
				<span class="note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
				<select class="field ui-widget-content" name="groups[]" multiple="multiple" size="6">
							<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->groups); ?>
				</select></label>
		</div>
		<?php }
	} ?>
	
	<?php if ( $this->display_abilities ) { ?>
	<div class="element heading">
		<h1>Abilities</h1>
		<input type="hidden" name="abilities" value="true" />
	</div>
	<div class="element">
		<span class="label">Inherit</span>
		<label>
			<input class="field ui-widget-content" type="checkbox" name="inherit_abilities" value="ON" <?php echo ($this->entity->inherit_abilities ? 'checked="checked" ' : ''); ?>/>
			&nbsp;Inherit additional abilities from groups.
		</label>
	</div>
		<?php foreach ($this->sections as $cur_section) {
			$section_abilities = $config->ability_manager->get_abilities($cur_section);
			if ( count($section_abilities) ) { ?>
	<div class="element"><span class="label">Abilities for <em><?php echo $cur_section; ?></em></span>
		<div class="group">
			<?php foreach ($section_abilities as $cur_ability) { ?>
			<label>
				<input class="field ui-widget-content" type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>" <?php echo (array_search("{$cur_section}/{$cur_ability['ability']}", $this->entity->abilities) !== false) ? 'checked="checked" ' : ''; ?>/>
				&nbsp;<?php echo "{$cur_ability['title']} <small>({$cur_ability['description']})</small>"; ?>
			</label>
			<br />
			<?php } ?>
		</div>
	</div>
			<?php }
		}
	} ?>

	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_user', 'listusers'); ?>';" value="Cancel" />
	</div>
</form>