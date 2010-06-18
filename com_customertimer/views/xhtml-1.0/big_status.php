<?php
/**
 * Shows customer timer status.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer Timer Status';
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_customer_status {
		position: fixed;
		top: 0;
		bottom: 0;
		right: 0;
		left: 0;
		z-index: 1000;
		background-color: white;
	}
	#p_muid_customer_status > div {
		position: absolute;
		display: table;
		top: 0;
		left: 0;
		height: 100%;
		width: 100%;
	}
	#p_muid_customer_status > div > div {
		display: table-cell;
		vertical-align: middle;
		text-align: center;
		font-size: 10em;
	}
	#p_muid_customer_status > div.status_ok {
		background-color: darkgreen;
		color: green;
	}
	#p_muid_customer_status > div.status_warning {
		background-color: darkgoldenrod;
		color: goldenrod;
	}
	#p_muid_customer_status > div.status_critical {
		background-color: darkred;
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var customer_status;

	pines(function(){
		customer_status = $("#p_muid_customer_status");
		update_status();
	});

	function update_status() {
		$.ajax({
			url: "<?php echo pines_url('com_customertimer', 'bigstatus_json'); ?>",
			type: "GET",
			dataType: "json",
			complete: function(){
				setTimeout(update_status, 30000);
			},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to refresh the status:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				switch (data) {
					case "ok":
						customer_status.html("<div class=\"status_ok\"><div>OK</div></div>");
						break;
					case "warning":
						customer_status.html("<div class=\"status_warning\"><div>Warning</div></div>");
						break;
					case "critical":
						customer_status.html("<div class=\"status_critical\"><div>Critical</div></div>");
						break;
				}
			}
		});
	}
	// ]]>
</script>
<div id="p_muid_customer_status">Loading, please wait...</div>