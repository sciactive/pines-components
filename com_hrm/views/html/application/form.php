<?php
/**
 * Provides a form to apply for employment.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Apply for Employment' : 'Editing ['.htmlspecialchars($this->entity->name).']';
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .amt_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#p_muid_form button {
		margin: 0;
		padding: 0;
	}
	/* ]]> */
</style>
<script language="JavaScript" type="text/javascript">
	// <![CDATA[
	pines(function(){
		pines.com_hrm_form_clear = function(){
			$('#p_muid_employment').children().remove();
		};

		pines.com_hrm_add_school = function(){
			$('#p_muid_education').append($('#p_muid_school_skel').html().replace(/tmp_/g, ''));
		};

		pines.com_hrm_add_employer = function(){
			$('#p_muid_employment').append($('#p_muid_employer_skel').html().replace(/tmp_/g, ''));
			//$('#p_muid_employment [name=employer_start]').val('woot');
			$('#p_muid_employment .p_muid_date').datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true
			});
		};

		if ($("#p_muid_education").children().size() == 0)
			pines.com_hrm_add_school();

		if ($("#p_muid_employment").children().size() == 0)
			pines.com_hrm_add_employer();

		$(".p_muid_clear input").live('click', function(){
			if (this.value == this.defaultValue)
				this.value = '';
		}).live('blur', function(){
			if (this.value == '')
				this.value = this.defaultValue;
		});
	});
	// ]]>
</script>
<?php
	/*
	var_dump($this->entity->employment);
	exit;
	 */
