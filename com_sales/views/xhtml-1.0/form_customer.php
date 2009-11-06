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
$this->show_title = false;
?>
<form class="pform" method="post" id="customer_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
<fieldset>
    <legend><?php echo $this->title; ?></legend>
    <div class="element heading">
        <p>Provide customer details in this form.</p>
    </div>
    <div class="element">
        <label><span class="label">Name</span>
        <input class="field" type="text" name="name" size="20" value="<?php echo $this->entity->name; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Email</span>
        <input class="field" type="text" name="email" size="20" value="<?php echo $this->entity->email; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label"><?php if (!is_null($this->id)) echo 'Update '; ?>Password</span>
        <?php if (is_null($this->id)) {
            echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
        } else {
            echo '<span class="note">Leave blank, if not changing.</span>';
        } ?>
        <input class="field" type="password" name="password" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Repeat Password</span>
        <input class="field" type="password" name="password2" size="20" /></label>
    </div>
    <div class="element">
        <label><span class="label">Company</span>
        <input class="field" type="text" name="company" size="20" value="<?php echo $this->entity->company; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Job Title</span>
        <input class="field" type="text" name="job_title" size="20" value="<?php echo $this->entity->job_title; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Address 1</span>
        <input class="field" type="text" name="address_1" size="20" value="<?php echo $this->entity->address_1; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Address 2</span>
        <input class="field" type="text" name="address_2" size="20" value="<?php echo $this->entity->address_2; ?>" /></label>
    </div>
    <div class="element">
        <span class="label">City, State</span>
        <input class="field" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
        <input class="field" type="text" name="state" size="2" value="<?php echo $this->entity->state; ?>" />
    </div>
    <div class="element">
        <label><span class="label">Zip</span>
        <input class="field" type="text" name="zip" size="20" value="<?php echo $this->entity->zip; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Home Phone</span>
        <input class="field" type="text" name="phone_home" size="20" value="<?php echo $this->entity->phone_home; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Work Phone</span>
        <input class="field" type="text" name="phone_work" size="20" value="<?php echo $this->entity->phone_work; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Cell Phone</span>
        <input class="field" type="text" name="phone_cell" size="20" value="<?php echo $this->entity->phone_cell; ?>" /></label>
    </div>
    <div class="element">
        <label><span class="label">Fax</span>
        <input class="field" type="text" name="fax" size="20" value="<?php echo $this->entity->fax; ?>" /></label>
    </div>
	<div class="element buttons">
        <?php if ( !is_null($this->id) ) { ?>
        <input type="hidden" name="id" value="<?php echo $this->id; ?>" />
        <?php } ?>
        <input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
        <input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listcustomers'); ?>';" value="Cancel" />
    </div>
</fieldset>
</form>