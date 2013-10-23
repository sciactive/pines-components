<?php
/**
 * Provides a form for the user to edit or create a testimonial.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Testimonial' : 'Editing Testimonial ['.htmlspecialchars($this->entity->id).'] for '.htmlspecialchars($this->entity->customer->name);
$this->note = 'Provide testimonial details in this form.';
$pines->com_customer->load_customer_select();
$pines->com_ptags->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_testimonials', 'testimonial/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			// Customer Autocomplete.
			$("#p_muid_customer").customerselect();
			var stars = $('#rating-container .star');
			var rating_input = $('[name=rating]');
			var rate_button = $('.rate');
			
			rate_button.click(function(){
				stars.find('.icon-star-empty')
				$(this).remove();
			});
			
			stars.click(function(){
				var cur_star = $(this);
				if (cur_star.find('i').hasClass('icon-star-empty')) {
					stars.find('i').addClass('icon-star').removeClass('icon-star-empty');
				}
				var num = cur_star.nextAll('.star').andSelf().length;
				var rated = cur_star.nextAll('.star').andSelf(); // deprecated changed to addBack();
				rating_input.val(num);
				
				stars.removeClass('rated');
				rated.addClass('rated');
			});
			
			$('#p_muid_tags').ptags();
			
			$('[name=review]').change(function(){
				if ($(this).val() == 'ON' && $(this).is(':checked')) {
					// Tell the user to be SURE to add the classes for a review.
					$('#p_muid_add_tags_warning').fadeIn();
					$('#p_muid_remove_tags_warning').hide();
				} else {
					$('#p_muid_add_tags_warning').hide();
					$('#p_muid_remove_tags_warning').fadeIn();
				}
					
			})
		});
	</script>
	<style type="text/css">
		#rating-container {
			float: right;
			margin-left: 10px;
		}
		#rating-container .star {
			color: <?php echo (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_hr_bottom)) ? $pines->config->com_testimonials->feedback_hr_bottom : '#5cb4f2'; ?>;
			cursor: pointer;
			float: right;
			padding-right: 2px;
			-o-transition: color 125ms linear 0s;
			-ms-transition: color 125ms linear 0s;
			-moz-transition: color 125ms linear 0s;
			-webkit-transition: color 125ms linear 0s;
			/* ...and now override with proper CSS property */
			transition: color 125ms linear 0s;
		}
		#rating-container .remove {
			display: block;
			text-align:center;
			font-size: 10px;
		}
		#rating-container .remove span {
			font-size: 10px;
		}
		#rating-container .star:hover, #rating-container .star:hover ~ .star, #rating-container .star.rated  {
			color: <?php echo (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_hr_top)) ? $pines->config->com_testimonials->feedback_hr_top : '#005c9e'; ?>;
		}
	</style>
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>Customer: <span><?php echo htmlspecialchars("{$this->entity->customer->name} [{$this->entity->customer->username}]"); ?></span></div>
		<div>User: <span><?php echo ($this->entity->user) ? htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]") : 'See Customer.'; ?></span></div>
		<div>Group: <span><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
		<div>Modified: <span><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Customer</h3>
	</div>
	<div class="pf-element">
		<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
		<input id="p_muid_customer" class="pf-field" type="text" name="customer" value="<?php echo (isset($this->entity->customer->guid) ? htmlspecialchars("{$this->entity->customer->guid}: {$this->entity->customer->name}") : ''); ?>"/>
	</div>
	<div class="pf-element pf-heading">
		<h3>Feedback</h3>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Customer Feedback
			<span class="pf-note">Testimonial uses this feedback as default if it's approved.</span>
		</span>
		<textarea class="pf-field" style="width:60%; min-height: 125px;" name="feedback"><?php echo isset($this->entity->feedback) ? htmlspecialchars($this->entity->feedback) : ''; ?></textarea>
	</div>
	<?php if (gatekeeper('com_testimonials/quotetestimonials')) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Quote Feedback for Testimonial 
				<span class="pf-note">If desired, you can quote part of the feedback for the testimonial instead.</span>
			</span>
			<textarea class="pf-field" style="width:60%; min-height: 125px;" name="quotefeedback"><?php echo isset($this->entity->quotefeedback) ? htmlspecialchars($this->entity->quotefeedback) : ''; ?></textarea>
		</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Allow us to Share Feedback</span>
		<input class="pf-field" type="checkbox" name="share" value="ON" <?php echo ($this->entity->share) ? 'checked="checked"' : (isset($this->entity->guid) ? '' : 'checked="checked"') ; ?>/>
	</div>
	<div class="pf-element">
		<span class="pf-label">Share Anonymously</span>
		<input class="pf-field" type="checkbox" name="anon" value="ON" <?php echo ($this->entity->anon) ? 'checked="checked"' : ''; ?>/>
	</div>
	<div class="pf-element">
		<span class="pf-label">Rating</span>
		<div id="rating-container">
			<?php if (!$this->entity->rating) { ?>
				<span class="star"><i class="icon-star-empty"></i></span>
				<span class="star"><i class="icon-star-empty"></i></span>
				<span class="star"><i class="icon-star-empty"></i></span>
				<span class="star"><i class="icon-star-empty"></i></span>
				<span class="star"><i class="icon-star-empty"></i></span>
			<?php } else { 
				for ($c = 5; $c >= 1; $c--) { 
					if ((int) $this->entity->rating >= $c) { ?>
					<span class="star rated"><i class="icon-star"></i></span>
					<?php } else { ?>
					<span class="star"><i class="icon-star"></i></span>
					<?php } 
					}
				} ?>
		</div>
		<input type="hidden" name="rating" value="<?php echo ($this->entity->rating) ? htmlspecialchars($this->entity->rating) : ''; ?>" />
	</div>
	<?php if (gatekeeper('com_testimonials/edittags')) { ?>
	<div class="pf-element">
		<span class="pf-label">Review
			<span class="pf-note">If the testimonial is a review of a certain product/entity as opposed to a review of the general business.</span>
		</span>
		<input class="pf-field" type="checkbox" name="review" value="ON" <?php echo (in_array('review',$this->entity->tags)) ? 'checked="checked"' : ''; ?>/>
	</div>
	<div class="pf-element hide" id="p_muid_add_tags_warning">
		<div class="alert alert-info"><strong>Be sure to add the appropriate tags for the review</strong>:
                    <ul style="margin-top: 10px;">
                        <li>The unique name or alias of the item being reviewed</li>
                        <li>OR, the entity guid</li>
                        <li>AND the entity class name</li>
                    </ul>
                    <p>ie. 34893,com_gallery_picture OR a-very-specific_picture-of_someone OR a random number 3849349384939</p>
                </div>
	</div>
	<div class="pf-element hide" id="p_muid_remove_tags_warning">
		<div class="alert alert-info">
			<strong>Be sure to remove any tags you removed from the testimonial</strong>!
        </div>
	</div>
	<div class="pf-element">
		<span class="pf-label">Tags 
			<span class="pf-note">Certain tags cannot be removed or added: approved, com_testimonials, testimonial etc</span>
		</span>
		<input class="pf-field" id="p_muid_tags" name="tags" type="text" value="<?php echo implode(',', $this->entity->tags);?>" />
	</div>
	<?php } if (gatekeeper('com_testimonials/changestatus')) { ?>
	<div class="pf-element">
		<span class="pf-label">Auto-Approve</span>
		<input class="pf-field" type="checkbox" name="approve" value="ON" <?php echo ($this->entity->has_tag('approved')) ? 'checked="checked"' : (isset($this->entity->guid) ? '' : 'checked="checked"') ; ?>/>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading" style="margin-bottom: 20px;"></div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="type" value="form" />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_testimonials', 'testimonial/list'))); ?>);" value="Cancel" />
	</div>
</form>