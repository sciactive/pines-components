<?php
/**
 * Get the dialog contents.
 *
 * @package Components\entityhelper
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

// TODO: Provide a method to define a context. (So non-entities would still work.)

$entity = $pines->entity_manager->get_entity(
		array('class' => $_REQUEST['context']),
		array('&',
			'guid' => (int) $_REQUEST['id']
		)
	);
if (!$entity->guid) {
	$pines->page->override_doc(json_encode(false));
	return;
}

if (is_callable(array($entity, 'helper'))) {
	$response = $entity->helper();
	if (is_a($response, 'module')) {
		$response->render = 'body';
		$response->entity = $entity;
		$body = $response->render();
		$result = array(
			'title' => empty($response->title) ? $entity->info('name') : $response->title,
			'body' => $body
		);
		$response = $entity->helper();
		$response->render = 'footer';
		$response->entity = $entity;
		$result['footer'] = $response->render();
		$pines->page->override_doc(json_encode($result));
		return;
	} elseif ((array) $response === $response && isset($response['title']) && isset($response['body']) && isset($response['footer'])) {
		$pines->page->override_doc(json_encode($response));
		return;
	}
}

$module = new module('com_entityhelper', 'default_helper');
$module->render = 'body';
$module->entity = $entity;
$result = array(
	'title' => $entity->info('name'),
	'body' => $module->render()
);
$module = new module('com_entityhelper', 'default_helper');
$module->render = 'footer';
$module->entity = $entity;
$result['footer'] = $module->render();
$pines->page->override_doc(json_encode($result));

?>