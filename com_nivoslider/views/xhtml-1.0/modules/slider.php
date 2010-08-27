<?php
/**
 * A view to build a Nivo Slider module.
 *
 * @package Pines
 * @subpackage com_nivoslider
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!$pines->uploader->check($this->file)) {
	echo 'Unsafe file detected.';
	return;
}
$pines->com_nivoslider->load();

$images = trim(file_get_contents($pines->uploader->real($this->file)));
$dir = ($this->absolute_path == 'true') ? $pines->config->rela_location : rtrim(dirname($this->file), '/') . '/';
$images = explode("\n", $images);

$options = (object) array();

if (isset($this->effect))
	$options->effect = (string) $this->effect;
if (isset($this->slices))
	$options->slices = (int) $this->slices;
if (isset($this->animSpeed))
	$options->animSpeed = (int) $this->animSpeed;
if (isset($this->pauseTime))
	$options->pauseTime = (int) $this->pauseTime;
if (isset($this->startSlide))
	$options->startSlide = (int) $this->startSlide;
if (isset($this->directionNav))
	$options->directionNav = ($this->directionNav == 'true');
if (isset($this->directionNavHide))
	$options->directionNavHide = ($this->directionNavHide == 'true');
if (isset($this->controlNav))
	$options->controlNav = ($this->controlNav == 'true');
if (isset($this->controlNavThumbs))
	$options->controlNavThumbs = ($this->controlNavThumbs == 'true');
if (isset($this->controlNavThumbsFromRel))
	$options->controlNavThumbsFromRel = ($this->controlNavThumbsFromRel == 'true');
if (isset($this->controlNavThumbsSearch))
	$options->controlNavThumbsSearch = (string) $this->controlNavThumbsSearch;
if (isset($this->controlNavThumbsReplace))
	$options->controlNavThumbsReplace = (string) $this->controlNavThumbsReplace;
if (isset($this->keyboardNav))
	$options->keyboardNav = ($this->keyboardNav == 'true');
if (isset($this->pauseOnHover))
	$options->pauseOnHover = ($this->pauseOnHover == 'true');
if (isset($this->manualAdvance))
	$options->manualAdvance = ($this->manualAdvance == 'true');
if (isset($this->captionOpacity))
	$options->captionOpacity = (float) $this->captionOpacity;

?>
<script type="text/javascript">
	// <![CDATA[
	<?php if ($this->include_basic_style == 'true') { ?>
	pines.loadcss("<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_nivoslider/includes/basic_style/slider.css");
	<?php } ?>
	pines(function(){
		$('#p_muid_slider').nivoSlider(<?php echo json_encode($options); ?>);
	});
	// ]]>
</script>
<div id="p_muid_slider" class="<?php echo htmlspecialchars($this->class); ?>" style="<?php echo htmlspecialchars((isset($this->width) ? "width: $this->width; " : '').(isset($this->height) ? "height: $this->height;" : '')); ?>">
	<?php
	foreach ($images as $cur_image) {
		if (empty($cur_image))
			continue;
		$image_html = '';
		$parts = explode(';', $cur_image);
		$image_html .= '<img alt="" src="'.htmlspecialchars($dir.$parts[0]);
		if (!empty($parts[1]))
			$image_html .= '" title="'.htmlspecialchars($parts[1]);
		$image_html .= '" />';
		if (!empty($parts[2]))
			$image_html = '<a href="'.htmlspecialchars($parts[2]).'">'.$image_html.'</a>';
		echo $image_html;
	}
	?>
</div>