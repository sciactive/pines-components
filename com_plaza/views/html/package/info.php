<?php
/**
 * Displays package info.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Package Info for '.htmlspecialchars($this->package['package']);
?>
<div id="p_muid_info">
	<style type="text/css">
		/* <![CDATA[ */
		#p_muid_info {
			padding: 1em 2em;
		}
		#p_muid_info .version {
			display: block;
			float: right;
			clear: right;
		}
		#p_muid_info .short_description {
			font-size: 1.1em;
		}
		#p_muid_info .icon {
			float: left;
			margin-right: .5em;
		}
		#p_muid_fancybox {
			padding: .4em;
			height: 120px;
			overflow: auto;
		}
		#p_muid_fancybox .screen_small {
			width: 100px;
			height: auto;
			max-height: 100px;
			vertical-align: top;
		}
		/* ]]> */
	</style>
	<div class="pf-form">
		<div class="pf-element pf-heading">
			<?php if (!empty($this->package['icon'])) { ?>
			<img src="<?php echo htmlspecialchars(pines_url('com_plaza', 'package/media', array('local' => 'false', 'name' => $this->package['package'], 'publisher' => $this->package['publisher'], 'media' => $this->package['icon']))); ?>" alt="Icon" class="icon" style="width: 32px; height: 32px;" />
			<?php } ?>
			<h1><span class="name"><?php echo htmlspecialchars($this->package['name']); ?></span><span class="package" style="float: right;"><?php echo htmlspecialchars($this->package['package']); ?></span></h1>
			<p style="clear: right;">
				<span>By <span class="author"><?php echo htmlspecialchars($this->package['author']); ?></span></span>
				<span class="version">Version <span class="text"><?php echo htmlspecialchars($this->package['version']); ?></span></span>
			</p>
		</div>
		<div class="pf-element pf-full-width short_description"><?php echo htmlspecialchars($this->package['short_description']); ?></div>
		<?php if ($this->package['services']) { ?>
		<div class="pf-element services">
			<span class="pf-label">Provides Services</span>
			<span class="pf-field"><?php echo htmlspecialchars(implode(', ', $this->package['services'])); ?></span>
		</div>
		<?php } ?>
		<div class="pf-element license">
			<span class="pf-label">License</span>
			<?php if (preg_match('/^https?:\/\//', $this->package['license'])) { ?>
			<span class="pf-field"><a href="<?php echo htmlspecialchars($this->package['license']); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($this->package['license']); ?></a></span>
			<?php } else { ?>
			<span class="pf-field"><?php echo htmlspecialchars($this->package['license']); ?></span>
			<?php } ?>
		</div>
		<div class="pf-element license">
			<span class="pf-label">Website</span>
			<?php if (preg_match('/^https?:\/\//', $this->package['website'])) { ?>
			<span class="pf-field"><a href="<?php echo htmlspecialchars($this->package['website']); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($this->package['website']); ?></a></span>
			<?php } else { ?>
			<span class="pf-field"><?php echo htmlspecialchars($this->package['website']); ?></span>
			<?php } ?>
		</div>
		<div class="pf-element description"><?php echo str_replace("\n", '<br />', htmlspecialchars($this->package['description'])); ?></div>
		<?php if ($this->package['screens']) { if (isset($pines->com_fancybox)) { ?>
		<div class="pf-element pf-full-width screenshots">
			<div id="p_muid_fancybox" class="ui-widget-content ui-corner-all">
				<?php foreach ($this->package['screens'] as $cur_screen) { ?>
				<a rel="p_muid_ss" title="<?php echo htmlspecialchars($cur_screen['alt']); ?>" href="<?php echo htmlspecialchars($pines->com_plaza->package_get_media($this->package, $cur_screen['file'], true)); ?>">
					<img class="screen_small" alt="<?php echo htmlspecialchars($cur_screen['alt']); ?>" src="<?php echo htmlspecialchars($pines->com_plaza->package_get_media($this->package, $cur_screen['file'], true)); ?>" />
				</a>
				<?php } ?>
			</div>
		</div>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_fancybox > a").fancybox({titleShow: true, titlePosition: "inside"});
			});
			// ]]>
		</script>
		<?php } else { ?>
		<div class="pf-element screenshots">
			<span class="pf-label">Screenshots</span>
			<span class="pf-note">Install com_fancybox for a fancier screenshot experience.</span>
			<?php foreach ($this->package['screens'] as $cur_screen) { ?>
			<div class="pf-group">
				<div class="pf-field"><a href="<?php echo htmlspecialchars(pines_url('com_plaza', 'package/media', array('local' => 'false', 'name' => $this->package['package'], 'publisher' => $this->package['publisher'], 'media' => $cur_screen['file']))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_screen['alt']); ?></a></div>
			</div>
			<?php } ?>
		</div>
		<?php } } if ($this->package['depend']) { ?>
		<div class="pf-element">
			<a href="javascript:void(0);" onclick="$(this).nextAll('div').slideToggle();">See What This Package Depends On</a>
			<br />
			<div class="depend" style="display: none; padding-left: 10px;">
				<?php foreach ($this->package['depend'] as $key => $value) { ?>
				<span class="pf-label"><?php echo htmlspecialchars($key); ?></span><div class="pf-group"><div class="pf-field"><?php echo htmlspecialchars($value); ?></div></div>
				<?php } ?>
			</div>
		</div>
		<?php } if ($this->package['conflict']) { ?>
		<div class="pf-element">
			<a href="javascript:void(0);" onclick="$(this).nextAll('div').slideToggle();">See What This Package Conflicts With</a>
			<br />
			<div class="conflict" style="display: none; padding-left: 10px;">
				<?php foreach ($this->package['conflict'] as $key => $value) { ?>
				<span class="pf-label"><?php echo htmlspecialchars($key); ?></span><div class="pf-group"><div class="pf-field"><?php echo htmlspecialchars($value); ?></div></div>
				<?php } ?>
			</div>
		</div>
		<?php } if ($this->package['recommend']) { ?>
		<div class="pf-element">
			<a href="javascript:void(0);" onclick="$(this).nextAll('div').slideToggle();">See What This Package Recommends</a>
			<br />
			<div class="recommend" style="display: none; padding-left: 10px;">
				<?php foreach ($this->package['recommend'] as $key => $value) { ?>
				<span class="pf-label"><?php echo htmlspecialchars($key); ?></span><div class="pf-group"><div class="pf-field"><?php echo htmlspecialchars($value); ?></div></div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
	<br />
</div>