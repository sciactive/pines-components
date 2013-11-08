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

header("Content-type: text/css");
//$file = __FILE__;
//include('system/includes/externalcache.php');

$review_background = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->review_background)) ? $pines->config->com_testimonials->review_background : '#eeeeee';
$review_text = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->review_text)) ? $pines->config->com_testimonials->review_text : '#333333';
$average_background = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->average_background)) ? $pines->config->com_testimonials->average_background : '#cccccc';
$author_text = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->author_text)) ? $pines->config->com_testimonials->author_text : '#999999';
$feedback_background = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_background)) ? $pines->config->com_testimonials->feedback_background : '#66b8de';
$feedback_background_opened = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_background_opened)) ? $pines->config->com_testimonials->feedback_background_opened : '#0088cc';
$feedback_color = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_color)) ? $pines->config->com_testimonials->feedback_color : '#fcfcfc';
$feedback_color_opened = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_color_opened)) ? $pines->config->com_testimonials->feedback_color_opened : '#ffffff';
$feedback_hr_top = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_hr_top)) ? $pines->config->com_testimonials->feedback_hr_top : '#005c9e';;
$feedback_hr_bottom = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->feedback_hr_bottom)) ? $pines->config->com_testimonials->feedback_hr_bottom : '#5cb4f2';
$scroll_up_background = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->scroll_up_background)) ? $pines->config->com_testimonials->scroll_up_background : '#dedede';
$scroll_up_text = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->scroll_up_text)) ? $pines->config->com_testimonials->scroll_up_text : '#aaaaaa';
$list_item_border = (preg_match('/^#[a-fA-F0-9]{6}$/', $pines->config->com_testimonials->list_item_border)) ? $pines->config->com_testimonials->list_item_border : '#dddddd';
$misc_css = $pines->config->com_testimonials->misc_css;

