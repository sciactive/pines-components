<?php
/**
 * com_replace's configuration defaults.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'search_replace',
		'cname' => 'Search and Replace',
		'description' => 'Perform the search and replace feature.',
		'value' => true,
		'peruser' => true,
	),
);

?>