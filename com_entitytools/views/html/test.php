<?php
/**
 * Displays the results of an entity manager test.
 *
 * @package Components
 * @subpackage entitytools
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Entity Manager Tester';
$total_time = $this->time_end - $this->time_start;
$last_time = $this->time_start;
?>
<p>This entity manager tester will test the current entity manager for required
functionality. If the entity manager fails any of the tests, it is not
considered to be a compatible entity manager. Please note that this tester does
not test all aspects of an entity manager, and even if it passes, it may still
have bugs.</p>
<?php if ($this->error) { ?>
	<p>
		Error: Either there is no entity manager installed, or it hasn't
		registered itself as the system's entity manager! Test cannot continue!
	</p>
<?php } else { ?>
<div style="font-family: monospace; font-size: .9em; margin-left: 35px; margin-bottom: 6px; margin-top: 6px;">Test is starting...
	<ol>
	<?php foreach ($this->tests as $key => $cur_test) { ?>
		<li style="white-space: pre;"><?php echo str_pad($cur_test[2], 52, ' '); ?><?php echo ($cur_test[0]) ? '<span style="color: green;">[PASS]</span>' : '<span style="color: red;">[<a id="p_muid_'.$key.'" href="javascript:void(0);">FAIL</a>]</span>'; ?> <?php printf('%5.5f', $cur_test[1] - $last_time); ?>s <?php printf('%05.2f', ($cur_test[1] - $last_time) / $total_time * 100); ?>%</li>
	<?php
	if (!$cur_test[0] && isset($cur_test[3])) {
		?>
		<div id="p_muid_<?php echo htmlspecialchars($key); ?>_details" title="Details" style="display: none;">
			<pre style="width: 100%; height: 100%; overflow: auto;"><?php var_dump($cur_test[3]); ?></pre>
		</div>
		<script type="text/javascript">
			pines(function(){
				var dialog = $("#p_muid_<?php echo htmlspecialchars($key); ?>_details")
				.dialog({
					modal: true,
					width: 800,
					height: 600,
					autoOpen: false
				});
				$("#p_muid_<?php echo htmlspecialchars($key); ?>").click(function(){
					dialog.dialog("open");
					return false;
				});
			});
		</script>
		<?php
	}
	$last_time = $cur_test[1];
	} ?>
	</ol>
	The test is now complete. Test time was <?php echo htmlspecialchars($total_time); ?>s.
</div>
<?php } ?>