<?php
/**
 * com_content's modules.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'page' => array(
		'cname' => 'Page Content',
		'view' => 'page/page',
		'form' => 'modules/page_form',
	),
	'category' => array(
		'cname' => 'Category Listing',
		'view' => 'category/category',
		'form' => 'modules/category_form',
	),
);

?>