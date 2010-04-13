<?php
/**
 * View a newsletter without the Pines interface.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !isset($_REQUEST['mail_id']) ) {
	pines_error("No mail specified!");
	return false;
}

$mail = $pines->entity_manager->get_entity(array('guid' => $_REQUEST['mail_id'], 'tags' => array('com_newsletter', 'mail')));
if ( is_null($mail) ) {
	pines_error('Invalid mail!');
	return false;
}

$pines->page->override = true;

$pines->page->override_doc('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>'.$mail->subject.'</title>
</head>

<body>
	'.$mail->message.'
</body>
</html>');

?>