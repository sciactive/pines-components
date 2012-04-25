<?php
/**
 * Include custom CSS.
 *
 * @package Components
 * @subpackage content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
if (!$pines->com_content->get_custom_css()) {
	$this->detach();
	return;
}
?>
<script type="text/javascript">
	<?php foreach ($pines->com_content->get_custom_css() as $cur_file) { ?>
	pines.loadcss(<?php echo json_encode($pines->config->location.$cur_file); ?>);
	<?php } ?>
</script>