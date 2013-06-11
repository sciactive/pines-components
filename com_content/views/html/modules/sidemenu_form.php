<?php
/**
 * Provides a form for the user to choose what tag to base the side menu off of.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$pines->editor->load();
?>
<style type="text/css">
	#p_muid_form.pf-form:after, #p_muid_form.pf-form fieldset:after {
		height: auto;
		margin-left: 0;
	}
	#p_muid_form .red-warning {
		border-radius: 6px;
		background: #990000;
		text-shadow: 1px 1px solid #990000;
		padding:5px; 
		margin:4px auto; 
		color: #fff;
		text-transform: uppercase;
		font-size: .8em;
		text-align: center;
	}
	#p_muid_form .preview-menu {
		border-top: 1px solid #ddd;
		padding-top: 10px;
		clear: both;
	}
	#p_muid_form .preview-menu-content {
		margin: 10px auto;
		border: 1px solid #ddd;
		padding: 10px;
	}
	#p_muid_form ul.preview-menu {
		list-style: none;
	}
	#p_muid_form ul.preview-menu li {
		margin: 0 0 3px 0;
		font-weight: bold;
	}
	#p_muid_form ul.preview-menu li span.item {
		text-align: left;
		width: 70%;
	}
	#p_muid_form ul.preview-menu li span {
		cursor: move;
	}
	#p_muid_form .preview-menu .btn-group {
		display: block;
	}
</style>
<script type="text/javascript">
	pines(function(){
		// Define Variables
		var form = $('#p_muid_form');
		var preview_menu_content = form.find('.preview-menu-content');
		var preview_button = form.find('.preview-button');
		var update_button = form.find('.update-button');
		var tag_input = form.find('[name=tag]');
		var guid_order = form.find('[name=guid_order]');
		var done_button = $('.ui-dialog-buttonset button.btn:contains(Done):visible');
		var new_done_button = $('<button class="btn new-done-button">Done</button>');
		var update_modal = $('.update-modal');
		var update_modal_update_button = update_modal.find('.update-modal-update');
		var update_modal_done_button = update_modal.find('.update-modal-done');
		
		update_modal_update_button.mousedown(function(){
			update_button.mousedown();
		});
		
		update_modal_done_button.mousedown(function(){
			new_done_button.mousedown();
		});
		
		done_button.hide();
		done_button.after(new_done_button);
		
		new_done_button.mousedown(function(){
			// If they clicked to preview, but then didn't update... we should indicate that they should update. but they can opt not to.
			if (preview_button.hasClass('btn-success') && !update_button.hasClass('btn-success') && !new_done_button.hasClass('btn-primary')) {
				// Show modal to suggest that the menu should be updated.
				update_modal.modal({
					keyboard: false,
					backdrop: false
				});
				// Turn this button to btn primary
				new_done_button.addClass('btn-primary');
			} else {
				done_button.click();
				update_modal.remove();
			}
		});

		preview_button.mousedown(function(){
			form.find('.red-warning').remove();
			if (tag_input.val() == '') {
				// We can't get a menu without a tag!
				tag_input.after('<div class="red-warning"><i class="icon-warning-sign"></i> Cannot preview a menu without specifying a tag!</div>');
				return;
			}
			
			// We do have a value in the tag!
			var tag = tag_input.val();
			get_menu(tag);
			
			// Make this button green too
			preview_button.addClass('btn-success');
		});
		
		update_button.mousedown(function(){
			form.find('.red-warning').remove();
			if (guid_order.val() == '') {
				// We can't get a menu without a tag!
				guid_order.after('<div class="red-warning"><i class="icon-warning-sign"></i> Cannot save the menu without a menu order. Make sure the preview is working properly.</div>');
				return;
			}
			
			// We do have a value in the tag!
			save_menu(guid_order.val());
		});
		
		tag_input.focusin(function(){
			form.find('.red-warning').remove();
		});

		var get_menu = function(tag) {
			$.ajax({
				url: <?php echo json_encode(pines_url('com_content', 'create_sidemenu')); ?>,
				type: "POST",
				dataType: "json",
				data: {"tag": tag},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to create the menu:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data != false) {
						// We need to generate the html for creating the sort ul and lis
						var ul = $('<ul class="preview-menu"></ul>');
						var order_value = new Array();
						$.each(data, function(index, value){
							ul.append('<li data-id="'+value.menu_item_guid+'" class="btn-group"><span class="btn"><i class="icon-move"></i></span><span class="btn item">'+value.menu_item_name+'</span></li>');
							order_value.push(value.menu_item_guid);
						});
						
						guid_order.val(order_value.join(","));
						preview_menu_content.html(ul).fadeIn();
						update_button.fadeIn();
						
						// Make the UL sortable...Make the order get saved on change.
						ul.sortable({
							//handle: "button",
							items: "li",
							stop: function() {
								var sorted = ul.sortable('toArray', { attribute: 'data-id' });
								guid_order.val(sorted);
							}
						}).disableSelection();
					}
				}
			});
		};
		
		var save_menu = function(guid_order) {
			var tag = tag_input.val();
			$.ajax({
				url: <?php echo json_encode(pines_url('com_content', 'save_sidemenu')); ?>,
				type: "POST",
				dataType: "json",
				data: {"guid_order": guid_order, "tag": tag},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to save the menu:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == true) {
						update_button.addClass('btn-success');
						new_done_button.addClass('btn-primary');
					}
				}
			});
		};
		
		
		
	});
</script>
<div id="p_muid_form" class="pf-form span6">
	<div class="pf-element pf-heading">
		<p>What page tag do you want to use to base the menu on.</p>
	</div>
	<p class="well" style="clear:both;"><i class="icon-info-sign"></i> 
	This module is useful if you have many pages, sorted by categories that you want
	to display on a left side menu. At this point in time its limitations are that it
	relies on JavaScript/jQuery, that the pages and categories you want to use must 
	have a specified tag, that categories cannot be links themselves but must 
	contain pages, and that the template you are using must have a sub menu position, 
	or you won't see the menu's sub categories or pages. You have to make your sub menu
	appear on hover with your own jQuery/JavaScript, or put your sub position at the bottom of
	your template so it works well on mobile devices.
	</p>
	<div class="pf-element pf-full-width">
		<label>
			<span class="pf-label">Page Tag</span><br />
			<input class="pf-field" type="text" name="tag" value="<?php echo htmlspecialchars($this->tag); ?>"/>
		</label>
	</div>
	<div class="pf-element pf-full-width">
		<label>
			<span class="pf-label">Sub Menu Position</span><br />
			<input class="pf-field" type="text" name="sub_position" value="<?php echo htmlspecialchars($this->sub_position); ?>"/>
		</label>
	</div>
	<div class="preview-menu">
		<p class="well"><i class="icon-info-sign"></i> You must click to preview the menu in order to update your menu. 
		You want to update the menu whenever you add new pages or categories with your selected tag. It
		saves time loading pages to not run a function to call the menu every time. So update here!</p>
		<div class="preview-button btn btn-primary">Preview Top Level</div>
		<div class="preview-menu-content hide">
			
		</div>
		<div class="update-button btn btn-primary hide">Update Menu</div>
	</div>
	<input type="hidden" name="guid_order" />
	
</div>

<div class="modal hide fade update-modal" style="z-index: 9000;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3><span style="font-weight:bold; text-transform: uppercase; text-align: center; display:block;">Update Before Saving?</span></h3>
	</div>
	<div class="modal-body">
		<p>Did you sort your menu? Did you re-order your pages within categories? Have you added new pages? Then update your menu before you click done!</p>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success update-modal-update" data-dismiss="modal" aria-hidden="true">Update</button>
		<button class="btn btn-primary update-modal-done" data-dismiss="modal" aria-hidden="true">Done</button>
	</div>
</div>