<?php
/**
 * Provides a file manager.
 *
 * @package Components\elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'File Manager';
$pines->com_elfinder->load();
?>
<script type="text/javascript">
	pines(function(){
		<?php if (!$this->ckeditor) { ?>
		$(".com_elfinder_finder").elfinder({
			url: <?php echo json_encode(pines_url('com_elfinder', 'connector')); ?>,
			docked: false,
			height: <?php echo json_encode((int) $pines->config->com_elfinder->default_height); ?>
		});
		<?php } else { ?>
		var funcNum = window.location.search.replace(/^.*CKEditorFuncNum=(\d+).*$/, "$1");
		var langCode = window.location.search.replace(/^.*langCode=([a-z]{2}).*$/, "$1");

		$(".com_elfinder_finder").css({"margin-left": "12px", "margin-right": "12px"}).elfinder({
			url: <?php echo json_encode(pines_url('com_elfinder', 'connector')); ?>,
			docked: false,
			height: <?php echo json_encode((int) $pines->config->com_elfinder->default_height); ?>,
			lang: langCode,
			getFileCallback: function(file){
				window.opener.CKEDITOR.tools.callFunction(funcNum, <?php echo json_encode($this->absolute ? preg_replace('/^(https?:\/\/[^\/]+).*$/i', '$1', $pines->config->full_location) : ''); ?>+file);
				window.close();
			}
		});
		<?php } ?>
	});
</script>
<div class="com_elfinder_finder"></div>