?>
/* style tag used only for ide purposes. 
<style type="text/css"> */
	/* TESTIMONIAL FRAME */
	.testimonials-module .frame {
		background: <?php echo $review_background; ?>;
	}
	
	.testimonials-module #feedback_form {
		display: none;
		text-align: left;
	}
	
	.testimonials-module .testimonials {
		padding: 10px;
	}
	
	.testimonials-module .give-feedback {
		padding: 3px 10px;
		background-color: <?php echo $feedback_background; ?>;
		color: <?php echo $feedback_color; ?>;
		cursor: pointer;
		text-transform: uppercase;
		font-size: .8em;
		position: relative; 
	}
	
	.testimonials-module .trigger-feedback {
		text-align:right;
	}
	
	.testimonials-module .give-feedback:hover, .testimonials-module .give-feedback.opened {
		background-color: <?php echo $feedback_background_opened; ?>;
		color: <?php echo $feedback_color_opened; ?>;
	}
	
	.testimonials-module .feedback-hr {
		margin: 4px 0;
		border-color: <?php echo $feedback_hr_top; ?>;
		border-bottom-color: <?php echo $feedback_hr_bottom; ?>;
	}
	
	.testimonials-module .form-submit {
		font-size: 20px;
		text-align:center;
		text-transform: none;
		display: none;
	}
	
	.testimonials-module .feedback-status {
		position: absolute;
		width: 100%;
		margin-left: -10px;
		top: 50%;
		margin-top: -23px;
	}
	
	.testimonials-module .share-again {
		text-align:center;
		background: <?php echo $feedback_hr_top ?>;
		position: absolute;
		left: 0;
		bottom: 0;
		visibility: hidden;
		font-size: 10px;
		text-transform: uppercase;
		width: 100%;
	}
	.testimonials-module .star-container {
		background: <?php echo $feedback_hr_top; ?>;
		padding: 5px;
		border-radius: 3px;
		color: <?php echo $feedback_color_opened; ?>;
		font-size: 14px;
		display: inline-block;
		width: 77px;
		line-height: 20px;
		float: right;
	}
	.testimonials-module .rate-us {
		font-weight:bold;
		margin-right: 10px;
		vertical-align: text-top;
	}
	.testimonials-module .please-rate-us {
		display: inline-block;
		background: <?php echo $feedback_hr_top; ?>;
		padding:10px;
		border-radius: 5px;
		margin-bottom: 7px;
	}
	.testimonials-module .star {
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
	.testimonials-module .star-container .remove {
		display: block;
		text-align:center;
		font-size: 10px;
	}
	.testimonials-module .star-container .remove span {
		font-size: 10px;
	}
	.testimonials-module .star-container .star:hover, .testimonials-module .star-container .star:hover ~ .star, .testimonials-module .star-container .star.rated  {
		color: <?php echo $feedback_hr_bottom; ?>;
	}
	.testimonials-module .please-rate-us .star:hover, .testimonials-module .please-rate-us .star:hover ~ .star, .testimonials-module .please-rate-us .star.rated  {
		color: <?php echo $feedback_hr_bottom; ?>;
	}
	.testimonials-module .submit-button {
		background: <?php echo $feedback_hr_top; ?>;
		padding: 5px 10px;
		border-radius: 3px 3px 3px 3px;
		border: none;
		color: <?php echo $feedback_color_opened; ?>;
		text-transform: uppercase;
		font-size: 12px;
		box-sizing: border-box;
		-moz-box-sizing: border-box;
		-o-transition: all 125ms linear 0s;
		-ms-transition: all 125ms linear 0s;
		-moz-transition: all 125ms linear 0s;
		-webkit-transition: all 125ms linear 0s;
		/* ...and now override with proper CSS property */
		transition: all 125ms linear 0s;
		line-height: 20px;
		font-weight: bold;
	}
	.testimonials-module .submit-button:hover {
		color: <?php echo $feedback_hr_bottom; ?>;
	}
	
	.testimonials-module .share-checkbox label.right-align {
		float: right;
	}
	
	.testimonials-module .average-rating, .testimonials-module .no-average-rating {
		background: <?php echo $average_background; ?>;
		color: <?php echo $author_text; ?>;
		padding: 3px;
	}
	
	.testimonials-module .testimonial-loader {
		color: <?php echo $author_text; ?>;
		text-align:center;
		padding: 20px;
		display: none;
	}
	
	.testimonials-module .testimonial-loader i {
		font-size: 25px;
		margin-bottom: 7px;
	}
	.testimonials-module .enable-js.star-container { 
		float:left;
		width: auto;
		font-size: 10px;
		line-height: 12px
	}
	.testimonials-module .testimonials .rating-container {
		color: <?php echo $feedback_background; ?>;
	}
	
	
	/* LIST */
	.testimonials-module .testimonials.make-list {
		position: relative;
		height: 200px;
		overflow: hidden;
		padding-bottom: 30px;
		padding-top: 20px;
	}
	.testimonials-module .testimonials.make-list.carousel {
		margin-bottom: auto;
		line-height: inherit;
	}
	.testimonials-module .testimonial-list-container {
		overflow: auto;
		height: 100%;
		width: 100%;
		text-align: left;
		color: <?php echo $author_text; ?>;
		padding: 0 20px 0 9px;
		display: none;
		margin-top: 5px;
	}
	.testimonials-module .testimonials.make-list .testimonial.item {
		margin-bottom: 14px;
		padding: 0;
		border-bottom: 1px solid <?php echo $list_item_border; ?>;
	}
	.testimonials-module .testimonials.make-list .testimonial.item blockquote {
		margin: 5px;
		border: none;
		padding: 0;
		color: <?php echo $review_text; ?>;
	}
	.testimonials-module .testimonials.make-list .testimonial.item blockquote small {
		color: <?php echo $author_text; ?>;
	}
	.testimonials-module .list-read-more {
		margin-left: -10px;
		font-size: 12px;
		color: <?php echo $feedback_background_opened; ?>;
		text-shadow: 1px 1px <?php echo $feedback_color_opened; ?>;
		cursor: pointer;
		position: absolute;
		bottom: 5px;
		width: 100%;
		text-align:center;
		display: none;
	}
	.testimonials-module .list-up, .testimonials-module .list-top {
		position:absolute;
		color: <?php echo $scroll_up_text; ?>;
		font-size: 12px;
		top: 0;
		cursor: pointer;
		display: none;
	}
	.testimonials-module .list-up {
		background: <?php echo $scroll_up_background; ?>;
		margin-left: -10px;
		width: 100%;
		text-align: center;
	}
	.testimonials-module .list-top {
		right: 6px;
	}
	.testimonials-module .list-top i {
		transform:rotate(90deg);
		-ms-transform:rotate(90deg); /* IE 9 */
		-webkit-transform:rotate(90deg); /* Safari and Chrome */
		display: inline-block;
		font-size: 9px;
		vertical-align: middle;
	}
	.testimonials-module .list-up:hover, .testimonials-module .list-top:hover {
		color: <?php echo $feedback_background_opened; ?>;
	}
	.testimonials-module .list-read-more:hover {
		color: <?php echo $author_text; ?>;
	}
	.testimonials-module .testimonials.make-list .list-read-more, .testimonials-module .testimonials.make-list .list-up, .testimonials-module .testimonials.make-list .list-top {
		display: block;
	}
	.testimonials-module .item-bottom-border {
		border-top: 1px solid <?php echo $feedback_color_opened; ?>;
		margin-top: 10px;
	}
	.testimonials-module .item .timeago {
		line-height: 10px;
		float:right;
		font-size: 10px;
		color: <?php echo $scroll_up_text; ?>;
		margin-top: 3px;
		border-bottom: none;
	}
	
	.testimonials-module .item .description {
		clear: both;
	}
	
	
	/* CAROUSEL */
	.testimonials-module .carousel-inner {
		padding: 0;
		overflow: hidden;
	}
	.testimonials-module .carousel blockquote {
		padding-left: 0;
	}
	.testimonials-module .testimonials .testimonial.item blockquote {
		color: <?php echo $review_text; ?>;
	}
	
	
	/* LOGIN MENU */
	.testimonials-module  .login-container .pf-label {
		float: none;
		width: auto;
		font-size: 12px;
		font-weight: bold;
	}
	.testimonials-module  .login-container .pf-group {
		margin-left: 0;
	}
	.testimonials-module  .login-container .pf-buttons {
		padding-left: 0;
	}
	.testimonials-module  .login-container script+.pf-group {
		display: none;
	}
	.testimonials-module  .login-container input.pf-field {
		width: 100%;
		max-width: 300px;
	}
	.testimonials-module  .login-container .pf-group {
		padding-right: 0;
	}
	.testimonials-module  .login-container .pf-element .pf-label {
		display:block;
	}
	.testimonials-module  .login-container .pf-element.pf-buttons, .testimonials-module  .login-container [id$=_recovery] {
		text-align: center;
	}
	.testimonials-module  .login-container .pf-element .pf-field {
		margin-left: 0;
	}
	.testimonials-module  .login-container .pf-element {
		float: none;
		padding-right: 0;
		text-align: center;
	}
	.testimonials-module .login-container .signup-button {
		clear:both;
		display: inline-block;
		margin: 10px 0;
		border-radius: 20px;
		font-size: 14px;
		font-weight:bold;
		max-width: 300px;
	}
	.testimonials-module .login-container [type=submit] {
		margin-left: 8px;
		border-radius: 20px 20px 20px 20px;
		width: 100%;
		max-width: 300px;
		padding: 10px 20px;
	}
	.testimonials-module .login-container [type=reset] {
		display: none;
	}
	.testimonials-module .login-container a.pf-field {
		color: <?php echo $feedback_color_opened; ?>;
	}
	
	
	/* SMALL SIZE */
	.testimonials-module.small .trigger-feedback {
		text-align: center;
	}
	.testimonials-module.small .share-checkbox .span6 {
		margin-left: 0;
		width: 100%;
	}
	.testimonials-module.small .share-checkbox label.right-align {
		float: none;
	}
	.testimonials-module.small .share-checkbox input {
		float: right;
	}
	/* SMALL LOGIN MENU */
	.testimonials-module.small  .login-container .pf-element {
		padding-right: 16px;
	}
	<?php echo $misc_css; ?>
	/* </style> */
<?php exit; ?>