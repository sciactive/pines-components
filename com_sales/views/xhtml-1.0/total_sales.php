<?php
/**
 * Provides a form for viewing sales totals.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Totals';
$this->note = 'Use this form to see the sales totals for a given time period and location.';
?>
<div class="pform">
	<div class="element heading">
		<h1>Date</h1>
	</div>
	<div class="element">
		<label><span class="label">Location</span>
			<select class="field" id="location" name="location">
				<option value="current">-- Current --</option>
				<?php if ($this->show_all) { ?>
				<option value="all">-- All --</option>
				<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations); ?>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#date_start").datepicker({
					dateFormat: "yy-mm-dd"
				});
			});
			// ]]>
		</script>
		<label><span class="label">Start Date</span>
			<input class="field" type="text" id="date_start" name="date_start" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#date_end").datepicker({
					dateFormat: "yy-mm-dd"
				});
			});
			// ]]>
		</script>
		<label><span class="label">End Date</span>
			<input class="field" type="text" id="date_end" name="date_end" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
	<div class="element buttons">
		<script type="text/javascript">
			// <![CDATA[
			var com_sales_location;
			var com_sales_date_start;
			var com_sales_date_end;
			var com_sales_result_totals;

			$(function(){
				com_sales_location = $("#location");
				com_sales_date_start = $("#date_start");
				com_sales_date_end = $("#date_end");
				com_sales_result_totals = $("#result_totals");

				$("#retrieve_totals").click(function(){
					var loader;
					$.ajax({
						url: "<?php echo pines_url('com_sales', 'totaljson'); ?>",
						type: "POST",
						dataType: "json",
						data: {"location": com_sales_location.val(), "date_start": com_sales_date_start.val(), "date_end": com_sales_date_end.val()},
						beforeSend: function(){
							loader = pines.alert('Retrieving totals from server...', 'Sales Totals', 'icon picon_16x16_animations_throbber', {pnotify_hide: false});
						},
						complete: function(){
							loader.pnotify_remove();
						},
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to retrieve totals:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("No sales data was returned.");
								return;
							}
							// Handle return data.
						}
					});
				});
			});
			// ]]>
		</script>
		<button id="retrieve_totals" class="button ui-state-default ui-corner-all" onmouseover="$(this).addClass('ui-state-hover');" onmouseout="$(this).removeClass('ui-state-hover');">Retrieve</button>
	</div>
	<div id="result_totals"></div>
</div>