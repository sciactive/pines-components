<?php
/**
 * Add customer interaction.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Add Customer Interaction';
$pines->icons->load();
//$pines->com_customer->load_customer_select();
$module = new module('com_customer', 'customer/select');
echo $module->render();
?>
<div id="p_muid_form">
	<style type="text/css" scoped="scoped">
		#p_muid_form label, #p_muid_form label input {
			display: inline;
		}
		#p_muid_results .status {
			margin-top: 1em;
		}
		#p_muid_results .well {
			padding-bottom: 1px;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			$("#p_muid_customer_name").customerselect();
			$("#p_muid_done").click(function(){
				var customer_name = $("#p_muid_customer_name"),
					name_id = customer_name.val(),
					status_bar = $("#p_muid_status"),
					interaction_type = $("#p_muid_type"),
					comments = $("#p_muid_comments");
				if (name_id.match(/^\s*$/)) {
					alert("Please enter a customer.");
					return;
				}
				$.ajax({
					url: <?php echo json_encode(pines_url('com_customer', 'interaction/add')); ?>,
					type: "POST",
					dataType: "json",
					data: {
						customer: name_id,
						employee: '',
						type: interaction_type.val(),
						status: 'open',
						comments: comments.val()
					},
					beforeSend: function(){
						status_bar.html('');
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data) {
							status_bar.html('<div style="padding-bottom: 10px;"><i class="icon-ok-sign"></i> Success!<?php if (gatekeeper('com_customer/editcustomer')) { ?> <a data-entity="'+pines.safe(parseInt(name_id))+'" data-entity-context="com_customer_customer">View Customer</a>.<?php } ?></div>');
							comments.val('');
							customer_name.val('');
							interaction_type.val('');
						} else
							status_bar.html('<div style="padding-bottom: 10px;"><i class="icon-remove-sign"></i> Error! Please check input.</div>');
					}
				});
			});
		});
	</script>
	<div id="p_muid_status"></div>
	<div id="p_muid_controls" class="clearfix pf-form">
		<div class="pf-element" style="float: left; margin-right: 1em; max-width:100%">
			<label><span class="pf-label">Customer</span>
				<input type="text" style="width:140px;max-width:90%;" name="customer_name" id="p_muid_customer_name" />
			</label>
		</div>
		<div class="pf-element" style="clear:left;max-width:100%">
			<label><span class="pf-label">Interaction Type</span>
				<select id="p_muid_type" name="interaction_type" style="width:140px; max-width:90%;">
					<?php foreach ($pines->config->com_customer->interaction_types as $cur_type) {
						$cur_type = explode(':', $cur_type);
						echo '<option value="'.htmlspecialchars($cur_type[1]).'">'.htmlspecialchars($cur_type[1]).'</option>';
					} ?>
				</select></label>
		</div>
		<div class="pf-element pf-full-width">
			<textarea rows="3" cols="30" style="width:95%;" id="p_muid_comments" name="interaction_comments"></textarea>
		</div>
		<div class="pf-element pf-full-width" style="text-align: right;">
			<button class="btn" type="button" id="p_muid_done">Add</button><br />
		</div>
	</div>
</div>