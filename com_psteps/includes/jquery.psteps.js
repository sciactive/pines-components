/*
 * jQuery Pines Steps (psteps) Plugin 0.0.1alpha
 *
 * http://pinesframework.org/psteps/
 * Copyright (c) 2012 Angela Murrell
 *
 * Triple license under the GPL, LGPL, and MPL:
 *	  http://www.gnu.org/licenses/gpl.html
 *	  http://www.gnu.org/licenses/lgpl.html
 *	  http://www.mozilla.org/MPL/MPL-1.1.html
 */

(function($) {
	$.fn.psteps = function(options) {
		// Build main options before element iteration.
		var opts = $.extend({}, $.fn.psteps.defaults, options);

		// Iterate and transform each matched element.
		var all_elements = this;
		all_elements.each(function(){
			var psteps = $(this);
			psteps.psteps_version = "0.0.1alpha";

			// Check for the ptags class. If it has it, we've already transformed this element.
			if (psteps.hasClass("steps-transformed")) return true;
			// Add the ptags class.
			psteps.addClass("steps-transformed");

			psteps.extend(psteps, opts);

			// All arrays and objects in our options need to be copied,
			// since they just have a pointer to the defaults if we don't.
			//psteps.example_property = psteps.example_property.slice();

			// Step submit and next button variables
			var all_steps = psteps.find('.step-content');
			var all_titles = psteps.find('.step-title');
			var next_button = psteps.find('.next-button');
			var back_button = psteps.find('.back-button');
			var send_button = psteps.find('.submit-button');
			var toggle_buttons = psteps.find('.next-button, .submit-button');
			var num_steps = psteps.find('.step-title').length;

			// Ensure steps width always looks good (mobile)
			if (psteps.steps_width_percentage) {
				var percentage = num_steps * 10;
				if ($(window).width() < 650 )
					psteps.find('.step-title').css('width', percentage+'%');
			}

			// When viewport is small, do not display step names. Show numbers only.
			if (psteps.shrink_step_names) {
				if ($(window).width() < 650 )
					psteps.find('.step-name').css('display', 'none');
			}

			// Function for adjusting progress title bars on textarea change.
			// All Validation happens here.
			psteps.check_progress_titles = function(){
				var i = 1;
				psteps.find('.step-content').each(function(){
					var r = 1;
					var cur_step = $(this);
					var class_to_add = 'pstep'+i;
					cur_step.addClass(class_to_add);
					psteps.find('.step-title').each(function(){
						if (r == i) {
							// this title matches the textarea
							var title = $(this);
							// Titles are always colored to indicate progress for present/past steps
							// If you can click titles, colored progress will indicate for future steps too.
							if ((psteps.traverse_titles == 'visited' && title.hasClass('step-visited')) || (psteps.traverse_titles == 'never' && title.hasClass('step-visited')) || psteps.traverse_titles == 'always') {
								var validate_result = psteps.validation_rule.call(cur_step);
								if (validate_result == 'warning') {
									title.addClass('step-warning');
									psteps.trigger_error(cur_step);
								} else if (validate_result == 'error') {
									title.addClass('step-error');
									psteps.trigger_error(cur_step);
								} else if (validate_result) {
									title.removeClass('btn-info').addClass('btn-success').find('i').remove().end().prepend('<i class="icon-ok"></i> ');
								} else if (!validate_result) {
									title.removeClass('btn-success')
										.addClass('btn-info')
										.find('i').remove();
								}
							}
						}
						r++;
					});
					i++;
				});
				psteps.toggle_buttons_function();
			}

			// Function for toggling send/next buttons as btn-success or btn-info.
			psteps.toggle_buttons_function = function(){
				// Toggle whether to show send or next.
				if (psteps.find('.step-content').last().hasClass('step-active')) {
					next_button.hide();
					send_button.show();
				} else {
					next_button.show();
					send_button.hide();
				}

				// Changes color of send/next buttons based upon completion.
				var active_title = psteps.find('.step-title.step-active');
				if (active_title.hasClass('btn-success') || active_title.hasClass('step-warning'))
					toggle_buttons.removeClass('btn-info').addClass('btn-success');
				else if (active_title.hasClass('btn-info'))
					toggle_buttons.removeClass('btn-success').addClass('btn-info');
				else if (active_title.hasClass('step-error'))
					toggle_buttons.removeClass('btn-success btn-info').addClass('btn-danger');

				// Check submit button for all steps if necessary
				if (psteps.validate_submit_all_steps) {
					var incomplete;
					all_titles.each(function(){
						var invalid_before = incomplete;
						if (!$(this).hasClass('btn-success'))
							incomplete = true;
						if ($(this).hasClass('step-warning') && !invalid_before)
							incomplete = false;
					});
					if (incomplete)
						send_button.removeClass('btn-success').addClass('btn-info');
				}

				// Back Button
				if (psteps.back) {
					if (psteps.find('.step-title').first().hasClass('step-active')) {
						back_button.hide();
					} else {
						var previous_title = psteps.find('.step-title.step-active').prev('.step-title');
						if (previous_title.hasClass('btn-info'))
							back_button.removeClass('btn-success btn-warning btn-danger').addClass('btn-info').css('cursor', 'pointer');
						else if (previous_title.hasClass('btn-success'))
							back_button.removeClass('btn-info btn-warning btn-danger').addClass('btn-success').css('cursor', 'pointer');
						else if (previous_title.hasClass('btn-warning'))
							back_button.removeClass('btn-info btn-success btn-danger').addClass('btn-warning').css('cursor', 'pointer');
						else if (previous_title.hasClass('btn-danger'))
							back_button.removeClass('btn-success btn-warning btn-info').addClass('btn-danger').css('cursor', 'pointer');

						back_button.show();
					}
				} else {
					back_button.hide();
				}
			}

			// Function to go to a certain step
			psteps.go_to_step = function(step_num){
				var c = 1;
				var active_step = psteps.find('.step-content.step-active');
				var active_title = psteps.find('.step-title.step-active');
				var show_step;
				var show_title;

				if (step_num > num_steps) 
					return;

				psteps.find('.step-content').each(function(){
					if (c == step_num) {
						show_step = $(this);
					}
					c++;
				});
				c = 1;
				psteps.find('.step-title').each(function(){
					if (c == step_num) {
						show_title = $(this);
					}
					c++;
				});

				if (!show_step.hasClass('step-loaded'))
					psteps.steps_onload.call(show_step);

				active_step.hide().removeClass('step-active');
				show_step.show().addClass('step-active step-visited step-loaded');

				active_title.removeClass('step-active').addClass('disabled');
				show_title.addClass('step-active step-visited').removeClass('disabled');

				// If visisted traversing,
				if (psteps.traverse_titles == 'visited') {
					active_title.css('cursor', 'pointer');
				}

				psteps.check_progress_titles();
			}

			// Function to go to the next step (calls go to step)
			psteps.next_step_function = function(){
				var preceeding_titles = psteps.find('.step-title.step-active').prevAll('.step-title');
				var num = preceeding_titles.length + 2;
				psteps.go_to_step(num);
			}

			// Function to go to the next step (calls go to step)
			psteps.previous_step_function = function(){
				var preceeding_titles = psteps.find('.step-title.step-active').prevAll('.step-title');
				var num = preceeding_titles.length;
				psteps.go_to_step(num);
			}

			// Function for traversing steps through the titles.
			psteps.traverse_titles_function = function(){
				if (psteps.traverse_titles == 'always') {
					var step_titles = psteps.find('.step-title');
					step_titles.click(function(){
						var clicked_title = $(this);
						var all_prev = clicked_title.prevAll('.step-title');
						var click_num = all_prev.length + 1;
						psteps.go_to_step(click_num);
					}).css('cursor', 'pointer');
				} else if (psteps.traverse_titles == 'visited') {
					psteps.on('click', '.step-title.step-visited', function(){
						var clicked_title = $(this);
						// if the title is the "next" title from the current view,
						// trigger next.
						// if the title is the "previous" title from the current view,
						// trigger previous.
						if (clicked_title.prev('.step-title').hasClass('step-active'))
							psteps.next_step_function();
						else if (clicked_title.next('.step-title').hasClass('step-active'))
							psteps.previous_step_function();
						else {
							var all_prev = clicked_title.prevAll('.step-title');
							var click_num = all_prev.length + 1;
							psteps.go_to_step(click_num);
						}
					});
				} 
			}


			// Trigger Error in Title
			psteps.trigger_error = function(the_step) {
				var step_num = the_step.prevAll('.step-content').length + 1;
				var title;
				var c = 1;
				psteps.find('.step-title').each(function(){
					if (c == step_num)
						title = $(this);
					c++;
				});
				if (title.hasClass('step-warning'))
					title.removeClass('btn-info btn-success').addClass('btn-warning').find('i').remove().end().prepend('<i class="icon-remove"></i> ');
				else if (title.hasClass('step-error'))
					title.removeClass('btn-info btn-success').addClass('btn-danger').find('i').remove().end().prepend('<i class="icon-remove"></i> ');
			}

			// Load necessary classes
			all_steps.hide().first().addClass('step-visited step-active').show();
			all_titles.addClass('disabled').first().addClass('step-visited step-active').removeClass('disabled');

			// Load functions
			psteps.traverse_titles_function();
			psteps.check_progress_titles();

			// Load the default step
			if (psteps.start_incomplete_step) {
				var incomplete = psteps.find('.step-title.btn-info').first();
				var all_prev = incomplete.prevAll('.step-title');
				var num = all_prev.length + 1;
				psteps.go_to_step(num);
			} else {
				psteps.go_to_step(psteps.step_start);
			}

			// Event Triggers
			back_button.click(function(){
				psteps.previous_step_function();
			});

			psteps.bind('validate_psteps', function(){
				psteps.check_progress_titles();
			});

			all_steps.bind('psteps_step_error', function(){
				psteps.trigger_error($(this));
			});

			// Submit or Next. Checks for success in order to progress. Stops submit if fails.
			toggle_buttons.click(function(e){
				var this_button = $(this);
				var active_title = psteps.find('.step-title.step-active');
				if (active_title.hasClass('step-error')) {
					if (psteps.validate_use_error_msg) {
						alert(psteps.validate_error_msg)
					} else {
						active_title.click();
					}
				} else if (this_button.hasClass('btn-success')) {
					psteps.next_step_function();
				} else if (this_button.hasClass('submit-button') && (psteps.validate_submit_all_steps || psteps.validate_next_step)) {
					alert(psteps.before_submit);
					e.preventDefault();
				} else if (psteps.validate_next_step)
					alert(psteps.before_next);
				else
					psteps.next_step_function();
			});

			// Save the ptags object in the DOM, so we can access it.
			this.pines_steps = psteps;
		});

		return all_elements;
	};

	$.fn.psteps.defaults = {
		// Set how progress titles can be traversed.
		// 'always' = always have the ability to traverse all steps.
		// 'visited' = visited means the user has gone backwards and can
		// traverse steps already visited.
		// 'never' = never means the user cannot traverse steps other
		// than through progression.
		traverse_titles: "never",
		// Make width of step titles an even percentage for the number of steps.
		steps_width_percentage: false,
		// Do not display step names in small viewports, just numbers.
		shrink_step_names: true,
		// Set if there is a back button.
		back: true,
		// Set the default step.
		step_start: "1",
		// Use the steps onload function to customize events that happen
		// when a step is loaded, or rather viewed the first time. (First time only).
		steps_onload: function(){},
		// Go to the first incomplete step
		start_incomplete_step: false,
		// Function to determine that progress has been made (For titles
		// and for progression). This function received an argument 'step' which is 
		// The current step it's checking for validation.
		// Making it really easy to do something like:
		// return (step.find('textarea').val() != '')
		// You can return true, false, 'warning', 'error'.
		// each step has a class of pstep# ie (pstep1). You can check if the step
		// has the class and then write a specific validation rule for that step.
		// you can throw errors by returning 'error' and an alert. You can use that
		// alert error message in place of the default by setting validate_use_error_msg
		// to false. That way you can create custom alert messages and have them work
		// as the alert when clicking next.
		validation_rule: function(){
			return true;
		},
		// Validate the current step before advancing to the next step.
		validate_next_step: true,
		// Validate all steps before submitting.
		validate_submit_all_steps: true,
		// Validation for errors. If validating next step is off, this is still true.
		// Also if validating for next step, and this is false, it will just
		// use the normal next step validation alert.
		// This will not affect traversing titles.
		validate_errors: true,
		// Default validation error message. You can change it...
		validate_error_msg: 'There was an error processing the step.',
		// Use validate error message. If you don't use it, but you DO
		// use the validate_errors option, the error message will be
		// whatever alert you used on that step, if you used one.
		validate_use_error_msg: true,
		// The alert text to display to the user when the steps fail validation.
		before_submit: 'Please complete all of the required steps before submitting.',
		// The alert text to display to the user when the step fails validation.
		before_next: 'Please complete this step before advancing to the next step.'
	};
})(jQuery);
