<?php
/**
 * Provides a form for the user to edit a customer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Customer' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide customer details in this form.';
?>
<form class="pform" method="post" id="customer_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
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
		<label><span class="label">First Name</span>
			<input class="field" type="text" name="name_first" size="24" value="<?php echo $this->entity->name_first; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Last Name</span>
			<input class="field" type="text" name="name_last" size="24" value="<?php echo $this->entity->name_last; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Email</span>
			<input class="field" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Company</span>
			<input class="field" type="text" name="company" size="24" value="<?php echo $this->entity->company; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Job Title</span>
			<input class="field" type="text" name="job_title" size="24" value="<?php echo $this->entity->job_title; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Address 1</span>
			<input class="field" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Address 2</span>
			<input class="field" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
	</div>
	<div class="element">
		<span class="label">City, State</span>
		<input class="field" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
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
			<input class="field" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Cell Phone</span>
			<input class="field" type="text" name="phone_cell" size="24" value="<?php echo $this->entity->phone_cell; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Work Phone</span>
			<input class="field" type="text" name="phone_work" size="24" value="<?php echo $this->entity->phone_work; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Home Phone</span>
			<input class="field" type="text" name="phone_home" size="24" value="<?php echo $this->entity->phone_home; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Fax</span>
			<input class="field" type="text" name="fax" size="24" value="<?php echo $this->entity->fax; ?>" /></label>
	</div>
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listcustomers'); ?>';" value="Cancel" />
	</div>
</form>