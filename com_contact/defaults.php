<?php
/**
 * com_contact's configuration defaults.
 *
 * @package Components\contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$get_pages = $pines->entity_manager->get_entities(array('class' => com_content_page), array('&', 'tag' => array('com_content', 'page')));
$pages = array();
foreach ($get_pages as $page) {
	$pages[] = $page->guid." - ".$page->name;
}
return array(
	array(
		'name' => 'contact_email',
		'cname' => 'Contact Address',
		'description' => 'The email address for users to contact',
		'value' => 'root@localhost',
		'peruser' => true,
	),
	array(
		'name' => 'thankyou_message',
		'cname' => 'Thank You Message',
		'description' => 'A message you can write for your users after they successfully submit the form.',
		'value' => 'We appreciate your feedback and thank you for your contribution.',
		'peruser' => true,
	),
	array(
		'name' => 'thankyou_title',
		'cname' => 'Thank You Title',
		'description' => 'A thank you title if you do not use a page',
		'value' => 'Thank You!',
		'peruser' => true,
	),
	array(
		'name' => 'thankyou_page',
		'cname' => 'A thank you page',
		'description' => 'You can specify a specific page to use as the thank you message.',
		'value' => '',
		'options' => $pages,
		'peruser' => true,
	),
);

?>