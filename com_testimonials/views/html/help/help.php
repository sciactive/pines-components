<?php
/**
 * Provide Help for Testimonials
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_testimonials/help')) {
	punt_user(null, pines_url('', ''));
}
$pines->com_timeago->load();
$pines->com_testimonials->load();
$bg_lightest = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->review_background)) ? $pines->config->com_testimonials->review_background : '#eeeeee';
$bg_medium = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->list_item_border)) ? $pines->config->com_testimonials->list_item_border : '#dddddd';
$bg_darkest = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->average_background)) ? $pines->config->com_testimonials->average_background : '#cccccc';

$accent_medium = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_background_opened)) ? $pines->config->com_testimonials->feedback_background_opened : '#0088cc';
$accent_light = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_hr_bottom)) ? $pines->config->com_testimonials->feedback_hr_bottom : '#5cb4f2';

$font_lightest = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_color_opened)) ? $pines->config->com_testimonials->feedback_color_opened : '#ffffff';
$font_light = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->author_text)) ? $pines->config->com_testimonials->author_text : '#999999';

?>
<style type="text/css">
	.p_muid_help .help-info {
		background: <?php echo $bg_lightest; ?>;
		padding: 15px;
		margin: 10px auto;
		width: 90%;
		text-align: center;
		font-size: 16px;
		border-left: 5px solid <?php echo $bg_medium; ?>;
	}
	.p_muid_help .help-info:hover {
		background: <?php echo $bg_medium; ?>;
	}
	.p_muid_help .help-info-highlight {
		background: <?php echo $accent_medium; ?>;
		color: <?php echo $font_lightest; ?>;
		width: 80%;
		margin: auto;
		padding: 10px;
		font-size: 16px;
	}
	.p_muid_help .help-info.height {
		height: 80px;
	}
	.p_muid_help .text-center {
		text-align: center;
	}
	.p_muid_help .text-right {
		text-align: right;
	}
	.p_muid_help .help-list {
		list-style-type: none;
		margin: 0;
		margin-left: 10px;
		padding: 0;
	}
	.p_muid_help .help-list li {
		padding: 5px 15px;
		margin-left: 20px;
		text-shadow: 1px 1px <?php echo $font_lightest; ?>;
		position: relative;
		font-size: 12px;
	}
	.p_muid_help .help-list li i {
		position: absolute;
		left: -16px;
		top:8px;
		color: <?php echo $font_light; ?>;
	}
	.p_muid_help .option-icon {
		color: <?php echo $font_light; ?>;
		font-size: 12px;
		vertical-align: text-top;
	}
	.p_muid_help .help-list li:hover i, .p_muid_help .help-list li:hover {
		color: <?php echo $accent_medium; ?>;
	}
	.p_muid_help .help-code-container {
		overflow-x: auto;
	}
	.p_muid_help .help-code {
		display: block;
		padding: 5px;
		white-space: normal;
		margin: 0 20px;
		white-space: pre;
		color: #DD1144;
		overflow-x: auto;
		font-size: 10px;
	}
	.p_muid_help .step-info {
		 width: 70%;
		 float:right;
		 padding: 5%;
	}
	.p_muid_help .step-num {
		width: 20%;
		float:left;
		text-align: center;
		margin-top: 10%;
		font-size: 20px;
		color: <?php echo $font_light; ?>;
	}
	.p_muid_help .help-info.step-heading {
		padding: 0;
	}
	.p_muid_help .show-more {
		font-size: 12px;
	}
	.p_muid_help .show-more a {
		text-decoration: none;
	}
	.p_muid_help .help-info-plain {
		margin: 0 20px;
		padding: 10px;
	}
	.p_muid_help .modal .option-section {
		margin: auto;
		width: 80%;
		padding: 0 10px;
		background: <?php echo $bg_lightest; ?>;
	}
	.p_muid_help .modal .option-section.more-padding {
		padding: 10px;
	}
	.p_muid_help .option-name {
		font-weight: bold;
		cursor: pointer;
	}
	.p_muid_help .option-name:hover {
		color: <?php echo $accent_medium; ?>;
	}
	.p_muid_help .option.blink .option-name:hover {
		color: <?php echo $accent_light; ?>;
	}
	.p_muid_help .blink {
		background: <?php echo $accent_medium; ?>;
		color: <?php echo $font_lightest; ?>
	}
	.p_muid_help .clone-to {
		width: 80%;
		margin: 0 auto 20px;
	}
	.p_muid_help .preview {
		margin: 20px 0;
	}
	.p_muid_help .module-code {
		width: 100%;
		height: 300px;
	}
	.p_muid_help .help-menu-button {
		padding: 10px;
		font-size: 16px;
		margin-bottom: 5px;
		background: <?php echo $bg_lightest; ?>;
		color: <?php echo $accent_medium; ?>;
		cursor: pointer;
		display: inline-block;
		width: auto;
		-o-transition: all 125ms linear 0s;
		-ms-transition: all 125ms linear 0s;
		-moz-transition: all 125ms linear 0s;
		-webkit-transition: all 125ms linear 0s;
		/* ...and now override with proper CSS property */
		transition: all 125ms linear 0s;
	}
	.p_muid_help .help-menu-button:hover {
		background: <?php echo $accent_light; ?>;
		color: <?php echo $font_lightest; ?>;
	}
	h3 {
		position: relative;
	}
	.help-top {
		font-size: 10px;
		position: absolute;
		right: 10px;
	}
	.add-section .option-get-value {
		width: 80%;
	}
	
	/* SECTION HIGHLIGHTS */
	.p_muid_help .section-highlight {
		background: <?php echo $bg_lightest; ?>;
		padding: 10px;
	}
	.p_muid_help .section-highlight .help-info {
		background: <?php echo $bg_medium; ?>;
		border-color: <?php echo $bg_darkest; ?>;
	}
	.p_muid_help .section-highlight hr, .p_muid_help .option-section hr {
		border-top-color: <?php echo $bg_medium; ?>;
	}
