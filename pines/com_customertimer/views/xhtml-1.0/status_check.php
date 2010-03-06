<?php
/**
 * Checks and notifies about customer timer status.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){pines.com_customertimer = {
		"ppm": <?php echo (int) $pines->config->com_customertimer->ppm; ?>,
		"level_critical": <?php echo (int) $pines->config->com_customertimer->level_critical; ?>,
		"level_warning": <?php echo (int) $pines->config->com_customertimer->level_warning; ?>,
		"status_url": "<?php echo pines_url('com_customertimer', 'status_json'); ?>",
		"status_page_url": "<?php echo pines_url('com_customertimer', 'status'); ?>"
	};});
	// ]]>
</script>
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_customertimer/includes/status_check.js"></script>