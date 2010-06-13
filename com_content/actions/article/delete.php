<?php
/**
 * Delete an article.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_content/deletearticle') )
	punt_user('You don\'t have necessary permission.', pines_url('com_content', 'article/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_article) {
	$cur_entity = com_content_article::factory((int) $cur_article);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_article;
}
if (empty($failed_deletes)) {
	pines_notice('Selected article(s) deleted successfully.');
} else {
	pines_error('Could not delete articles with given IDs: '.$failed_deletes);
}

redirect(pines_url('com_content', 'article/list'));

?>