</style>
<script type="text/javascript">
	pines(function(){
		var help_container = $('.p_muid_help');
		var help_list = help_container.find('.help-list');
		var help_list_li = help_list.find('li');
		var help_code = help_container.find('.help-code');
		var view_more = help_list.find('.view-more');
		var show_more = help_container.find('.show-more a');
		var step_heading = help_container.find('.step-heading');
		var option_table = help_container.find('.option-table');
		var option_trs = option_table.find('.option');
		var option_modal = help_container.find('.option-modal');
		var option_asterisk = option_trs.find('.option-asterisk');
		var option_review = option_trs.find('.option-review');
		var initialize_manually_link = help_container.find('.initialize-manually');
		var preview_container = help_container.find('.preview');
		var preview_button = help_container.find('.preview-button');
		var undo_button = help_container.find('.undo-button');
		var clear_button = help_container.find('.clear-button');
		var store_options = help_container.find('.store-options');
		var module_code_textarea = help_container.find('.module-code');
		var h3_headers = help_container.find('h3:not(.no-top)');
		var add_modal = help_container.find('.add-modal');
		var add_modal_button = add_modal.find('.add-modal-button');
		
		// h3 headers - add tops
		h3_headers.append('<a href="#help-top" class="help-top"><span><i class="icon-caret-up"></i> Top</span></a>');
		
		// Fix Lists with nicer bullets
		help_list_li.prepend('<i class="icon-ok"></i>');
		
		// Fix pre tags with properly typed "html"
		String.prototype.escapeHTML = function () {                                        
			return(                                                                 
			  this.replace(/>/g,'&gt;').
				   replace(/</g,'&lt;').
				   replace(/"/g,'&quot;')
			);
		};
		
		var fix_code = function(element) {
			 element.html(element.html().replace(/>/g,'&gt;').replace(/</g,'&lt;').replace(/"/g,'&quot;'))
		};
		
		help_code.each(function(){
			fix_code($(this));
		});
		
		if ($(window).width() < 600)
			view_more.hide();
		else
			show_more.addClass('viewing').html('<i class="icon-caret-up"></i> Hide');
		
		show_more.click(function(){
			if (show_more.hasClass('viewing')) {
				view_more.fadeOut();
				show_more.html('<i class="icon-caret-down"></i> Show More');
				show_more.removeClass('viewing');
			} else {
				view_more.fadeIn();
				show_more.addClass('viewing');
				show_more.html('<i class="icon-caret-up"></i> Hide');
			}
		});
		
		step_heading.each(function(){
			var cur_step = $(this);
			var step_num = cur_step.find('.step-num');
			
			var margin_top = (cur_step.height() - step_num.height()) / 2;
			step_num.css('margin-top', margin_top+'px');
		});
		
		// Make options light up on hover
		option_trs.mouseenter(function(){
			var button = $(this).find('button');
			button.filter('.details').addClass('btn-primary');
			button.filter('.add').addClass('btn-info');
		}).mouseleave(function(){
			var button = $(this).find('button');
			button.removeClass('btn-primary btn-info');
		});
		
		// Make asterisks icons
		option_asterisk.each(function(){
			$(this).before('<i class="icon-asterisk option-icon"></i> ');
		});
		// Make Review Icons
		option_review.each(function(){
			$(this).before('<i class="icon-thumbs-up-alt option-icon"></i> ');
		});
		
		// Make shown event function before calling the modal
		option_modal.on('show', function(){
			if (option_modal.hasClass('initialize-module')) {
				var load_module = option_modal.find('.option-module');
				setTimeout(function(){
					window.create_testimonial_module(load_module.find('.testimonial-box'));
				}, 300);
				option_modal.removeClass('initilize-module');
			}
		});
		
		// Make Options load into modal
		option_trs.find('.details').click(function(){
			var option_tr = $(this).closest('tr');
			var skip_load = false;
			// Check real quick that the option is not already loaded
			if (option_tr.find('.option-name').text() == option_modal.find('.option-name').text())
				skip_load = true;
			
			if (!skip_load) {
				// Get html/text of what to load
				var option_name = option_tr.find('.option-name').text();
				var option_type = option_tr.find('.option-type').text();
				var option_default = option_tr.find('.option-default').html();
				var option_description = option_tr.find('.option-description').html();
				var option_example = option_tr.find('.option-example').html();
				var pre_div_text = option_tr.find('.option-example pre').text();
				var pre_div = $(pre_div_text.replace(/\r/, '').replace(/\t/, '').replace(/(.*)>.*\[.*">/, '$1>'));
				var pre_inputs = $(pre_div_text.replace(/\r/).replace(/\t/, '').replace(/.*testimonials \/\].*?<(.*)<\/div>/), '>$1').find('input');
					
				// Get jQuery objects of where to put things
				var load_name = option_modal.find('.option-name');
				var load_type = option_modal.find('.option-type');
				var load_default = option_modal.find('.option-default');
				var load_description = option_modal.find('.option-description');
				var load_example = option_modal.find('.option-example');
				var load_module = option_modal.find('.option-module');
				var clone_from = load_module.find('.clone-from');
				var clone_from_module = clone_from.find('.testimonials-module').removeAttr('id');
				var clone_to = load_module.find('.clone-to');
				
				// Load it!
				load_name.text(option_name);
				load_type.text(option_type);
				load_default.text(option_default);
				load_description.html(option_description);
				load_example.html(option_example);
				
				// Clear, clone, Load Module
				pre_div.html(clone_from_module.clone());
				pre_div.prepend(pre_inputs);
				clone_to.html(pre_div) // clear the clone_to html with a cleared pre_div.
				option_modal.addClass('initialize-module');
			}
			
			// Show modal
			option_modal.modal('show');
		});
		
		var flash = function(element, time) {
			time = (time == undefined) ? 300 : time;
			element.addClass('blink');
			if (time == 'never')
				return;
			setTimeout(function(){
				element.removeClass('blink', 400);
			}, time);
		};
		
		initialize_manually_link.click(function(){
			flash($(this));
		});
		
		// The function to make a preview... from the picker.
		var make_preview = function(skip_check) {
			var loader = $('<div style="padding: 30px; font-weight:bold;" class="text-center"></div>');
			loader.append('<i class="icon-spin icon-spinner icon-2x"></i>');
			loader.append('<div style="margin-top: 10px;">Loading...</div>');
			preview_container.html(loader);
			
			var options_true = store_options.html().length;
			
			// If options, make invisible because it would have to be a list or a carousel.
			if (options_true)
				var preview_box = preview_container.html('<div class="testimonial-box" style="visibility: hidden;"></div>').find('.testimonial-box');
			else
				var preview_box = preview_container.html('<div class="testimonial-box"></div>').find('.testimonial-box');
			
			// Start building code
			var code = $(preview_container.html());
			
			// CHECK FOR ERRORS
			if (skip_check == undefined) {
				if (!store_options.find('[name=review_option_display]').length && options_true) {
					var message = 'You need to choose a list or carousel display type!';
					var error = true;
					flash($('.option-name:contains(review_option_display)').closest('tr'), 'never');
				}
				if (!store_options.find('[name=review_list_height]').length && store_options.find('[name=review_option_display]').val() == 'list') {
					var message = 'You need to set a height for your list or it will behave strangely!';
					var error = true;
					flash($('.option-name:contains(review_list_height)').closest('tr'), 'never');
				}
				if (!store_options.find('[name=review_item_name]').length && store_options.find('[name=review_option_display]').length) {
					var message = 'You need to pick a caption or item name to appear at the top!';
					var error = true;
					flash($('.option-name:contains(review_item_name)').closest('tr'), 'never');
				}
				if (store_options.find('[name=review_option_display]').val() == 'list' && store_options.find('review_option_offset').length) {
					var message = 'You should NOT use an offset with the display type list!';
					var error = true;
					flash(undo_button, 'never');
				}
				if (store_options.find('[name=review_option_display]').val() == 'list' && !store_options.find('[name=review_option_limit]').length) {
					var message = 'You need to specify a limit for lists, usually a lower amount. Otherwise the list will keep loading the same last 20 which is default.';
					var error = true;
					flash($('.option-name:contains(review_option_limit)').closest('tr'), 'never');
				}
				if (!store_options.find('[name=review_option_type]').length && (store_options.find('[name=review_option_name]').length || store_options.find('[name=review_option_entity]').length || store_options.find('[name=review_option_entity_id]').length )) {
					var message = 'You need to specify that this is a review using review_option_type.';
					var error = true;
					flash($('.option-name:contains(review_option_type)').closest('tr'), 'never');
				}
				if ((store_options.find('[name=review_option_type]').length && !store_options.find('[name=review_option_name]').length) && (!store_options.find('[name=review_option_entity]').length && !store_options.find('[name=review_option_entity_id]').length)) {
					var message = 'You must include both an entity id [review_option_entity_id] and entity class [review_option_entity] for reviews if not using a unique name [review_option_name]';
					var error = true;
					flash($('.option-name:contains(review_option_entity_id)').closest('tr'), 'never');
					flash($('.option-name:contains(review_option_entity)').closest('tr'), 'never');
					flash($('.option-name:contains(review_option_name)').closest('tr'), 'never');
				}
			}
			
			if (error == undefined) {
				if (!store_options.find('[name=review_option_clear]').length && options_true)
					store_options.append('<input type="hidden" name="review_option_clear" value="true"/>');
			
				if (store_options.find('[name=review_option_display]').val() == 'list' && !store_options.find('[name=review_option_offset]').length)
					store_options.append('<input type="hidden" name="review_option_offset" value="0"/>');
					
				// Load Clone
				preview_box.html(help_container.find('.clone-from').html());

				// Append all input options
				if (options_true)
					preview_box.append(store_options.html());


				// Load JS
				window.create_testimonial_module(preview_box);

				// Set the code
				code.append('&#013;&#09;[com_testimonials/testimonials /]&#013;');
				var clone_options = store_options.clone();
				clone_options.find('input').each(function(){
					var span = $('<span></span>');
					span.append($(this));
					code.append('&#09;'+span.html()+'&#013;');
				});
				var code_wrapper = $('<div class="code-wrapper"></div>');
				code_wrapper.html(code);
				module_code_textarea.text(code_wrapper.html());
			} else {
				var loader = $('<div style="padding: 30px; font-weight:bold;" class="text-center help-info-highlight"></div>');
				loader.append('<i class="icon-remove icon-2x"></i>');
				loader.append('<div style="margin-top: 10px;">'+message+'</div>');
				loader.append('<button class="skip-check btn btn-inverse" style="margin-top: 10px;">Get Code Anyway!</div>');
				loader.find('.skip-check').click(function(){
					make_preview(true);
				});
				preview_container.html(loader);
				module_code_textarea.text('Add missing options to get code.');
			}
		}
		
		// When you click the text area it selects
		module_code_textarea.click(function(){
			$(this).select();
		});
		
		// Start with a plain preview:
		make_preview();
		
		// preview button makes a whole new clone...
		preview_button.click(function(){
			make_preview();
		});
		
		
		// Add an item to the store options
		option_trs.find('.add').click(function(){
			var tr = $(this).closest('tr');
			// Get variables
			var requirements = tr.find('.add-requirements').html();
			var section = tr.find('.add-section').html();
			var name = tr.find('.option-name').text();
			
			// Get sections to load things to...
			var load_requirements = add_modal.find('.add-requirements');
			var load_section = add_modal.find('.add-section');
			var load_name = add_modal.find('.add-name');
			
			
			// Load them
			load_requirements.html(requirements);
			load_section.html(section);
			load_name.html(name);
			
			// Create some cool events
			var get_value = load_section.find('.option-get-value');
			var put_value = load_section.find('.option-put-value');
			
			get_value.change(function(){
				var value = $(this).val();
				if (value != '') {
					put_value.val(value);
				}
			});
			
			add_modal.modal();
		});
		
		add_modal_button.click(function(){
			// Take the current modal's "put-value" and add it to store options
			var put_input = add_modal.find('.option-put-value');
			var put_value = (put_input.val() == undefined) ? '' : put_input.val();
			var put_name = put_input.attr('name');
			
			var additional_tags = store_options.find('[name=review_option_additional_tags]');
			
			if (put_value != '' && !store_options.find('[name='+put_name+']').length) {
				
				// Special stuff is the "comment" for review_option_type
				if (put_name == 'review_option_type' && put_value == 'comment') {
					// they want a review, but also add in submit_refresh and make sure it's a list and add tag comment
					put_value = 'review';
					store_options.append('<input type="hidden" name="review_submit_refresh" value="true" />');
					if (additional_tags.length) {
						additional_tags.val(additional_tags.val() + ',comment');
					} else
						store_options.append('<input type="hidden" name="review_option_additional_tags" value="comment" />');
					
				}
				
				if (put_name == 'review_option_additional_tags' && additional_tags.length) 
					additional_tags.val(additional_tags.val() + ','+put_value);
				else
					store_options.append('<input type="hidden" name="'+put_name+'" value="'+put_value+'" />');
				option_trs.filter('.blink').removeClass('blink');
			}
			if (put_value != '' && put_name == store_options.find('input').last().attr('name')) {
				preview_container.find('.blink').remove();
				preview_container.prepend('<div class="blink help-info-highlight" style="margin-bottom: 20px;">Clear to start over with new options, or undo the last one to override.</div>')
			}
			// Should we auto-trigger previews? Or not? Maybe? Cause it will help tell the user what options they need
			make_preview();
		});
		
		undo_button.click(function(){
			if (store_options.html().length) {
				store_options.find('input').last().remove();
				make_preview();
			} else {
				preview_container.find('.blink').remove();
				preview_container.prepend('<div class="blink help-info-highlight" style="margin-bottom: 20px;">No options to undo.</div>')
			}
		});
		
		clear_button.click(function(){
			store_options.html('');
			option_trs.filter('.blink').removeClass('blink');
			preview_container.find('.blink').remove();
			make_preview();
		});
	});
</script>
<div class="p_muid_help">
	<div class="row-fluid">
		<div class="span12">
			<h3 id="help-top" class="text-center no-top">Testimonial Help</h3>
			<div class="text-center">
				<a href="#help-features"><span class="text-center help-info-highlight help-menu-button">Features</span></a>
				<a href="#help-limitations"><span class="text-center help-info-highlight help-menu-button">Limitations</span></a>
				<a href="#help-addtags"><span class="text-center help-info-highlight help-menu-button">Add Tags</span></a>
				<a href="#help-manage"><span class="text-center help-info-highlight help-menu-button">Manage</span></a>
				<a href="#help-search"><span class="text-center help-info-highlight help-menu-button">Search</span></a>
				<a href="#help-implementation"><span class="text-center help-info-highlight help-menu-button">Implementation</span></a>
				<a href="#help-options"><span class="text-center help-info-highlight help-menu-button">Options</span></a>
				<a href="#help-picker"><span class="text-center help-info-highlight help-menu-button">Picker</span></a>
			</div>
			<hr>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span5 section-highlight">
			<h3 class="text-center" id="help-features">Features</h3>
			<p class="text-center help-info-highlight">Testimonials can be used for Business Testimonials, Reviews, and Comments.</p>
			<hr>
			<h4>Component Features</h4>
			<ul class="help-list">
				<li>All Testimonials are Pending until Approved.</li>
				<li>Display your Testimonials or Reviews in a list or Carousel</li>
				<li>Load more comments, reviews, or testimonials asychronously with AJAX.</li>
				<li>Use multiple review/comments/testimonial modules perpage</li>
				<li>Externalized JavaScript/CSS, ideal for multiple modules</li>
				<li class="view-more">All Reviews/Comments can be Denied, but receive automatic Approval.</li>
				<li class="view-more">Edit Testimonials, Comments, Reviews for grammar, or quote sections.</li>
				<li class="view-more">Use Star Ratings on Reviews and Testimonials, or turn them off.</li>
				<li class="view-more">Obtain aggregate data for total review statistics.</li>
				<li class="view-more">Written with Rich Snippets in mind using Schema Attributes for search engine review/rating results.</li>
				<li class="view-more">Per Condition Configurable CSS styling to match your website.</li>
				<li class="view-more">Override the preset style rules with custom style configuration!</li>
				<li class="view-more">All reviews/comments are "Tagged" with your own custom tags.</li>
				<li class="view-more">Custom Tags can be an alias "This really cool picture" or a <abbr title="Unique Identifier">GUID</abbr> of an entity (picture or product).</li>
				<li class="view-more">Note: Only customers can make testimonials</li>
			</ul>
			<p class="text-center show-more"><a href="javascript:void(0);"><i class="icon-caret-down"></i> Show More</a></p>
			<hr/>
			<h3 class="text-center" id="help-limitations">Limitations</h3>
			<p class="help-info-plain">As of now, <strong>only customers</strong> can write testimonials, reviews, or comments on modules.</p>
			<p class="help-info-plain">Employees/Users with abilities can create testimonials on behalf of customers from the <strong>grid view</strong> of testimonials.</p>
			<p class="help-info-highlight"><i class="icon-info-sign"></i> Technically, employees/users can later <strong></strong> add tags to a testimonial in the approve/deny dialog to transform the testimonial into a review/comment for another name or entity.</p>
			<p class="help-info-plain">Aggregate data not available yet for list|carousel testimonials.</p>
			<hr/>
			<h3 class="text-center" id="help-addtags">Add Tags</h3>
			<ul class="help-list">
				<li>When approving testimonials, add tags</li>
				<li>Categorize your testimonials/reviews by adding tags to them</li>
				<li>ie, someone writes a testimonial about a particular service</li>
				<li>Later create a module or use testimonials to target that service.</li>
			</ul>
			<br/>
		</div>
		<div class="span7">
			<h3 class="text-center" id="help-manage">Manage Testimonials</h3>
			<p class="help-info" style="text-align:left;"><strong>There are two types of official "testimonials"</strong>. The <strong>default type</strong> is "testimonials" for a business/website. They only have tags "com_testimonials" and "testimonial".</p>
			<p class="help-info-plain">In <strong>Business</strong> > <strong>Testimonials</strong> > <strong>Testimonials</strong>, you will see a <strong>grid view</strong>. Pending, Approved, and Denied testimonials are accessible through this view. All testimonials must be approved before they can be retrieved by the testimonial module for display.</p>
			<p class="help-info" style="text-align:left;">The <strong>second type is "reviews"</strong>. These are aimed at being associated with an entity or reviewable item. Reviewable items could be products, pictures, stories. Thus, we use this method for comments and reviews - with or without ratings.</p>
			<p class="help-info-plain">In <strong>Business</strong> > <strong>Testimonials</strong> > <strong>Reviews</strong>, you will see a <strong>grid view</strong>. Pending, Approved, and Denied Reviews are accessible through this view. All reviews are automatically approved and can display immediately. They can, of course, be manually denied at any time.</p>
			<hr/>
			<h3 class="text-center" id="help-search">Searching Testimonials</h3>
			<p class="help-info-highlight" style="margin-bottom: 20px;">In the <strong>grid views</strong>, you will notice a text input in the top left corner of the grid. You can search by the <strong>customer's name/email/username</strong> or by searching tags.</p>
			<p class="text-center"><input type="text" value="review,comment,4958"/></p>
			<p class="text-center"> Search <strong>multiple tags</strong> by comma separating them (no spaces). Maybe 4958 is a specific guid of a product/picture/story. You should only have to search by one tag if you have the guid of the entity.</p>
			<hr>
			<h3 class="text-center" id="help-implementation">Implementation</h3>
			<p class="help-info-plain">
				Any Testimonial, Review, or Comment can be 
				easily implemented on your <strong>website content pages</strong>.
			</p>
			<hr/>
			<h4>Format - Easy Steps</h4>
			<div class="steps">
				<div class="clearfix help-info step-heading">
					<p class="step-num pull-left text-center">1</p>
					<p class="help-info-highlight pull-right step-info">
					In the content edit section of your page, create a div with a class testimonial box:
				    </p>
                </div>
				<code class="help-code text-center manual-trigger"><div class="testimonial-box"></div></code>
				<hr/>
				<div class="clearfix help-info step-heading">
					<p class="step-num pull-left text-center">2</p>
					<p class="help-info-highlight pull-right step-info">
					Add Module Code &amp; Put your options in hidden inputs within the div:
				    </p>
                </div>
				<pre class="help-code manual-trigger"><div class="testimonial-box">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Amazing Company"/>
</div>
				</pre>
				<hr/>
				<div class="clearfix help-info step-heading">
					<p class="step-num pull-left text-center">3</p>
					<p class="help-info-highlight pull-right step-info">
					If displaying as a list or a carousel, you need to hide and clear the box so a smooth transition occurs when the box is loaded:
				    </p>
                </div>
				<pre class="help-code manual-trigger"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
				</pre>
				<hr/>
				<p id="initialize-manually" class="help-info-plain clearfix">Initialize your module manually by adding the class <strong>manual-trigger</strong> to the element with class <strong>testimonial-box</strong> or its parent. Then, call this global function using the testimonial-box element.</p>
				<code class="help-code">window.create_testimonial_module($('.testimonial-box'))</code>
			</div>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<hr/>
			<h3 class="text-center" id="help-options">jQuery Modules</h3>
			<p class="text-center">Use these options to alter the code with jQuery. For developers, you can manually code testimonial modules in PHP with similar options, but they will not have any Ajax loading without using these options. Regardless of options, you always need to call the function on PHP loaded testimonials to expand the form to share.</p>
			<hr/>
		</div>
	</div>
	
	<div class="row-fluid">
		<div class="span6">
			<h3>Options</h3>
			<p class="help-info height">
				<i class="icon-danger-sign"></i> All options must be <strong>hidden inputs</strong>, and they <span class="label label-important">All Require</span> display type <strong>List</strong> or <strong>Carousel</strong>.
			</p>
			<p class="alert alert-info">
				<i class="icon-asterisk"></i> Required for List | Carousel, and therefore all modules with options.<br/><br/>
				<i class="icon-thumbs-up-alt"></i> Required for Reviews
			</p>
			<p class="alert"><i class="icon-warning-sign"></i> 
				If your carousel modules are hidden when initializing, 
				you should <a class="initialize-manually" href="#initialize-manually">initialize manually</a>.
			</p>
			<table class="table option-table table-condensed">
				<tbody>
					<tr class="option">
						<td>
							<span class="option-name option-asterisk details">review_option_display</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing, static random testimonial.</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>To set this, the value should be 'list' or 'carousel'.</li>
									<li>Indicates the type of enhanced module you'd like to display.</li>
									<li>In a carousel, your testimonials will be sorted randomly and slide, according to your limits.</li>
									<li>In a list, your testimonials will display chronologically according to your reverse option.</li>
									<li>In a list, they will only show a small limit, and continue to grow as the user scrolls to view more.</li>
									<li>See example of type = review, additional_tags = comment, review_option_name = 'Bob Joes Picture 1'</li>
									<li>Reverse = false, limit = 3, height = 200.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_reverse" value="false"/>
   <input type="hidden" name="review_option_limit" value="3"/>
   <input type="hidden" name="review_list_height" value="200"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_name" value="Bob Joes Picture 1"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="list">list</option>
									<option value="carousel">carousel</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_option_display"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_limit (for list)</span>
								<span class="label label-warning">review_option_offset = 0 (for list)</span>
								<span class="label label-warning">review_option_clear</span>
								<span class="label label-warning">review_item_name</span>
								<span class="label label-warning">review_list_height (if using a list)</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-asterisk details">review_option_clear</span>
							<div class="option-type hide">boolean</div>
							<div class="option-default hide">false</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>If set to true, the loaded module will "clear" to a loading view and then display either a list or carousel.</li>
									<li>Must be used with a display type!</li>
									<li>To avoid an awkward flash, please add style="visibility: hidden" to your testimonial-box element.</li>
									<li>It MUST be done with visibility: hidden and not display: block</li>
									<li>See example where we also change the feedback text and story text to correspond to comments instead of stories.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Foobar Comments"/>
   <input type="hidden" name="review_option_display" value="carousel"/>
   <input type="hidden" name="review_option_feedback_text" value="Add your Comment"/>
   <input type="hidden" name="review_option_story_text" value="Comment"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="true">true</option>
									<option value="false">false</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_option_clear"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
								<span class="label label-warning">review_item_name</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-asterisk details">review_item_name</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing | it will display aggregate data for testimonials.</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>The text displayed in the top bar of the module.</li>
									<li>You must use this option in conjuction with the clear option,</li>
									<li>and you must use the clear option in conjunction with a display type.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Foobar Testimonials"/>
   <input type="hidden" name="review_option_display" value="carousel"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="Item Caption/Name"/>
								<input class="option-put-value" type="hidden" name="review_item_name"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-asterisk details">review_list_height</span>
							<div class="option-type hide">int</div>
							<div class="option-default hide">Nothing, no height specified.</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>MUST be set when using lists.</li>
									<li>It will do nothing when not using lists.</li>
									<li>See example with list:</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_reverse" value="false"/>
   <input type="hidden" name="review_option_limit" value="3"/>
   <input type="hidden" name="review_list_height" value="200"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_name" value="Bob Joes Picture 1"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="Enter an integer"/>
								<input class="option-put-value" type="hidden" name="review_list_height"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display (suggested: list)</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-review details">review_option_type</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing, Assumes Testimonial</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>To set this, the value should be 'review'.</li>
									<li>Indicates that the type of testimonial this should be is a review,</li>
									<li>which associates the testimonials(reviews/comments) to an entity.</li>
									<li>Reviews <strong>are approved automatically</strong>, but can always be denied.</li>
									<li>It will expect either a name or an entity to be associated with it.</li>
									<li>You may want to add an additional tag "comment" for comments.</li>
									<li>See example of type = review, additional_tags = comment, review_option_name = 'Bob Joes Picture 1'</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
								<span class="label label-warning"><i class="icon-thumbs-up-alt"></i> Review</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_type" value="review"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_name" value="Bob Joes Picture 1"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="review">review</option>
									<option value="comment">comment</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_option_type"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display = list</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-review details">review_option_name</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Must be used with Reviews!</li>
									<li>Adds a tag by this value to the testimonial to help indicate the "reviewable" item to associate the comments to.</li>
									<li>Try to make the name, or alias, specific and unique so that it does not become confused with other testimonials with similar tags.</li>
									<li>See example of type = review, additional_tags = comment, review_option_name = 'Bob Joes Picture 1'</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
								<span class="label label-warning"><i class="icon-thumbs-up-alt"></i> Review</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_type" value="review"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_name" value="Bob Joes Picture 1"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="A unique name to use as a tag."/>
								<input class="option-put-value" type="hidden" name="review_option_name"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_type = review</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-review details">review_option_entity</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Must be used with Reviews!</li>
									<li>Adds a tag with the entity's class so that we can organize the type of entity this comment belongs to.</li>
									<li>See example of type = review, additional_tags = comment, review_option_entity = 'com_gallery_picture', review_option_entity_id = '3432'</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
								<span class="label label-warning"><i class="icon-thumbs-up-alt"></i> Review</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_type" value="review"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_entity" value="com_gallery_picture"/>
   <input type="hidden" name="review_option_entity_id" value="3432"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="The entity class."/>
								<input class="option-put-value" type="hidden" name="review_option_entity"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_type = review</span>
								<span class="label label-warning">review_option_entity_id</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-review details">review_option_entity_id</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Must be used with Reviews!</li>
									<li>Adds a tag with a guid of another entity to represent reviews/comments associated with that entity.</li>
									<li>Neither the entity nor the entity class <strong>have</strong> to exist, but the information can guarantee that the tags are unique and help retrieve information later.</li>
									<li>See example of type = review, additional_tags = comment, review_option_entity = 'com_gallery_picture', review_option_entity_id = '3432'</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
								<span class="label label-warning"><i class="icon-thumbs-up-alt"></i> Review</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_type" value="review"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_entity" value="com_gallery_picture"/>
   <input type="hidden" name="review_option_entity_id" value="3432"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="The entity guid."/>
								<input class="option-put-value" type="hidden" name="review_option_entity_id"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_type = review</span>
								<span class="label label-warning">review_option_entity_id</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name option-review details">review_submit_refresh</span>
							<div class="option-type hide">boolean</div>
							<div class="option-default hide">false</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Submits and "scrolls down" to load the user's newly added review/comment as soon as the user posts.</li>
									<li>Can only be used/noticed with a review - because testimonials have to get approved before being displayed.</li>
									<li>Only works if reverse = false, so that the newest item can be loaded at the bottom from scrolling.</li>
									<li>Can only be used with lists because of infinite lists. Carousels will never make another call to retrieve more testimonial, and they are random.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
								<span class="label label-warning"><i class="icon-thumbs-up-alt"></i> Review</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_reverse" value="false"/>
   <input type="hidden" name="review_option_limit" value="3"/>
   <input type="hidden" name="review_list_height" value="200"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_name" value="Bob Joes Picture 1"/>
   <input type="hidden" name="review_option_clear" value="true"/>
   <input type="hidden" name="review_submit_refresh" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="true">true</option>
									<option value="false">false</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_submit_refresh"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display = list</span>
								<span class="label label-warning">review_option_type = review (comment)</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_additional_tags</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Nothing</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Use a comma separated list with no spaces to add multiple tags.</li>
									<li>Add a tag like "comments" to help distinguish testimonials and to search for them easier.</li>
								</ul>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Bob Joe Being Ridiculous"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_type" value="review"/>
   <input type="hidden" name="review_option_additional_tags" value="comment"/>
   <input type="hidden" name="review_option_name" value="Bob Joes Picture 1"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="hidden" placeholder="tag,a_tag,another-tag"/>
								<input class="option-put-value" type="hidden" name="review_option_additional_tags"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display = list|carousel</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_feedback_text</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">Tell us your story. Give feedback!</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Change to any text, but not any html.</li>
								</ul>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_option_feedback_text" value="Want to share? Share it now!"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="The text to expand share form"/>
								<input class="option-put-value" type="hidden" name="review_option_feedback_text"/>
							</div>
							<div class="add-requirements hide">None</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_story_text</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">story</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Change to any text, but not any html.</li>
									<li>See example - used in conjunction with feedback_text</li>
									<li>Note: Since only customers can see the feedback form, you won't notice the example for 'story'</li>
								</ul>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_option_feedback_text" value="Add your comment"/>
   <input type="hidden" name="review_option_story_text" value="comment"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="The text to replace the word 'story'"/>
								<input class="option-put-value" type="hidden" name="review_option_story_text"/>
							</div>
							<div class="add-requirements hide">None</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_dates</span>
							<div class="option-type hide">boolean</div>
							<div class="option-default hide">false</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Using dates, the testimonial will say when the post was posted above the rating or right aligned.</li>
									<li>The date format is always in "time ago" format - as in "less than 1 minute ago" "3 days ago" etc</li>
									<li>Ideal for Reviews and Comments to help people understand which reviews/comments are new and relevant.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_option_dates" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="true">true</option>
									<option value="false">false</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_option_dates"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_quotes</span>
							<div class="option-type hide">boolean</div>
							<div class="option-default hide">true</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Whether or not to surround the testimonial in quotes.</li>
									<li>Set to False to not display the quotes.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_option_dates" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="true">true</option>
									<option value="false">false</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_option_quotes"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_ratings_off</span>
							<div class="option-type hide">boolean</div>
							<div class="option-default hide">false</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Whether or not to show/require ratings on a testimonial.</li>
									<li>Set to true to remove ratings.</li>
									<li>Ideal for <strong>comments</strong>.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_option_dates" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="true">true</option>
									<option value="false">false</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_ratings_off"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_reverse</span>
							<div class="option-type hide">boolean</div>
							<div class="option-default hide">true</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>The retrieval of testimonials/reviews starts with</li>
									<li>the most recent when reverse is true. For comments to work with the newest</li>
									<li>at the bottom, set this to false.</li>
									<li>See the example for reverse = false, limit = 10, offset = 5:</li>
									<li>Get the first 10 testimonials matching the tags [default]. Use with offset</li>
									<li>to get the first 10 after offsetting the first 5 entities.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Foobar Testimonials"/>
   <input type="hidden" name="review_option_reverse" value="false"/>
   <input type="hidden" name="review_option_limit" value="10"/>
   <input type="hidden" name="review_option_offset" value="5"/>
   <input type="hidden" name="review_option_display" value="carousel"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<select class="option-get-value">
									<option value=""></option>
									<option value="true">true</option>
									<option value="false">false</option>
								</select>
								<input class="option-put-value" type="hidden" name="review_option_reverse"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_limit</span>
							<div class="option-type hide">int</div>
							<div class="option-default hide">20</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Limits the number of testimonials to pull at a time.</li>
									<li>Lists pull infinitely, so set the limit low, to match the amount you can view at a time.</li>
									<li>Carousels pull ONCE so make the number high enough have plenty of slides.</li>
									<li>Carousels randomize the order of the entities. Lists do not.</li>
									<li>See the example for reverse = false, limit = 5, display = list</li>
									<li>Get the first 10 testimonials matching the tags [default]. Use with offset</li>
									<li>to get the first 10 after offsetting the first 5 entities.</li>
									<li>* Lists always need a height.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Foobar Comments"/>
   <input type="hidden" name="review_option_reverse" value="false"/>
   <input type="hidden" name="review_option_limit" value="3"/>
   <input type="hidden" name="review_list_height" value="200"/>
   <input type="hidden" name="review_option_display" value="list"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="Enter an integer. Low numbers for lists."/>
								<input class="option-put-value" type="hidden" name="review_option_limit"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_option_offset</span>
							<div class="option-type hide">int</div>
							<div class="option-default hide">0</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Offsets the number of testimonials to pull.</li>
									<li>MUST SET TO 0 WITH LISTS.</li>
									<li>Lists are able to be "infinite" by managing the offset,</li>
									<li>so you must not set a value for offset with lists.</li>
									<li>See the example for reverse = false, limit = 10, offset = 5:</li>
									<li>Get the first 10 testimonials matching the tags [default]. Use with offset</li>
									<li>to get the first 10 after offsetting the first 5 entities.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="option-example hide">
								<div class="clearfix">
									<pre class="help-code manual-trigger" style="margin-bottom: 20px;"><div class="testimonial-box" style="visibility: hidden;">
   [com_testimonials/testimonials /]
   <input type="hidden" name="review_item_name" value="Foobar Testimonials"/>
   <input type="hidden" name="review_option_reverse" value="false"/>
   <input type="hidden" name="review_option_limit" value="10"/>
   <input type="hidden" name="review_option_offset" value="5"/>
   <input type="hidden" name="review_option_display" value="carousel"/>
   <input type="hidden" name="review_option_clear" value="true"/>
</div>
					</pre>
								</div>
							</div>
							<div class="add-section hide">
								<input class="option-get-value" type="text" placeholder="Enter an integer. 0 for lists."/>
								<input class="option-put-value" type="hidden" name="review_option_offset"/>
							</div>
							<div class="add-requirements hide">
								<span class="label label-warning">review_option_display</span>
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
					<tr class="option">
						<td>
							<span class="option-name details">review_data_type</span>
							<div class="option-type hide">string</div>
							<div class="option-default hide">individual</div>
							<div class="option-description hide">
								<ul class="help-list">
									<li>Set to 'aggregate' to pull Aggregate data.</li>
									<li>Aggregate setting not fully functional yet: leave default.</li>
								</ul>
								<hr/>
								<span class="label">Required Options</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> Carousel</span>
								<span class="label label-warning"><i class="icon-asterisk"></i> List</span>
							</div>
							<div class="add-section hide">
								Option Currently Not Complete
							</div>
							<div class="add-requirements hide">
								Option Currently Not Complete
							</div>
						</td>
						<td><button class="btn btn-mini details">Details</button></td>
						<td><button class="btn btn-mini add">Add</button></td>
					</tr>
				</tbody>
			</table>
			
			<div class="option-modal modal hide fade in">
				<div class="modal-header">
					<button type="button" class="close pull-right" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="text-center"><span class="option-name"></span></h4>
				</div>
				<div class="modal-body">
					<h4 class="help-info-highlight">Information</h4>
					<div class="option-section">
						<table class="table">
							<thead>
								<tr>
									<th>Type</th>
									<th>Default</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="option-type"></td>
									<td class="option-default"></td>
								</tr>
							</tbody>
						</table>
					</div>
					<hr>
					<h4 class="help-info-highlight">Description</h4>
					<div class="option-description option-section more-padding"></div>
					<hr>
					<h4 class="help-info-highlight">Example</h4>
					<div class="option-example option-section more-padding"></div>
					<hr>
					<div class="option-module">
						<div class="hide clone-from"><?php echo $pines->format_content('[com_testimonials/testimonials /]'); ?></div>
						<div class="clone-to clearfix"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button style="border-radius: 20px; margin-top: 4px;" data-dismiss="modal" class="btn btn-large btn-primary">OK</button>
				</div>
			</div>
			
			<div class="add-modal modal hide fade in">
				<div class="modal-header">
					<button type="button" class="close pull-right" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="text-center">Add <span class="add-name"></span></h4>
				</div>
				<div class="modal-body">
					<h4 class="help-info-highlight">Add Option</h4>
					<div class="add-section option-section more-padding text-center"></div>
					<hr>
					<h4 class="help-info-highlight">Requirements</h4>
					<div class="add-requirements option-section more-padding"></div>
				</div>
				<div class="modal-footer">
					<button style="border-radius: 20px; margin-top: 4px;" data-dismiss="modal" class="btn btn-large btn-primary">Cancel</button>
					<button style="border-radius: 20px; margin-top: 4px;" data-dismiss="modal" class="btn btn-large btn-success add-modal-button">Add</button>
				</div>
			</div>
		</div>
		<div class="span6">
			<h3 class="text-center" id="help-picker">Preview</h3>
			<div class="help-info height">
				Here is a preview based on your options.
				<p style="margin-top: 20px;">
					<span class="btn-group">
						<button class="btn btn-primary undo-button"><i class="icon-undo"></i> Undo</button>
						<button class="btn btn-primary preview-button"><i class="icon-eye-open"></i> Preview</button>
						<button class="btn btn-primary clear-button"><i class="icon-eraser"></i> Clear</button>
					</span>
				</p>
			</div>
			<hr/>
			<h4>The Preview:</h4>
			<div class="preview clearfix">
				<div style="padding: 30px; font-weight:bold;" class="text-center">
					<i class="icon-spin icon-spinner icon-2x"></i>
					<div style="margin-top: 10px;">Loading...</div>
				</div>
			</div>
			<div class="store-options hide"></div>
			<hr/>
			<h4>The Code:</h4>
			<textarea class="module-code">Stuff.</textarea>
		</div>
	</div>
</div>