?>
<form class="pf-form" method="post" id="p_muid_form" enctype="multipart/form-data" action="<?php echo htmlspecialchars(pines_url('com_hrm', 'application/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Desired Position</span>
		<select class="pf-field ui-widget-content ui-corner-all" name="position">
			<?php foreach ($pines->config->com_hrm->employee_departments as $cur_dept) {
			$cur_dept = explode(':', $cur_dept); ?>
			<option value="<?php echo $cur_dept[0]; ?>"<?php echo $this->entity->position == $cur_dept[0] ? ' selected="selected"' : ''; ?>><?php echo $cur_dept[0]; ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">First Name</span>
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
	<div class="pf-element">
		<label><span class="pf-label">Email</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="email" name="email" size="24" value="<?php echo htmlspecialchars($this->entity->email); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Phone</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="phone" size="24" value="<?php echo format_phone($this->entity->phone); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
	</div>
	<?php if ($pines->config->com_hrm->ssn_field) { ?>
	<div class="pf-element">
		<label><span class="pf-label">SSN</span>
			<span class="pf-note">Without dashes.</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="ssn" size="24" value="<?php echo htmlspecialchars($this->entity->ssn); ?>" /></label>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Address</h1>
	</div>
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
	<div class="pf-element pf-heading">
		<h1 style="display: inline;">Education<span style="font-size: 10pt; float: right;"><button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_add_school();"><span class="amt_btn picon picon-list-add"></span> School</button></span></h1>
	</div>
	<div id="p_muid_education">
		<?php foreach ($this->entity->education as $cur_education) { ?>
		<div class="pf-element p_muid_clear">
			<span class="pf-label"><button class="ui-state-default ui-corner-all" type="button" onclick="$(this).parent().parent().remove();"><span class="amt_btn picon picon-list-remove"></span> Remove</button></span>
			<div class="pf-group">
				<select class="pf-field ui-widget-content ui-corner-all" name="school_type[]">
					<option value="college" <?php echo ($cur_education['type'] == 'college') ? 'selected="selected"' : ''; ?>>College</option>
					<option value="trade_school" <?php echo ($cur_education['type'] == 'trade_school') ? 'selected="selected"' : ''; ?>>Trade School</option>
					<option value="high_school" <?php echo ($cur_education['type'] == 'high_school') ? 'selected="selected"' : ''; ?>>High School</option>
				</select>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="school_name[]" size="24" value="<?php echo htmlspecialchars($cur_education['name']); ?>" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="school_major[]" size="18" value="<?php echo htmlspecialchars($cur_education['major']); ?>" />
				<br class="pf-clearing" />
			</div>
		</div>
		<?php } ?>
	</div>
	<div id="p_muid_school_skel" style="display: none;">
		<div class="pf-element p_muid_clear">
			<span class="pf-label"><button class="ui-state-default ui-corner-all" type="button" onclick="$(this).parent().parent().remove();"><span class="amt_btn picon picon-list-remove"></span> Remove</button></span>
			<div class="pf-group">
				<select class="pf-field ui-widget-content ui-corner-all" name="tmp_school_type[]">
					<option value="college">College</option>
					<option value="trade_school">Trade School</option>
					<option value="high_school">High School</option>
				</select>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="tmp_school_name[]" size="24" value="Institution Name" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="tmp_school_major[]" size="18" value="Area of Study" />
				<br class="pf-clearing" />
			</div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h1 style="display: inline;">Previous Employment<span style="font-size: 10pt; float: right;"><button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_add_employer();"><span class="amt_btn picon picon-list-add"></span> Employer</button></span></h1>
	</div>
	<div id="p_muid_employment">
		<?php foreach ($this->entity->employment as $cur_employer) { ?>
		<div class="pf-element p_muid_clear">
			<span class="pf-label"><button class="ui-state-default ui-corner-all" type="button" onclick="$(this).parent().parent().remove();"><span class="amt_btn picon picon-list-remove"></span> Remove</button></span>
			<div class="pf-group">
				<input class="pf-field ui-widget-content ui-corner-all p_muid_date" type="text" name="employer_start[]" size="10" value="<?php echo format_date($cur_employer['start'], 'date_short'); ?>" />
				<input class="pf-field ui-widget-content ui-corner-all p_muid_date" type="text" name="employer_end[]" size="10" value="<?php echo format_date($cur_employer['end'], 'date_short'); ?>" style="margin: 0px;" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="employer_position[]" size="24" value="<?php echo htmlspecialchars($cur_employer['position']); ?>" />
				<br class="pf-clearing" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="employer_company[]" size="25" value="<?php echo htmlspecialchars($cur_employer['company']); ?>" />
				<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="employer_phone[]" size="24" value="<?php echo format_phone($cur_employer['phone']); ?>" />
				<br class="pf-clearing" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="employer_reason[]" size="55" value="<?php echo htmlspecialchars($cur_employer['reason']); ?>" />
				<br class="pf-clearing" />
			</div>
		</div>
		<?php } ?>
	</div>
	<div id="p_muid_employer_skel" style="display: none;">
		<div class="pf-element p_muid_clear">
			<span class="pf-label"><button class="ui-state-default ui-corner-all" type="button" onclick="$(this).parent().parent().remove();"><span class="amt_btn picon picon-list-remove"></span> Remove</button></span>
			<div class="pf-group">
				<input class="pf-field ui-widget-content ui-corner-all p_muid_date" type="text" name="tmp_employer_start[]" size="10" value="Start" />
				<input class="pf-field ui-widget-content ui-corner-all p_muid_date" type="text" name="tmp_employer_end[]" size="10" value="End" style="margin: 0px;" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="tmp_employer_position[]" size="24" value="Position Title" />
				<br class="pf-clearing" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="tmp_employer_company[]" size="25" value="Company Name" />
				<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="tmp_employer_phone[]" size="24" value="Phone Number" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" />
				<br class="pf-clearing" />
				<input class="pf-field ui-widget-content ui-corner-all" type="text" name="tmp_employer_reason[]" size="55" value="Reason for Leaving" />
				<br class="pf-clearing" />
			</div>
		</div>
	</div>
	
	<div class="pf-element pf-heading">
		<h1>References</h1>
	</div>
	<div class="pf-element pf-full-width p_muid_clear">
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_name[]" size="30" value="<?php echo $this->entity->references[0]['name']; ?>" />
		<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="reference_phone[]" size="24" value="<?php echo $this->entity->references[0]['phone']; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" />
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_occupation[]" size="24" value="<?php echo $this->entity->references[0]['occupation']; ?>" />
	</div>
	<div class="pf-element pf-full-width p_muid_clear">
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_name[]" size="30" value="<?php echo $this->entity->references[1]['name']; ?>" />
		<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="reference_phone[]" size="24" value="<?php echo $this->entity->references[1]['phone']; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" />
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_occupation[]" size="24" value="<?php echo $this->entity->references[1]['occupation']; ?>" />
	</div>
	<div class="pf-element pf-full-width p_muid_clear">
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_name[]" size="30" value="<?php echo $this->entity->references[2]['name']; ?>" />
		<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="reference_phone[]" size="24" value="<?php echo $this->entity->references[2]['phone']; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" />
		<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_occupation[]" size="24" value="<?php echo $this->entity->references[2]['occupation']; ?>" />
	</div>
	<?php if (isset($this->entity->resume)) { ?>
	<div class="pf-element">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_form [name=update_resume]").change(function(){
					if ($(this).is(":checked")) {
						$("#p_muid_form [name=uploadedfile]").removeAttr('disabled').removeClass("ui-state-disabled");
					} else {
						$("#p_muid_form [name=uploadedfile]").attr('disabled', 'disabled').addClass("ui-state-disabled");
					}
				}).change();
			});
			// ]]>
		</script>
		<label><input class="pf-field" type="checkbox" name="update_resume" value="ON" /> Change Resume (Currently using '<?php echo htmlspecialchars($this->entity->resume['path']); ?>')</label>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Resume</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="file" size="24" name="uploadedfile" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<?php if (gatekeeper('com_hrm/editapplication')) { ?>
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_hrm', 'application/list')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url()); ?>');" value="Cancel" />
		<?php } ?>
	</div>
</form>