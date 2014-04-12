<?php
/**
 * A view to load the employee selector.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
<?php if ($pines->config->com_hrm->no_autocomplete_employee) { ?>
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_hrm/includes/<?php echo ($pines->config->debug_mode ? 'jquery.employeeselect.noauto.js' : 'jquery.employeeselect.noauto.min.js'); ?>");
<?php } else { ?>
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_hrm/includes/<?php echo ($pines->config->debug_mode ? 'jquery.employeeselect.js' : 'jquery.employeeselect.min.js'); ?>");
<?php } ?>
	pines.com_hrm_autoemployee_url = <?php echo json_encode(pines_url('com_hrm', 'employee/search')); ?>;
</script>