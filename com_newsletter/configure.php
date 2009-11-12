<?php
/**
 * com_newsletter's configuration.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 => 
  array (
	'name' => 'default_from',
	'cname' => 'Default From',
	'description' => 'The default "from" email.',
	'value' => 'Nowhere <nowhere@example.com>',
  ),
  1 => 
  array (
	'name' => 'default_reply_to',
	'cname' => 'Default Reply To',
	'description' => 'The "reply-to" email.',
	'value' => 'webmaster@example.com',
  ),
);

?>