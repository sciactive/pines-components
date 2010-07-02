<?php
/**
 * Save changes to an article.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_content/editarticle') )
		punt_user('You don\'t have necessary permission.', pines_url('com_content', 'article/list'));
	$article = com_content_article::factory((int) $_REQUEST['id']);
	if (!isset($article->guid)) {
		pines_error('Requested article id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_content/newarticle') )
		punt_user('You don\'t have necessary permission.', pines_url('com_content', 'article/list'));
	$article = com_content_article::factory();
}

// General
$article->name = $_REQUEST['name'];
$article->alias = preg_replace('/[^\w\d-.]/', '', $_REQUEST['alias']);
$article->enabled = ($_REQUEST['enabled'] == 'ON');
$article->content_tags = explode(',', $_REQUEST['content_tags']);
$article->intro = $_REQUEST['intro'];
$article->content = $_REQUEST['content'];
foreach ($pines->config->com_content->banned_tags as $cur_tag) {
	$article->name = str_replace("<{$cur_tag}", '', $article->name);
	$article->intro = str_replace("<{$cur_tag}", '', $article->intro);
	$article->content = str_replace("<{$cur_tag}", '', $article->content);
}

if (empty($article->name)) {
	$article->print_form();
	pines_notice('Please specify a name.');
	return;
}
if (empty($article->alias)) {
	$article->print_form();
	pines_notice('Please specify an alias.');
	return;
}
// If others can't access articles, this could fail...
$test = $pines->entity_manager->get_entity(array('class' => com_content_article), array('&', 'data' => array('alias', $article->alias), 'tag' => array('com_content', 'article')));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$article->print_form();
	pines_notice('There is already an article with that alias. Please choose a different alias.');
	return;
}

$article->ac->group = $pines->config->com_content->ac_article_group;
$article->ac->other = $pines->config->com_content->ac_article_other;

if ($article->save()) {
	pines_notice('Saved article ['.$article->name.']');
	// Assign the article to the selected categories.
	// We have to do this here, because new articles won't have a GUID until now.
	$categories = array_map('intval', $_REQUEST['categories']);
	$all_categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'data' => array('enabled', true), 'tag' => array('com_content', 'category')));
	foreach($all_categories as &$cur_cat) {
		if (in_array($cur_cat->guid, $categories) && !$article->in_array($cur_cat->articles)) {
			$cur_cat->articles[] = $article;
			if (!$cur_cat->save())
				pines_error("Couldn't add article to category {$cur_cat->name}. Do you have permission?");
		} elseif (!in_array($cur_cat->guid, $categories) && $article->in_array($cur_cat->articles)) {
			$key = $article->array_search($cur_cat->articles);
			unset($cur_cat->articles[$key]);
			if (!$cur_cat->save())
				pines_error("Couldn't remove article from category {$cur_cat->name}. Do you have permission?");
		}
	}
	unset($cur_cat);
} else {
	pines_error('Error saving article. Do you have permission?');
}

redirect(pines_url('com_content', 'article/list'));

?>