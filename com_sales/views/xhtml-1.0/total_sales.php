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
<script type="text/javascript">
	// <![CDATA[

	$(document).ready(function(){
		// TODO: Ajax calls to get sales data.
	});

	// ]]>
</script>
<div class="pform">
	<div class="element heading">
		<h1>Date</h1>
	</div>
	<div class="element">
		<label><span class="label">Location</span>
			<select class="field" id="location" name="location">
				<option value="current">-- Current --</option>
				<option value="all">-- All --</option>
				<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations); ?>
			</select></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(document).ready(function(){
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
			$(document).ready(function(){
				$("#date_end").datepicker({
					dateFormat: "yy-mm-dd"
				});
			});
			// ]]>
		</script>
		<label><span class="label">End Date</span>
			<input class="field" type="text" id="date_end" name="date_end" size="24" value="<?php echo date('Y-m-d'); ?>" /></label>
	</div>
</div>