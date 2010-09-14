<?php
/**
 * A view to build a FancyBox.
 *
 * @package Pines
 * @subpackage com_fancybox
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->uploader->check($this->file)) {
	echo 'Unsafe file detected.';
	return;
}
$pines->com_fancybox->load();

$images = trim(file_get_contents($pines->uploader->real($this->file)));
$dir = ($this->absolute_path == 'true') ? $pines->config->rela_location : rtrim(dirname($this->file), '/') . '/';
$images = explode("\n", $images);

$options = (object) array();

if (isset($this->padding))
	$options->padding = (int) $this->padding;
if (isset($this->margin))
	$options->margin = (int) $this->margin;
if (isset($this->opacity))
	$options->opacity = ($this->opacity == 'true');
if (isset($this->modal))
	$options->modal = ($this->modal == 'true');
if (isset($this->cyclic))
	$options->cyclic = ($this->cyclic == 'true');
if (isset($this->scrolling))
	$options->scrolling = (string) $this->scrolling;
if (isset($this->width))
	$options->width = (int) $this->width;
if (isset($this->height))
	$options->height = (int) $this->height;
if (isset($this->autoScale))
	$options->autoScale = ($this->autoScale == 'true');
if (isset($this->centerOnScroll))
	$options->centerOnScroll = ($this->centerOnScroll == 'true');
if (isset($this->hideOnOverlayClick))
	$options->hideOnOverlayClick = ($this->hideOnOverlayClick == 'true');
if (isset($this->hideOnContentClick))
	$options->hideOnContentClick = ($this->hideOnContentClick == 'true');
if (isset($this->overlayShow))
	$options->overlayShow = ($this->overlayShow == 'true');
if (isset($this->overlayOpacity))
	$options->overlayOpacity = (float) $this->overlayOpacity;
if (isset($this->overlayColor))
	$options->overlayColor = (string) $this->overlayColor;
if (isset($this->titleShow))
	$options->titleShow = ($this->titleShow == 'true');
if (isset($this->titlePosition))
	$options->titlePosition = (string) $this->titlePosition;
if (isset($this->transitionIn))
	$options->transitionIn = (string) $this->transitionIn;
if (isset($this->transitionOut))
	$options->transitionOut = (string) $this->transitionOut;
if (isset($this->speedIn))
	$options->speedIn = (int) $this->speedIn;
if (isset($this->speedOut))
	$options->speedOut = (int) $this->speedOut;
if (isset($this->changeSpeed))
	$options->changeSpeed = (int) $this->changeSpeed;
if (isset($this->changeFade))
	$options->changeFade = (int) $this->changeFade;
if (isset($this->easingIn))
	$options->easingIn = (string) $this->easingIn;
if (isset($this->easingOut))
	$options->easingOut = (string) $this->easingOut;
if (isset($this->showCloseButton))
	$options->showCloseButton = ($this->showCloseButton == 'true');
if (isset($this->showNavArrows))
	$options->showNavArrows = ($this->showNavArrows == 'true');
if (isset($this->enableEscapeButton))
	$options->enableEscapeButton = ($this->enableEscapeButton == 'true');

if (isset($this->type))
	$options->type = (string) $this->type;
if (isset($this->href))
	$options->href = (string) $this->href;
if (isset($this->title))
	$options->title = (string) $this->title;
if (isset($this->icontent))
	$options->content = (string) $this->icontent;
if (isset($this->index))
	$options->index = (int) $this->index;

if ($this->include_basic_style == 'true') { ?>
<style type="text/css">/* <![CDATA[ */
#p_muid_fancybox img {
background:none repeat scroll 0 0 white;
border:1px solid #BBBBBB;
margin:7px 14px 7px 0;
padding:5px;
width:160px;
}
/* ]]> */</style>
<?php } ?>
<script type="text/javascript">// <![CDATA[
pines(function(){$('a', '#p_muid_fancybox').fancybox(<?php echo json_encode($options); ?>);});
// ]]></script>
<div id="p_muid_fancybox" class="<?php echo htmlspecialchars($this->class); ?>">
	<?php
	foreach ($images as $cur_image) {
		if (empty($cur_image))
			continue;
		$image_html = '';
		$parts = explode(';', $cur_image);
		$image_html .= '<a href="'.htmlspecialchars($dir.$parts[0]).'" rel="p_muid_rel';
		if (!empty($parts[2]))
			$image_html .= '" title="'.htmlspecialchars($parts[2]);
		$image_html .= '"><img alt="" src="'.htmlspecialchars($dir.$parts[1]).'" /></a>';
		echo $image_html;
	}
	?>
</div>