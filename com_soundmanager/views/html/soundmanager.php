<?php
/**
 * A view to load SoundManager 2.
 *
 * @package Components
 * @subpackage soundmanager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	window.SM2_DEFER = true;
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_soundmanager/includes/soundmanager/script/<?php echo $pines->config->debug_mode ? 'soundmanager2.js' : 'soundmanager2-nodebug-jsmin.js'; ?>");
	pines.load(function(){
		window.soundManager = new SoundManager();
		soundManager.url = "<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_soundmanager/includes/soundmanager/swf/";
		soundManager.flashVersion = 9;
		soundManager.useFlashBlock = false;
		soundManager.beginDelayedInit();
	});
</script>