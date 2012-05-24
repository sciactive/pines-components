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
		var form = $("#p_muid_form").on("click", ":button.page-save", function(){
			$(":button.page-save", "#p_muid_form").attr("disabled", "disabled");
			var enabled = $(this).hasClass("page-publish"),
				data = form.find(":input[name]").serialize();
			if (enabled)
				data += "&enabled=ON";
			$.ajax({
				url: <?php echo json_encode(pines_url('com_content', 'page/save')); ?>,
				type: "POST",
				dataType: "json",
				data: data,
				complete: function(){
					$(":button.page-save", "#p_muid_form").removeAttr("disabled");
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to save the page:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						pines.error("Page could not be saved.");
						return;
					}
					if (data.notice.length)
						$.each(data.notice, function(i, e){pines.notice(pines.safe(e))});
					if (data.error.length)
						$.each(data.error, function(i, e){pines.error(pines.safe(e))});
					if (data.result) {
						// Clear the form.
						form.closest(".object").find(".widget_refresh").click();
					}
				}
			});
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
	<input type="hidden" name="com_menueditor_entries" value="[]" />
	<input type="hidden" name="title_position" value="<?php echo htmlspecialchars(isset($this->title_position) ? $this->title_position : 'null'); ?>" />
	<input type="hidden" name="show_front_page" value="<?php echo htmlspecialchars(isset($this->show_front_page) ? $this->show_front_page : 'null'); ?>" />
	<input type="hidden" name="meta_tags" value="<?php echo htmlspecialchars(isset($this->meta_tags) ? $this->meta_tags : '[]'); ?>" />
	<input type="hidden" name="enable_custom_head" value="<?php echo htmlspecialchars($this->enable_custom_head); ?>" />
	<input type="hidden" name="custom_head" value="<?php echo htmlspecialchars($this->custom_head); ?>" />
	<input type="hidden" name="conditions" value="<?php echo htmlspecialchars(isset($this->conditions) ? $this->conditions : '[]'); ?>" />
	<input type="hidden" name="p_cdate" value="<?php echo htmlspecialchars($this->p_cdate); ?>" />
	<input type="hidden" name="p_mdate" value="<?php echo htmlspecialchars($this->p_mdate); ?>" />
	<input type="hidden" name="publish_begin" value="<?php echo htmlspecialchars(!empty($this->publish_begin) ? $this->publish_begin : format_date(time(), 'full_med')); ?>" />
	<input type="hidden" name="publish_end" value="<?php echo htmlspecialchars($this->publish_end); ?>" />
	<input type="hidden" name="show_title" value="<?php echo htmlspecialchars(isset($this->show_title_save) ? $this->show_title_save : 'null'); ?>" />
	<input type="hidden" name="show_author_info" value="<?php echo htmlspecialchars(isset($this->show_author_info) ? $this->show_author_info : 'null'); ?>" />
	<input type="hidden" name="show_content_in_list" value="<?php echo htmlspecialchars(isset($this->show_content_in_list) ? $this->show_content_in_list : 'null'); ?>" />
	<input type="hidden" name="show_intro" value="<?php echo htmlspecialchars(isset($this->show_intro) ? $this->show_intro : 'null'); ?>" />
	<input type="hidden" name="show_breadcrumbs" value="<?php echo htmlspecialchars(isset($this->show_breadcrumbs) ? $this->show_breadcrumbs : 'null'); ?>" />
	<?php foreach ((array) $this->variants as $cur_variant) { if (empty($cur_variant)) continue; ?>
	<input type="hidden" name="variants[]" value="<?php echo htmlspecialchars($cur_variant); ?>" />
	<?php } foreach ((array) $this->categories as $cur_guid) { if (empty($cur_guid)) continue; ?>
	<input type="hidden" name="categories[]" value="<?php echo htmlspecialchars($cur_guid); ?>" />
	<?php } ?>
	<input type="hidden" name="ajax" value="true" />
	<div class="pf-element pf-full-width">
		<div class="btn-group" style="float: right;">
			<input class="pf-button btn btn-success page-save page-publish" type="button" value="Save and Publish" />
			<input class="pf-button btn page-save" type="button" value="Save" />
		</div>
	</div>
</div>