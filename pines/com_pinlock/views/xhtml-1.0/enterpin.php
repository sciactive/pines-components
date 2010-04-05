<?php
/**
 * Provide a form for the user to enter their PIN.
 *
 * @package Pines
 * @subpackage com_pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Verify Access';
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		$("#com_pinlock__login input[name=pin]").focus();
	});
	// ]]>
</script>
<form class="pform" id="com_pinlock__login" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
	<div class="element">
		<label>
			<span class="label">PIN</span>
			<span class="note">Please enter your PIN to continue.</span>
			<input class="field ui-widget-content" type="password" name="pin" size="24" />
		</label>
	</div>
	<div class="element buttons">
		<input type="hidden" name="option" value="<?php echo $this->orig_component; ?>" />
		<input type="hidden" name="action" value="<?php echo $this->orig_action; ?>" />
		<input type="hidden" name="sessionid" value="<?php echo $this->orig_sessionid; ?>" />
		<input type="hidden" name="com_pinlock_continue" value="true" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Continue" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="reset" name="reset" value="Reset" />
	</div>
</form>