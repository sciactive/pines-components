<?php
/**
 * The view to load into the head section to attach css and javascript for a testimonial module.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

header("Content-type: text/javascript");
$save_testimonial = json_encode(pines_url('com_testimonials', 'testimonial/save'));
$get_testimonials = json_encode(pines_url('com_testimonials', 'testimonial/get_testimonials'));
$logged_in = !empty($_SESSION['user']);
$customer = ($logged_in && $_SESSION['user']->has_tag('customer'));
?>
//script tag used for ide purposes only
//<script type="text/javascript">
pines(function(){
		
		var create_testimonial_module = function(parent) {
			var testimonials_container = parent.find('.testimonials-module'),
				feedback_container = testimonials_container.find('.give-feedback'),
				trigger_feedback = feedback_container.find('.trigger-feedback'),
				feedback_form = testimonials_container.find('#feedback_form'),
				anon_check = feedback_form.find('[name="anon"]'),
				share_check = feedback_form.find('[name="share"]'),
				submit = testimonials_container.find('.submit-button'),
				feedback_textarea = testimonials_container.find('[name=feedback]'),
				share_again = testimonials_container.find('.share-again'),
				form_submit = testimonials_container.find('.form-submit'),
				form_content = testimonials_container.find('.form-content'),
				please_rate = form_submit.find('.please-rate-us'),
				status_icon = form_submit.find('.feedback-status-icon'),
				status_words = form_submit.find('.feedback-status-words'),
				stars = form_content.find('.star'),
				please_stars = please_rate.find('.star'),
				rating_container = form_content.find('.rating-container'),
				stars_container = form_content.find('.star-container'),
				average_rating = testimonials_container.find('.average-rating'),
				star_rating = average_rating.find('.star-rating'),
				votes = average_rating.find('.votes'),
				testimonials_testimonials = testimonials_container.find('.testimonials'),
				login_container = testimonials_container.find('.login-container');

			// Get all testimonial display javascript variables
			var test_loader = testimonials_container.find('.testimonial-loader'),
				loaded_testimonial = testimonials_container.find('.loaded-testimonial'),
				testimonial_box = parent,
				average_rating_box = testimonials_container.find('.average-rating'),
				no_average_rating_box = testimonials_container.find('.no-average-rating'),
				story_spans = testimonials_container.find('.story'),
				list_container = testimonials_container.find('.testimonial-list-container'),
				list_more = testimonials_container.find('.list-read-more'),
				list_up = testimonials_container.find('.list-up'),
				list_top = testimonials_container.find('.list-top');


			// Get all testimonial display variables
			if (!testimonial_box.length) {
				console.log('Please wrap your testimonial module in an element with a class testimonial-box. Put all your options in inputs within that same box.')
				return;
			}

			// Get all testimonial display options
			var review_reverse = testimonial_box.find('[name=review_option_reverse]'),
				review_limit = testimonial_box.find('[name=review_option_limit]'),
				review_offset = testimonial_box.find('[name=review_option_offset]'),
				review_type = testimonial_box.find('[name=review_option_type]'),
				review_data_type = testimonial_box.find('[name=review_data_type]'),
				review_display = testimonial_box.find('[name=review_option_display]'),
				review_tags = testimonial_box.find('[name=review_option_additional_tags]'),
				review_clear = testimonial_box.find('[name=review_option_clear]'),
				review_feedback_text = testimonial_box.find('[name=review_option_feedback_text]'),
				review_story_text = testimonial_box.find('[name=review_option_story_text]'),
				review_item_name = testimonial_box.find('[name=review_item_name]'),
				review_list_height = testimonial_box.find('[name=review_list_height]'),
				review_option_dates = testimonial_box.find('[name=review_option_dates]'),
				review_ratings_off = testimonial_box.find('[name=review_ratings_off]'),
				review_submit_refresh = testimonial_box.find('[name=review_submit_refresh]'),
				review_module_hidden = testimonial_box.find('[name=review_module_hidden]'),
				review_option_quotes = testimonial_box.find('[name=review_option_quotes]');

			if (review_type.val() == 'review') {
				// define more variables
				var review_entity = testimonial_box.find('[name=review_option_entity]');
				var review_entity_id = testimonial_box.find('[name=review_option_entity_id]');
				var review_name = testimonial_box.find('[name=review_option_name]');
			}
			
			<?php if (!$logged_in) { ?>
					// If not logged in, guarantee a hide
					login_container.find('.pf-element').first().hide();
			<?php } ?>

			$(window).resize(function(){
				if (testimonials_container.width() < 600)
					testimonials_container.addClass('small')
				else
					testimonials_container.removeClass('small');
			}).resize();

			trigger_feedback.click(function(){
				console.log('here');
				if (feedback_container.hasClass('opened')) {
					feedback_container.removeClass('opened')
					feedback_form.fadeOut(50);
				} else {
					feedback_container.addClass('opened')
					feedback_form.fadeIn(1000);
				}
			});

			share_check.change(function(){
				if ($(this).is(':checked')) {
					anon_check.removeAttr('disabled').closest('label').show();
				} else 
					anon_check.attr('disabled', 'disabled').closest('label').hide();
			});

			share_again.click(function(){
				status_icon.addClass('icon-spin icon-spinner').removeClass('icon-ok');
				status_words.text('Submitting');
				share_again.css('visiblity', 'hidden');
				if (status_words.text() != 'Error. Not Submitted.')
					feedback_textarea.val('');
				form_submit.hide();
				form_content.fadeIn();
				// Reset the height
				form_content.css('height', 'auto');
			});

			// Stars
			please_stars.click(function(){
				var cur_star = $(this);
				var num = cur_star.prevAll('.star').length;
				stars.eq(num).click();
				// submit now
				status_icon.show();
				submit.click();
			});
			stars.click(function(){
				var clicked_star = $(this);
				var rated = clicked_star.nextAll('.star').andSelf(); // deprecated changed to addBack();
				var rating = rated.length;

				stars.removeClass('rated');
				rated.addClass('rated')
				stars.hide();

				if (rating > 3)
					stars_container.append('<span class="remove"><i class="icon-ok"></i> <span>Thanks!</span></span></span>').fadeIn(200);
				else
					stars_container.append('<span class="remove" style="font-size: 14px;"><i class="icon-ok"></i></span>').fadeIn(200);

				setTimeout(function(){
					stars_container.find('.remove').remove();
					stars.fadeIn();
				}, 700);
			});
			<?php if ($logged_in && $customer) { ?>
			submit.click(function(e){
                e.preventDefault();
				please_rate.hide();
				var height = feedback_form.height() - 11;
				// Set the height so that it doesn't move weird when we send other messages to this form area.
				form_submit.height(height);

				if (check_value(feedback_textarea) != "" && (form_content.find('.rated').length || check_value(review_ratings_off) == 'true')) {
					// This is where we make the ajax submit thing

					var feedback = feedback_textarea.val(),
						share = share_check.is(':checked') ? 'ON' : '',
						anon = anon_check.is(':checked') ? 'ON' : '',
						rating = form_content.find('.rated').length,
						customer_guid = <?php echo json_encode($_SESSION['user']->guid);?>;

					form_content.hide();
					form_submit.fadeIn();

					var values = {};
					values.type = 'module';
					values.customer = customer_guid;
					values.feedback = feedback;
					values.share = share;
					values.anon = anon;
					values.rating = rating;
					// Add some possible inputs from the module
					values.review_option_entity = check_value(review_entity);
					values.review_option_entity_id = check_value(review_entity_id);
					values.review_option_additional_tags = check_value(review_tags);
					values.review_option_name = check_value(review_name);
					values.review_option_type = check_value(review_type);
					values.review_data_type = check_value(review_data_type);

					$.ajax({
						url: <?php echo $save_testimonial; ?>,
						type: "POST",
						dataType: "json",
						data: values,
						beforeSend: function() {
							status_icon.addClass('icon-spin icon-spinner').removeClass('icon-ok');
							status_words.text('Submitting');
							share_again.css('visibility', 'hidden');
						}, 
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
							status_icon.removeClass('icon-spin icon-spinner').removeClass('icon-remove');
							status_words.text('Error. Not Submitted.');
							share_again.hide().text('Try Again?').css('visibility', 'visible').fadeIn();
						},
						success: function(data){
							if (!data) {
								status_icon.removeClass('icon-spin icon-spinner icon-warning-sign icon-ok').addClass('icon-remove');
								status_words.text('Error. Not Submitted.');
								share_again.hide().text('Try Again?').css('visibility', 'visible').fadeIn();
								return false;
							}

							if (data) {
								status_icon.removeClass('icon-spin icon-spinner icon-warning icon-remove').addClass('icon-ok');
								status_words.text('Success!');
								if (check_value(review_story_text) != '')
									var text = review_story_text.val();
								else
									var text = 'Story';
								share_again.hide().text('Share another '+text+'?').css('visibility', 'visible').fadeIn();
								if (check_value(review_submit_refresh) == 'true' && check_value(review_display) == 'list') {
									// Actually load more testimonials
									// Scroll to bottom first
									var scroll = list_container.height();
									list_container.animate({scrollTop: scroll}, function(){
										list_more.click();
									});
								}
								return false;
							}

							if (!data.result) {
								status_icon.removeClass('icon-spin icon-spinner icon-ok icon-warning-sign').addClass('icon-remove');
								status_words.text(data.message);
								share_again.hide().text('Try Again?').css('visibility', 'visible').fadeIn();
								return false;
							}

						}
					});
				} else if (feedback_textarea.val() != "" && check_value(review_ratings_off) != 'true') {
					// They have completed the story but they didn't rate us.
					// Make them rate us here.
					status_icon.hide();
					please_rate.show();
					status_words.text('Please Rate Us!');
					form_content.hide();
					share_again.css('visibility', 'hidden');
					form_submit.fadeIn();
				} else {
					// Hide the content, show the submit
					status_icon.removeClass('icon-spin icon-spinner icon-remove').addClass('icon-warning-sign');
					status_words.text('Incomplete!');
					share_again.css('visibility', 'hidden');
                                        setTimeout(function(){
                                            form_content.hide();
                                        }, 20);
                                        form_submit.fadeIn();
					

					setTimeout(function(){
						status_icon.addClass('icon-spin icon-spinner').removeClass('icon-ok icon-warning-sign icon-remove');
						status_words.text('Submitting');
						share_again.css('visibility', 'hidden');
                                                setTimeout(function(){
                                                    form_submit.hide();
                                                }, 20);
						form_content.fadeIn();
						// Reset the height
						feedback_form.css('height', 'auto');
					}, 500);
				}
                            return false;
			});
			<?php } ?>

			// BEGIN RETRIEVAL 
			var check_value = function(element) {
				return (element == undefined) ?  '' : element.val();
			}
			
			// Randomize the order of the JavaScript Array (for carousel)
			if (check_value(review_display) == 'carousel') {
				var shuffle = function(array) {
					var currentIndex = array.length,
						temporaryValue,
						randomIndex;

					// While there remain elements to shuffle...
					while (0 !== currentIndex) {

					  // Pick a remaining element...
					  randomIndex = Math.floor(Math.random() * currentIndex);
					  currentIndex -= 1;

					  // And swap it with the current element.
					  temporaryValue = array[currentIndex];
					  array[currentIndex] = array[randomIndex];
					  array[randomIndex] = temporaryValue;
					}

					return array;
				  };
			}

			// this will be used to determine all the options we need and what functions to call for the review/testimonials
			var get_testimonials = function(){
				// Change text
				if (check_value(review_feedback_text) != '') {
					trigger_feedback.text(review_feedback_text.val());
				}
				if (check_value(review_story_text) != '') {
					story_spans.text(review_story_text.val());
				}

				// Determine if we need to hide the php loaded one with a loading throbber.
				if (check_value(review_clear) == 'true') {
					loaded_testimonial.hide();
					average_rating_box.hide();
					if (check_value(review_item_name) != '')
						no_average_rating_box.text(pines.safe(review_item_name.val()));
					no_average_rating_box.show();
					testimonial_box.hide();
					testimonial_box.css('visibility', 'visible');
					testimonial_box.fadeIn();
					test_loader.fadeIn();
				}

				// Determine how to display the testimonials
				// If list, then call list function
				var options = {};
				options.review_reverse = check_value(review_reverse);
				options.review_limit = check_value(review_limit);
				options.review_offset = check_value(review_offset);
				options.review_tags = check_value(review_tags);
				options.review_entity = check_value(review_entity);
				options.review_entity_id = check_value(review_entity_id);
				options.review_name = check_value(review_name);
				options.review_option_type = check_value(review_type);
				options.review_data_type = check_value(review_data_type);
				options.review_ratings_off = check_value(review_ratings_off);

				if (check_value(review_ratings_off) == 'true') {
					// Remove rating container
					rating_container.remove();
				}

				if (check_value(review_display) == 'list') {
					list_testimonials(options);
					return;
				}

				// Do this for carousel
				// Only do a carousel if it is an option
				if (check_value(review_display) == 'carousel')
					load_testimonials(options, 'carousel');
			};

			// Use to create list
			var list_testimonials = function(options){
				testimonials_testimonials.addClass('make-list');
				if (check_value(review_list_height) != '') {
					testimonials_testimonials.css('height', review_list_height.val()+'px');
				}
				// Capture the first time here, and then never again
				// To create the scroll and click rule
				if (!testimonials_container.hasClass('list-started')) {
					testimonials_container.addClass('list-started');
					var scroll_increment = (check_value(review_list_height) != '') ? parseInt(review_list_height.val()) - 40 : 120;
					// Define scroll rule
					// Define click rule on list more
					list_more.click(function(){
						if (list_container[0].scrollHeight - list_container.scrollTop() == list_container.height()) {
							list_testimonials(options);
						}
						var scroll = list_container.scrollTop() + scroll_increment;
						list_container.animate({scrollTop: scroll});
					});
					// Capture scrolling
					list_container.scroll(function(){
						if (list_container[0].scrollHeight - list_container.scrollTop() == list_container.height()) {
							list_testimonials(options);
						}
					});
					// Define click rule for list up
					list_up.click(function(){
						var scroll = list_container.scrollTop() - scroll_increment;
						list_container.animate({scrollTop: scroll});
					});
					// Define click rule for list up
					list_top.click(function(){
						list_container.animate({scrollTop: 0});
					});
				}

				// You only need to increment the offset
				// So you WILL need to get this value every time
				options.review_offset = check_value(review_offset);

				// Load the testimonials
				load_testimonials(options, 'list');

				// what if we always make the offset exactly the number of loaded reviews/testimonials
				// IF that's the case we shouldnt reset it here, but rather after the result of the ajax call.

				// this is in charge of calling load testimonials in increments as the user clicks read more or scrolls down.
			};

			// the function to be used by all types of calls to retrieve reviews/testimonials
			var load_testimonials = function(options, display){
				// Using arguments, make ajax call to retrieve testimonials
				$.ajax({
					url: <?php echo $get_testimonials; ?>,
					type: "POST",
					dataType: "json",
					data: options,
					beforeSend: function() {
						// Here you will want to make the list-more have a loader icon
						// But you really won't want to have loading things happening with carousel, it should happen once and
						// hopefully not obstructively
						// and it should happen once in the beginning to load the first X amount of items for the list.
						if (display == 'list') {
							list_more.css('visibility', 'hidden');
							list_more.find('i').remove();
							list_more.prepend('<i class="icon-spin icon-spinner"></i> ');
							list_more.hide().css('visibility', 'visible').fadeIn();
						}
					}, 
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
						if (display == 'list') {
							list_more.find('i').remove();
							list_more.text('Error Loading...');
							list_more.prepend('<i class="icon-remove"></i> ');
						}
						// I don't know what kind of error you want for the carousel.
						// If we cleared then maybe show error, but if not a clear - leave it
						if (check_value(review_clear) == 'true') {
							test_loader.text('An Error Occurred.');
							test_loader.find('i').removeAttr('class').addClass('icon-remove')
						}
					},
					success: function(data){
						if (data == 'No testimonials found.') {
							// Fix the offset now.
							var cur_loaded = list_container.find('.testimonial.item').length;
							review_offset.val(cur_loaded); // Set the offset so next time we load the right increment
							
							if (test_loader.is(':visible')) {
								test_loader.text('Be the First to Share!');
								test_loader.find('i').removeAttr('class').addClass('icon-thumbs-up');
								list_more.find('i').remove();
								list_more.text('Share yours!');
								list_more.prepend('<i class="icon-edit"></i> ');
								list_more.css('visibility', 'hidden');
								list_more.hide().css('visibility', 'visible').fadeIn();
								return;
							} else {
								list_more.find('i').remove();
								list_more.text('No More Reviews. Share yours!');
								list_more.prepend('<i class="icon-edit"></i> ');
								list_more.css('visibility', 'hidden');
								list_more.hide().css('visibility', 'visible').fadeIn();
								return;
							}
						}
						test_loader.hide();
						if (display == 'list') {
							list_more.find('i').remove();
							list_more.css('visibility', 'hidden');
							list_more.hide().css('visibility', 'visible').fadeIn();
						} else {
							// remove the placeholder for lists if it's a carousel.
							list_container.find('.list-placeholder').remove();
							list_more.hide();
						}
						// Depending on the display, add items to list or to carousel
						// Remember we will fix the offset in add_testimonials, once they are added.
						list_container.fadeIn();
						add_testimonials(data, display);
					}
				});


				// On success, call function to put the testimonials in either a list or a carousel
				// call function to add item(s)
			};

			// Construct a testimonial
			var construct_testimonial = function(object) {
				var blockquote = $('<blockquote></blockquote>');
				blockquote.append('<meta content="'+check_value(review_item_name)+'" itemprop="about"/>');
				blockquote.append('<meta content="'+check_value(review_item_name)+'" itemprop="name"/>');
				if (check_value(review_option_quotes) == 'false')
					blockquote.append('<p class="description" itemprop="description">'+object.testimonial+'</p>');
				else
					blockquote.append('<p class="description" itemprop="description">"'+object.testimonial+'"</p>');
				if (object.author != false) {
					var just_author = object.author.replace(/ in.*$/, '');
					var just_place = ' in'+object.author.replace(/.*?in/, '');
					<?php if (gatekeeper('com_testimonials/showentityhelp')) { ?>
					blockquote.append('<small><a data-entity="'+object.guid+'" data-entity-context="com_testimonials_testimonial"><span itemprop="author">'+just_author+'</span></a>'+just_place+'</small>');
					<?php } else { ?>
					blockquote.append('<small><span itemprop="author">'+just_author+'</span>'+just_place+'</small>');
					<?php } ?>
				} else {
					<?php if (gatekeeper('com_testimonials/viewanonauthor')) { ?>
					blockquote.append('<small>Posted Anonymously by <a data-entity="'+object.guid+'" data-entity-context="com_testimonials_testimonial"><span itemprop="author">'+object.customer_name+'</span></a></small>');
					<?php } ?>
				}
				if (check_value(review_ratings_off) != 'true') {
					var stars = $('<div class="pull-right rating-container"><span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><meta itemprop="worstRating" content="1"><meta itemprop="bestRating" content="5"><meta itemprop="ratingValue" content="'+object.rating+'"></span></div>');
					for (var c = 1; c <= 5; c++) {
						if (object.rating >= c) {
							stars.find('span').append('<i class="icon-star"></i> ');
						} else {
							stars.find('span').append('<i class="icon-star-empty"></i> ');
						}
					}
					blockquote.append(stars);
				}
				
				if (check_value(review_option_dates) != '') {
					if (review_option_dates.val() == 'true') {
						var date = $('<div style="clear:both" class="pull-right"></div>');
						date.append('<abbr itemprop="datePublished" class="timeago" title="'+object.timeago+'">'+object.date+'</abbr>');
						blockquote.append(date);
					} else {
						blockquote.append('<meta content="'+object.date+'" itemprop="datePublished"/>');
					}
				}
				
				var item = $('<div class="testimonial item hide" itemtype="http://schema.org/Review" itemscope="" itemprop="review"><div class="content clearfix"></div><div class="item-bottom-border"></div></div>');
				item.find('.content').append(blockquote);
				item.find('.timeago').timeago();
				return item;
			}

			// Use to add items
			var add_testimonials = function(data, display){

				// Add Items to List
				if (display == 'list') {
					$.each(data, function(index, value){
						var item = construct_testimonial(value);
						list_container.find('.list-placeholder').before(item);
						item.fadeIn();
					});
				} else {
					// Randomize the data
					data = shuffle(data);
					// Add Items to Carousel
					// Add Carousel Class to testimonials_testimonials
					testimonials_testimonials.addClass('carousel slide');
					// Add Carousel-inner class to testimionial-list-container
					list_container.addClass('carousel-inner');
					var max = -1;
					
					$.each(data, function(index, value){
						var item = construct_testimonial(value);
						list_container.append(item);
						if (check_value(review_module_hidden) == 'true') {
							var temp_item = item.clone().addClass('remove-temp-item');
							$('body').append(temp_item)
							temp_item.css({
								'visibility': 'hidden',
								'position': 'absolute',
								'display': 'block'
							});
							// figure out max height
							var h = temp_item.height();
							max = h > max ? h : max;
							item.find('.item-bottom-border').remove();
						} else {
							item.css('visibility', 'hidden');
							// figure out max height
							var h = item.height();
							max = h > max ? h : max;
							item.find('.item-bottom-border').remove();
						}
						
					});
					
					if (check_value(review_module_hidden) == 'true') {
						$('.remove-temp-item').remove();
					}
					
					testimonials_testimonials.height(max);
					loaded_testimonial.hide();
					list_container.find('.item').each(function(){
						var this_item = $(this);
						this_item.css({
							'visibility': 'visible',
							'margin-top': ((max - this_item.height()) / 2)+'px'
						});
					}).first().addClass('active');
				}

				// Initialize the Carousel.
				if (!testimonials_container.hasClass('initialized')) {
					// You will need to initialize the Carousel!
					if (display == 'carousel') {
						testimonials_testimonials.carousel();
					}
					testimonials_container.addClass('initialized');
				}
				
				// Always fix offset once loaded
				var cur_loaded = list_container.find('.testimonial.item').length;
				review_offset.val(cur_loaded); // Set the offset so next time we load the right increment
			};


			// Launch Get Testimonials
			get_testimonials();

			if (star_rating.length) {
				star_rating.tooltip();
			}
		}
		
		$('.testimonial-box').each(function(){
			if (!$(this).hasClass('manual-trigger') && !$(this).closest('.manual-trigger').length)
				create_testimonial_module($(this));
		});
                
		// Attach globally so it can be called from anywhere.
		window.create_testimonial_module = create_testimonial_module;
	});
	//</script>
	<?php 
	exit;
	?>