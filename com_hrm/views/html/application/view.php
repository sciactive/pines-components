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
$this->title = 'Employment Application ['.htmlspecialchars($this->entity->name).']';
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_offer .employee_app_style .pf-label, #p_muid_offer .employee_app_style .pf-note {
		font-weight: bold;
		text-align: right;
	}
	/* ]]> */
</style>
<div class="pf-form pf-form-twocol" id="p_muid_offer">
	<div class="pf-element pf-full-width">
		<span class="pf-label">Status:</span>
		<span class="pf-field">
			<strong><?php echo htmlspecialchars(ucwords($this->entity->status)); ?></strong>
		</span>
	</div>
	<?php if (!empty($this->entity->notes)) { ?>
		<div class="pf-element pf-heading">
			<h1>Notes</h1>
		</div>
		<?php foreach ($this->entity->notes as $cur_note) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label"><?php echo htmlspecialchars($cur_note['user']->name); ?></span>
			<span class="pf-note"><?php echo format_date($cur_note['date']); ?></span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_note['note']); ?></span>
		</div>
		<?php }
	} ?>
	<div class="employee_app_style">
		<div class="pf-element pf-heading">
			<h1>Credit Application Setup Information</h1>
		</div>
		<div class="pf-element">
			<span class="pf-label">Name:</span>
			<span class="pf-field"><?php echo htmlspecialchars($this->entity->name); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Email:</span>
			<span class="pf-field">
				<?php echo htmlspecialchars($this->entity->email); ?>
			</span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Phone:</span>
			<span class="pf-field">
				<?php echo format_phone($this->entity->phone); ?>
			</span>
		</div>
		<div class="pf-element">
			<span class="pf-label">SSN:</span>
			<span class="pf-field">
				<?php echo htmlspecialchars($this->entity->ssn); ?>
			</span>
		</div>
		<div class="pf-element pf-heading">
			<h1>Education</h1>
		</div>
		<?php foreach ($this->entity->education as $cur_school) { ?>
		<div class="pf-element">
			<span class="pf-label">Name of Institution:</span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_school['name'].' ('.$cur_school['type'].')'); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Area of Interest:</span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_school['major']); ?></span>
		</div>
		<br class="pf-clearing" />
		<?php } ?>
		<div class="pf-element pf-heading">
			<h1>Employment History</h1>
		</div>
		<?php foreach ($this->entity->employment as $cur_employer) { ?>
		<div class="pf-element">
			<span class="pf-label">Position:</span>
			<span class="pf-field"><?php echo htmlspecialchars(ucwords($cur_employer['position'])); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Timeline:</span>
			<span class="pf-field"><?php echo format_date($cur_employer['start'], 'date_short').' - '.format_date($cur_employer['end'], 'date_short'); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Company:</span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_employer['company']); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Phone:</span>
			<span class="pf-field"><?php echo format_phone($cur_employer['phone']); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Reason for Leaving:</span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_employer['reason']); ?></span>
		</div>
		<br class="pf-clearing" />
		<br class="pf-clearing" />
		<?php } ?>
		<div class="pf-element pf-heading">
			<h1>References</h1>
		</div>
		<?php foreach ($this->entity->references as $cur_reference) { ?>
		<div class="pf-element">
			<span class="pf-label">Name:</span>
			<span class="pf-field"><?php echo htmlspecialchars(ucwords($cur_reference['name'])); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Phone:</span>
			<span class="pf-field"><?php echo format_phone($cur_reference['phone']); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Company:</span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_reference['company']); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Occupation:</span>
			<span class="pf-field"><?php echo htmlspecialchars($cur_reference['occupation']); ?></span>
		</div>
		<br class="pf-clearing" />
		<br class="pf-clearing" />
		<?php } ?>
		<div class="pf-element pf-heading">
			<h1>References</h1>
		</div>
		<div class="pf-element">
			<span class="pf-label">File Location:</span>
			<span class="pf-field"><?php echo htmlspecialchars($this->entity->resume['path']); ?></span>
		</div>
	</div>
</div>