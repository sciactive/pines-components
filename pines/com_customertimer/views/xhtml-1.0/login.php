<?php
/**
 * Provides a form for the customer to login.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer Login/Logout';
$this->note = 'Please enter your info, or scan your barcode to login or logout.';
?>
<form class="pform" id="customer_login" name="customer_login" method="post" action="<?php echo pines_url('com_customertimer', 'login'); ?>">
	<script type="text/javascript">
	// <![CDATA[
	var id_box;
	var pw_box;
	$(function(){
		id_box = $("#customer_login [name=id]");
		pw_box = $("#customer_login [name=password]");
		id_box.change(function(){
			if (id_box.val().indexOf("|") > 0) {
				pw_box.val(id_box.val().replace(/^[^|]*\|/, ""));
				id_box.val(id_box.val().replace(/\|.*$/, ""));
			}
		});
		$("#customer_login").submit(function(){
			id_box.change();
		});
		id_box.focus();
	});
	// ]]>
	</script>
	<div class="element">
		<label><span class="label">Customer ID</span>
			<input class="field ui-widget-content" type="password" name="id" size="24" /></label>
	</div>
	<div class="element">
		<label><span class="label">Password</span>
			<input class="field ui-widget-content" type="password" name="password" size="24" /></label>
	</div>
	<div class="element buttons">
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="reset" name="reset" value="Reset" />
	</div>
</form>