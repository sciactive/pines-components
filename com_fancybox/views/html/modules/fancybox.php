<?php
/**
 * A view to build a FancyBox.
 *
 * @package Components
 * @subpackage fancybox
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$pines->com_fancybox->load();

$options = (object) array();

if (isset($this->padding))
	$options->padding = (int) $this->padding;
if (isset($this->margin))
	$options->margin = (int) $this->margin;
if (isset($this->width))
	$options->width = (int) $this->width;
if (isset($this->height))
	$options->height = (int) $this->height;
if (isset($this->minWidth))
	$options->minWidth = (int) $this->minWidth;
if (isset($this->minHeight))
	$options->minHeight = (int) $this->minHeight;
if (isset($this->maxWidth))
	$options->maxWidth = (int) $this->maxWidth;
if (isset($this->maxHeight))
	$options->maxHeight = (int) $this->maxHeight;
if (isset($this->autoSize))
	$options->autoSize = ($this->autoSize == 'true');
if (isset($this->fitToView))
	$options->fitToView = ($this->fitToView == 'true');
if (isset($this->aspectRatio))
	$options->aspectRatio = ($this->aspectRatio == 'true');
if (isset($this->topRatio))
	$options->topRatio = (float) $this->topRatio;
if (isset($this->fixed))
	$options->fixed = ($this->fixed == 'true');
if (isset($this->scrolling) && in_array($this->scrolling, array('auto', 'yes', 'no')))
	$options->scrolling = $this->scrolling;
if (isset($this->wrapCSS))
	$options->wrapCSS = (string) $this->wrapCSS;
if (isset($this->arrows))
	$options->arrows = ($this->arrows == 'true');
if (isset($this->closeBtn))
	$options->closeBtn = ($this->closeBtn == 'true');
if (isset($this->closeClick))
	$options->closeClick = ($this->closeClick == 'true');
if (isset($this->nextClick))
	$options->nextClick = ($this->nextClick == 'true');
if (isset($this->mouseWheel))
	$options->mouseWheel = ($this->mouseWheel == 'true');
if (isset($this->loop))
	$options->loop = ($this->loop == 'true');
if (isset($this->ajax))
	$options->ajax = json_decode($this->ajax, true);
if (isset($this->keys))
	$options->keys = json_decode($this->keys, true);
if (isset($this->modal))
	$options->modal = ($this->modal == 'true');
if (isset($this->autoPlay))
	$options->autoPlay = ($this->autoPlay == 'true');
if (isset($this->playSpeed))
	$options->playSpeed = (int) $this->playSpeed;
if (isset($this->index))
	$options->index = (int) $this->index;
if (isset($this->type) && in_array($this->type, array('image', 'inline', 'ajax', 'iframe', 'swf', 'html')))
	$options->type = $this->type;
if (isset($this->href))
	$options->href = (string) $this->href;
if (isset($this->option_content))
	$options->content = (string) $this->option_content;
if (isset($this->title))
	$options->title = (string) $this->title;
if (isset($this->tpl))
	$options->tpl = json_decode($this->tpl, true);
if (isset($this->openEffect) && in_array($this->openEffect, array('elastic', 'fade', 'none')))
	$options->openEffect = $this->openEffect;
if (isset($this->closeEffect) && in_array($this->closeEffect, array('elastic', 'fade', 'none')))
	$options->closeEffect = $this->closeEffect;
if (isset($this->nextEffect) && in_array($this->nextEffect, array('elastic', 'fade', 'none')))
	$options->nextEffect = $this->nextEffect;
if (isset($this->prevEffect) && in_array($this->prevEffect, array('elastic', 'fade', 'none')))
	$options->prevEffect = $this->prevEffect;
if (isset($this->openSpeed) && is_numeric($this->openSpeed))
	$options->openSpeed = (int) $this->openSpeed;
if (isset($this->openSpeed) && in_array($this->openSpeed, array('slow', 'normal', 'fast')))
	$options->openSpeed = $this->openSpeed;
if (isset($this->closeSpeed) && is_numeric($this->closeSpeed))
	$options->closeSpeed = (int) $this->closeSpeed;
if (isset($this->closeSpeed) && in_array($this->closeSpeed, array('slow', 'normal', 'fast')))
	$options->closeSpeed = $this->closeSpeed;
if (isset($this->nextSpeed) && is_numeric($this->nextSpeed))
	$options->nextSpeed = (int) $this->nextSpeed;
if (isset($this->nextSpeed) && in_array($this->nextSpeed, array('slow', 'normal', 'fast')))
	$options->nextSpeed = $this->nextSpeed;
if (isset($this->prevSpeed) && is_numeric($this->prevSpeed))
	$options->prevSpeed = (int) $this->prevSpeed;
if (isset($this->prevSpeed) && in_array($this->prevSpeed, array('slow', 'normal', 'fast')))
	$options->prevSpeed = $this->prevSpeed;
if (isset($this->openEasing))
	$options->openEasing = (string) $this->openEasing;
if (isset($this->closeEasing))
	$options->closeEasing = (string) $this->closeEasing;
if (isset($this->nextEasing))
	$options->nextEasing = (string) $this->nextEasing;
if (isset($this->prevEasing))
	$options->prevEasing = (string) $this->prevEasing;
if (isset($this->openOpacity))
	$options->openOpacity = ($this->openOpacity == 'true');
if (isset($this->closeOpacity))
	$options->closeOpacity = ($this->closeOpacity == 'true');
if (isset($this->openMethod))
	$options->openMethod = (string) $this->openMethod;
if (isset($this->closeMethod))
	$options->closeMethod = (string) $this->closeMethod;
if (isset($this->nextMethod))
	$options->nextMethod = (string) $this->nextMethod;
if (isset($this->prevMethod))
	$options->prevMethod = (string) $this->prevMethod;
if (isset($this->helpers))
	$options->helpers = json_decode($this->helpers, true);

?>
<script type="text/javascript">
pines(function(){
	<?php foreach (array_keys((array) $options->helpers) as $cur_helper) {
		if (file_exists($file = 'components/com_fancybox/includes/helpers/jquery.fancybox-'.clean_filename($cur_helper).'.css')) { ?>
	pines.loadcss(<?php echo json_encode($pines->config->location.$file); ?>);
	<?php } if (file_exists($file = 'components/com_fancybox/includes/helpers/jquery.fancybox-'.clean_filename($cur_helper).'.js')) { ?>
	pines.loadjs(<?php echo json_encode($pines->config->location.$file); ?>);
	<?php } } ?>
	$('.fancybox-elem', '#p_muid_fancybox').fancybox(<?php echo json_encode($options); ?>);
});</script>
<div id="p_muid_fancybox" class="<?php echo htmlspecialchars($this->class); ?>">
<?php echo $this->icontent; ?>
</div>