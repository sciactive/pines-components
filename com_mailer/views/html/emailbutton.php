<?php
/**
 * Generate the Email Button
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited'); ?>

<div class="hide mailer-email-button"><div class="ui-pgrid-toolbar-sep ui-state-default add-item"><div class="ui-pgrid-toolbar-blank">-</div></div><button type="button" class="ui-pgrid-toolbar-button ui-state-default ui-corner-all add-item" data-name="send_email" style="display:none;" data-show="1000" onclick="javascript: window.mailer_send_email_modal();"><span class="picon picon-mail-forwarded">Send Email</span></button></div>
<div class="mailer-email-modal-container">
	<div class="modal hide fade in" data-backdrop="static" data-keyboard="false" data-url="<?php echo pines_url('com_mailer', 'sendmail_template'); ?>">
		<div class="modal-header">
			<h4 class="text-center" style="text-transform: uppercase;">Send <span class="num-rows"></span> Email(s)</h4>
		</div>
		<div class="modal-body">
			<div class="before-send">
				<h3 class="text-center text-success">
					<span class="sending-sent">Sending</span> as
					<span class="email-container">
						<span class="text-info email-prefix-label"><?php echo $this->email_prefix; ?></span><input value="<?php echo $this->email_prefix; ?>" type="text" class="text-info well email-prefix hide"/><span class="text-info email-suffix"><?php echo $this->email_suffix; ?></span>
						<?php if ($this->edit_email) { ?>
						<i class="text-success icon-pencil email-prefix-edit" style="cursor: pointer;"></i>
						<?php } ?>
					</span>
					<input type="hidden" name="sender" value="<?php echo $this->email_prefix.$this->email_suffix; ?>"/>
				</h3>
				<hr style="margin: 5px 0;"/>
				<h3>Pick a Template</h3>
				<p class="text-center">
					<select class="mailer-template-select" name="template" autocomplete="off">
						<option value="" selected="selected" data-description="Choose a template to read a description.">Choose Template</option>
						<?php
						foreach ($this->email_templates as $key => $cur_template) {
							$blocked_classes = implode(' ', $cur_template['blocked_classes']);
							?>
							<option value="<?php echo $key; ?>" class="<?php echo htmlspecialchars($blocked_classes); ?>" data-description="<?php echo htmlspecialchars($cur_template['description']); ?>"><?php echo htmlspecialchars($cur_template['name']); ?></option>
						<?php } ?>
						<!--					<option value="" selected="selected" data-description="Choose a template to read a description.">Choose Template</option>
											<option value="0" data-description="Send the customer their Approval/Pre-Approval Information.">Application Approval</option>
											<option value="1" data-description="Send the Customer a PDF of their Contract">Contract PDF</option>
											<option value="2" data-description="Request the customer to upload a LES/Paystub">Request Upload LES</option>
											<option value="3" data-description="Remind the customer to upload a LES/Paystub">Remind Upload LES</option>
											<option value="4" data-description="Remind the customer to pay their loan.">Loan Delinquency</option>-->
					</select>
				</p>
				<div class="text-center" style="padding:0 10%;"><p class="mailer-template-description text-center well"></p></div>
				<div class="mailer-custom-message">
					<h3>Append a Custom Message (Optional)</h3>
					<div style="width: 80%; margin:auto;" class="text-center">
						<div style="padding-right: 12px;">
							<textarea placeholder="Write a custom message here." style="width: 100%;" name="custom_message"></textarea>
						</div>
					</div>
				</div>
				<hr style="margin: 5px 0;"/>
				<div class="text-center"><button class="btn-large btn btn-info send-email-btn"><i class="icon-envelope"></i> Send Email</button></div>
				<div class="text-center" style="margin-top:10px;"><h5 class="text-error send-status"></h5></div>
			</div>
			<div class="results-container hide">
				<div class="partial-sent hide">
					<h3>Out of <span class="num-rows"></span> email(s),</h3>
					<hr style="margin: 2px;">
					<div class="results" style="margin-left: 20px;">
						<h4 class="partial-result-item text-success"><i class="icon-ok"></i> <span class="partial-sent-num"></span> was/were sent!</h4>
						<h4 class="partial-result-item text-warning"><i class="icon-warning-sign"></i> <span class="partial-skipped-num"></span> was/were not applicable to this template and not sent.</h4>
						<h4 class="partial-result-item text-error"><i class="icon-remove"></i> <span class="partial-failed-num"></span> failed to send.</h4>
					</div>
				</div>
				<div class="full-sent text-center hide">
					<h4 class="text-success"><i class="icon-ok"></i> <span class="num-rows"></span> email(s) were sent!</h4>
				</div>
				<div class="full-failed text-center hide">
					<h4 class="text-error"><i class="icon-remove"></i> <span class="num-rows"></span> email(s) failed to send!</h4>
				</div>
				<div class="progress-container">
					<div class="progress progress-striped">
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<input type="hidden" name="guids"/>
			<input type="hidden" name="entity_class"/>
			<a class="btn cancel-btn" data-dismiss="modal" href="javascript:void(0);">Cancel</a>
			<a class="btn done-btn btn-info" data-dismiss="modal" href="javascript:void(0);">Done</a>
		</div>
	</div>
</div>