<?php
/**
 * Provides a printable payment form.
 *
 * @package Pines
 * @subpackage com_authnet
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<form class="pform" method="post" action="">
	<div class="element">
		<label><span class="label">Cardholder First Name</span>
			<input class="field" type="text" name="name_first" value="<?php echo $this->name_first; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Cardholder Last Name</span>
			<input class="field" type="text" name="name_last" value="<?php echo $this->name_last; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Cardholder Address</span>
			<input class="field" type="text" name="address" value="<?php echo $this->address; ?>" /></label>
	</div>
	<div class="element">
		<span class="label">Cardholder State, Zip</span>
		<select class="field" name="state">
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
			<option value="<?php echo $key; ?>"<?php echo $this->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
			<?php } ?>
		</select>
		<input class="field" type="text" name="zip" size="5" value="<?php echo $this->zip; ?>" />
	</div>
	<div class="element">
		<label><span class="label">Card Number</span>
			<input class="field" type="text" name="card_number" value="<?php echo $this->card_number; ?>" /></label>
	</div>
	<div class="element">
		<span class="label">Expiration Date, CID</span>
		<select class="field" name="card_exp_month">
			<?php foreach (array(
					'01' => '01 January',
					'02' => '02 February',
					'03' => '03 March',
					'04' => '04 April',
					'05' => '05 May',
					'06' => '06 June',
					'07' => '07 July',
					'08' => '08 August',
					'09' => '09 September',
					'10' => '10 October',
					'11' => '11 November',
					'12' => '12 December'
				) as $key => $value) { ?>
			<option value="<?php echo $key; ?>"<? echo $this->card_exp_month == $key ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option>
			<?php } ?>
		</select>
		<select class="field" name="card_exp_year">
			<?php for ($i = 0; $i <= 25; $i++) { ?>
			<option value="<?php echo date('y', strtotime("+$i years")); ?>"<? echo $this->card_exp_year == date('y', strtotime("+$i years")) ? ' selected="selected"' : ''; ?>><?php echo date('y', strtotime("+$i years")); ?></option>
			<?php } ?>
		</select>
		<input class="field" type="password" name="cid" size="3" value="<?php echo $this->cid; ?>" />
	</div>
</form>