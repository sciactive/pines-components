<?php
/**
 * Provides a widget for the user to create page.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if (empty($this->widget_title))
	$this->title = 'Quick Page';
else
	$this->title = $this->widget_title;

$pines->editor->load();
$pines->com_ptags->load();

?>
<style type="text/css">
#p_muid_form.pf-form .pf-element .pf-label, #p_muid_form.pf-form .pf-element .pf-note {
width: 120px;
}
#p_muid_form.pf-form .pf-element .pf-group {
margin-left: 120px;
}
#p_muid_form.pf-form .pf-element .pf-field.pf-full-width {
margin-left: 125px;
}
</style>
<script type="text/javascript">
	pines(function(){
		$("#p_muid_form").on("click", ":button .page-save", function(){
			var enabled = $(this).hasClass("page-publish");
			// TODO: Save the page through ajax.
		});
	});
</script>
<div class="pf-form" id="p_muid_form">
	<div class="pf-element pf-full-width">
		<script type="text/javascript">
			pines(function(){
				var alias = $("#p_muid_form [name=alias]");
				$("#p_muid_form [name=name]").change(function(){
					if (alias.val() == "")
						alias.val($(this).val().replace(/[^\w\d\s\-.]/g, '').replace(/\s/g, '-').toLowerCase());
				}).blur(function(){
					$(this).change();
				}).focus(function(){
					if (alias.val() == $(this).val().replace(/[^\w\d\s\-.]/g, '').replace(/\s/g, '-').toLowerCase())
						alias.val("");
				});
			});
		</script>
		<label>
			<span class="pf-label">Name</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<input style="width: 100%;" type="text" name="name" value="" />
				</span>
			</span>
		</label>
	</div>
	<div class="pf-element pf-full-width" style="text-align: right;">
		<a href="javascript:void(0);" onclick="$(this).children().add('#p_muid_aliastitle').toggle();"><span>Edit Alias and Title</span><span style="display: none;">Hide Alias and Title</span></a>
	</div>
	<div id="p_muid_aliastitle" style="display: none;">
		<div class="pf-element pf-full-width">
			<label>
				<span class="pf-label">Alias</span>
				<span class="pf-group pf-full-width">
					<span class="pf-field" style="display: block;">
						<input style="width: 100%;" type="text" name="alias" value="" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
					</span>
				</span>
			</label>
		</div>
		<div class="pf-element pf-full-width">
			<script type="text/javascript">
				pines(function(){
					$("#p_muid_use_name").change(function(){
						if ($(this).is(":checked"))
							$("#p_muid_title").attr("disabled", "disabled");
						else
							$("#p_muid_title").removeAttr("disabled");
					}).change();
				});
			</script>
			<span class="pf-label">Page Title</span>
			<div class="pf-group pf-full-width">
				<label><input class="pf-field" type="checkbox" id="p_muid_use_name" name="title_use_name" value="ON" checked="checked" /> Use name as title.</label><br />
				<span class="pf-field" style="display: block;">
					<input style="width: 100%;" type="text" id="p_muid_title" name="title" value="" />
				</span>
			</div>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Intro</span><br />
		<div style="overflow: auto;">
			<textarea rows="3" cols="35" class="<?php echo $this->intro_full_editor == 'true' ? 'peditor' : 'peditor-simple'; ?>" style="width: 100%; height: 100px;" name="intro"></textarea>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Content</span><br />
		<div style="overflow: auto;">
			<textarea rows="5" cols="35" class="<?php echo $this->content_simple_editor == 'true' ? 'peditor-simple' : 'peditor'; ?>" style="width: 100%; height: 200px;" name="content"></textarea>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Tags</span>
		<div class="pf-group">
			<input class="pf-field" type="text" name="content_tags" size="10" value="<?php echo htmlspecialchars(implode(',', (array) $this->content_tags)); ?>" />
			<script type="text/javascript">
				pines(function(){
					$("#p_muid_form [name=content_tags]").ptags({
						ptags_sortable: {
							tolerance: 'pointer',
							handle: '.ui-ptags-tag-text'
						}
					});
				});
			</script>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<div class="btn-group" style="float: right;">
			<input class="pf-button btn btn-success page-save page-publish" type="button" value="Save and Publish" />
			<input class="pf-button btn page-save" type="button" value="Save" />
		</div>
	</div>
	<?php foreach (array() as $cur_var) { ?>
	<input type="hidden" name="<?php echo htmlspecialchars($cur_var); ?>" value="" />
	<?php } ?>
</div>