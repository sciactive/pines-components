<?php
/**
 * Include custom CSS.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if (!$pines->com_content->get_custom_css()) {
	$this->detach();
	return;
}
?>
<script type="text/javascript">
	// <![CDATA[
	<?php foreach ($pines->com_content->get_custom_css() as $cur_file) { ?>
	pines.loadcss(<?php echo json_encode($cur_file); ?>);
	<?php } ?>
	// ]]>
</script>