<?php
/**
 * com_jstree class.
 *
 * @package Pines
 * @subpackage com_jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_jstree main class.
 *
 * A JavaScript tree widget.
 *
 * @package Pines
 * @subpackage com_jstree
 */
class com_jstree extends component {
	/**
	 * Whether the jstree JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the tree.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_jstree', 'jstree', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
	
	/**
	 * Transform an entity array into a JSON-ready structure.
	 *
	 * This also works for data objects like groups.
	 *
	 * @param array $entity_array The array of entities.
	 * @param string $data_prop The property to use as the entity's data.
	 * @param string $state The inital state of the nodes. "closed" or "open".
	 * @return array A structured array.
	 */
	public function entity_json_struct($entity_array, $data_prop = 'name', $state = 'closed') {
		$struct = array();
		if (!is_array($entity_array))
			return $struct;
		foreach ($entity_array as $cur_entity) {
			if (!isset($cur_entity->parent)) {
				$array = array(
					'data' => $cur_entity->$data_prop,
					'attr' => array(
						'id' => "$cur_entity->guid"
					)
				);
				if ($state == 'open')
					$array['state'] = $state;
				$children = $this->entity_json_struct_children($cur_entity->guid, $entity_array, $data_prop, $state);
				if (!empty($children))
					$array['children'] = $children;
				$struct[] = $array;
			}
		}
		return $struct;
	}

	/**
	 * Parse the children of an entity into a JSON-ready structure.
	 *
	 * @param int $guid The GUID of the parent.
	 * @param array $entity_array The array of entities.
	 * @param string $data_prop The property to use as the entity's data.
	 * @param string $state The inital state of the nodes. "closed" or "open".
	 * @return array|null A structured array, or null if entity has no children.
	 * @access private
	 */
	protected function entity_json_struct_children($guid, $entity_array, $data_prop, $state) {
		$struct = array();
		if (!is_array($entity_array))
			return null;
		foreach ($entity_array as $cur_entity) {
			if ($cur_entity->parent == $guid || $cur_entity->parent->guid == $guid) {
				$array = array(
					'data' => $cur_entity->$data_prop,
					'attr' => array(
						'id' => "$cur_entity->guid"
					)
				);
				if ($state == 'open')
					$array['state'] = $state;
				$children = $this->entity_json_struct_children($cur_entity->guid, $entity_array, $data_prop, $state);
				if (!empty($children))
					$array['children'] = $children;
				$struct[] = $array;
			}
		}
		if (empty($struct))
			return null;
		return $struct;
	}
}

?>