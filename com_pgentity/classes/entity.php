<?php
/**
 * entity class.
 *
 * @package Pines
 * @subpackage com_pgentity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Database abstraction object.
 *
 * @package Pines
 * @subpackage com_pgentity
 */
class entity implements entity_interface {
	/**
	 * The GUID of the entity.
	 *
	 * @var int
	 * @access private
	 */
	private $guid = null;
	/**
	 * Array of the entity's tags.
	 *
	 * @var array
	 * @access private
	 */
	private $tags = array();
	/**
	 * The array used to store each variable assigned to an entity.
	 *
	 * @var array
	 * @access protected
	 */
	protected $data = array();
	/**
	 * Same as $data, but hasn't been unserialized.
	 *
	 * @var array
	 * @access protected
	 */
	protected $sdata = array();
	/**
	 * The array used to store referenced entities.
	 *
	 * This technique allows your code to see another entity as a variable,
	 * while storing only a reference.
	 *
	 * @var array
	 * @access protected
	 */
	protected $entity_cache = array();
	/**
	 * Whether this instance is a sleeping reference.
	 *
	 * @var bool
	 * @access private
	 */
	private $is_a_sleeping_reference = false;
	/**
	 * The reference to use to wake.
	 *
	 * @var array
	 * @access private
	 */
	private $sleeping_reference = false;
	/**
	 * Whether to use "skip_ac" when accessing entity references.
	 *
	 * @var bool
	 */
	public $_p_use_skip_ac = false;

	public function __construct() {
		$args = func_get_args();
		if (!empty($args))
			call_user_func_array(array($this, 'add_tag'), $args);
	}

	/**
	 * Create a new instance.
	 * @return entity The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$entity = new $class;
		$pines->hook->hook_object($entity, $class.'->', false);
		$args = func_get_args();
		if (!empty($args))
			call_user_func_array(array($entity, 'add_tag'), $args);
		return $entity;
	}

	/**
	 * Create a new sleeping reference instance.
	 *
	 * Sleeping references won't retrieve their data from the database until it
	 * is actually used.
	 *
	 * @param array $reference The Pines Entity Reference to use to wake.
	 * @return entity The new instance.
	 */
	public static function factory_reference($reference) {
		global $pines;
		$class = $reference[2];
		$entity = call_user_func(array($class, 'factory'));
		$entity->reference_sleep($reference);
		return $entity;
	}

	/**
	 * Retrieve a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @return mixed The value of the variable or nothing if it doesn't exist.
	 */
	public function &__get($name) {
		global $pines;
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ($name === 'guid' || $name === 'tags')
			return $this->$name;
		// Unserialize.
		if (isset($this->sdata[$name])) {
			$this->data[$name] = unserialize($this->sdata[$name]);
			unset($this->sdata[$name]);
		}
		// Check for an entity first.
		if (isset($this->entity_cache[$name])) {
			if ($this->data[$name][0] == 'pines_entity_reference') {
				if ($this->entity_cache[$name] === 0) {
					// The entity hasn't been loaded yet, so load it now.
					$class = $this->data[$name][2];
					$this->entity_cache[$name] = $class::factory_reference($this->data[$name]);
					$this->entity_cache[$name]->_p_use_skip_ac = (bool) $this->_p_use_skip_ac;
				}
				return $this->entity_cache[$name];
			} else {
				if (function_exists('pines_error'))
					pines_error("Corrupted entity data found on entity with GUID {$this->guid}.");
				if (function_exists('pines_log'))
					pines_log("Corrupted entity data found on entity with GUID {$this->guid}.", 'fatal');
			}
		}
		// If it's not an entity, return the regular value.
		if ((array) $this->data[$name] === $this->data[$name]) {
			// But, if it's an array, check all the values for entity references, and change them.
			array_walk($this->data[$name], array($this, 'reference_to_entity'));
		} elseif ((object) $this->data[$name] === $this->data[$name] && !(((is_a($this->data[$name], 'entity') || is_a($this->data[$name], 'hook_override'))) && is_callable(array($this->data[$name], 'to_reference')))) {
			// Only do this for non-entity objects.	
			foreach ($this->data[$name] as &$cur_property)
				$this->reference_to_entity($cur_property, null);
			unset($cur_property);
		}
		return $this->data[$name];
	}

	/**
	 * Checks whether a variable is set.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @return bool
	 * @todo Check that a referenced entity has not been deleted.
	 */
	public function __isset($name) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ($name === 'guid' || $name === 'tags')
			return isset($this->$name);
		// Unserialize.
		if (isset($this->sdata[$name])) {
			$this->data[$name] = unserialize($this->sdata[$name]);
			unset($this->sdata[$name]);
		}
		return isset($this->data[$name]);
	}

	/**
	 * Sets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 * @param string $value The value of the variable.
	 * @return mixed The value of the variable.
	 */
	public function __set($name, $value) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ($name === 'guid' || $name === 'tags')
			return ($this->$name = $value);
		// Delete any serialized value.
		if (isset($this->sdata[$name]))
			unset($this->sdata[$name]);
		if ((is_a($value, 'entity') || is_a($value, 'hook_override')) && is_callable(array($value, 'to_reference'))) {
			// Store a reference to the entity (its GUID and the class it was loaded as).
			// We don't want to manipulate $value itself, because it could be a variable that the program is still using.
			$save_value = $value->to_reference();
			// If to_reference returns an array, the GUID of the entity is set
			// or it's a sleeping reference, so this is an entity and we don't
			// store it in the data array.
			if ((array) $save_value === $save_value)
				$this->entity_cache[$name] = $value;
			elseif (isset($this->entity_cache[$name]))
				unset($this->entity_cache[$name]);
			$this->data[$name] = $save_value;
			return $value;
		} else {
			// This is not an entity, so if it was one, delete the cached entity.
			if (isset($this->entity_cache[$name]))
				unset($this->entity_cache[$name]);
			// Store the actual value passed.
			$save_value = $value;
			// If the variable is an array, look through it and change entities to references.
			if ((array) $save_value === $save_value)
				array_walk_recursive($save_value, array($this, 'entity_to_reference'));
			return ($this->data[$name] = $save_value);
		}
	}

	/**
	 * Unsets a variable.
	 *
	 * You do not need to explicitly call this method. It is called by PHP when
	 * you access the variable normally.
	 *
	 * @param string $name The name of the variable.
	 */
	public function __unset($name) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ($name === 'guid') {
			unset($this->$name);
			return;
		}
		if ($name === 'tags') {
			$this->$name = array();
			return;
		}
		if (isset($this->entity_cache[$name]))
			unset($this->entity_cache[$name]);
		unset($this->data[$name]);
		unset($this->sdata[$name]);
	}

	public function add_tag() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		$tag_array = func_get_args();
		if ((array) $tag_array[0] === $tag_array[0])
			$tag_array = $tag_array[0];
		foreach ($tag_array as $tag) {
			$this->tags[] = $tag;
		}
		$this->tags = array_keys(array_flip($this->tags));
	}

	public function array_search($array, $strict = false) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((array) $array !== $array)
			return false;
		foreach ($array as $key => $cur_entity) {
			if ($strict ? $this->equals($cur_entity) : $this->is($cur_entity))
				return $key;
		}
		return false;
	}

	public function clear_cache() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		// Convert entities in arrays.
		foreach ($this->data as &$value) {
			if ((array) $value === $value)
				array_walk_recursive($value, array($this, 'entity_to_reference'));
		}
		unset($value);

		// Handle individual entities.
		foreach ($this->entity_cache as $key => &$value) {
			if (strpos($key, 'reference_guid: ') === 0) {
				// If it's from an array, remove it.
				unset($this->entity_cache[$key]);
			} else {
				// If it's from a property, set it back to 0.
				$value = 0;
			}
		}
		unset($value);
	}

	public function delete() {
		global $pines;
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		return $pines->entity_manager->delete_entity($this);
	}

	/**
	 * Check if an item is an entity, and if it is, convert it to a reference.
	 *
	 * @param mixed &$item The item to check.
	 * @param mixed $key Unused.
	 * @access private
	 */
	private function entity_to_reference(&$item, $key) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((is_a($item, 'entity') || is_a($item, 'hook_override')) && isset($item->guid) && is_callable(array($item, 'to_reference'))) {
			// This is an entity, so we should put it in the entity cache.
			if (!isset($this->entity_cache["reference_guid: {$item->guid}"]))
				$this->entity_cache["reference_guid: {$item->guid}"] = clone $item;
			// Make a reference to the entity (its GUID) and the class the entity was loaded as.
			$item = $item->to_reference();
		}
	}

	public function equals(&$object) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if (!(is_a($object, 'entity') || is_a($object, 'hook_override')))
			return false;
		if (isset($this->guid) || isset($object->guid)) {
			if ($this->guid != $object->guid)
				return false;
		}
		if (get_class($object) != get_class($this))
			return false;
		$ob_data = $object->get_data();
		$my_data = $this->get_data();
		return ($ob_data == $my_data);
	}

	public function get_data() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		// Convert any entities to references.
		return array_map(array($this, 'get_data_reference'), $this->data);
	}

	/**
	 * Convert entities to references and return the result.
	 *
	 * @param mixed $item The item to convert.
	 * @return mixed The resulting item.
	 */
	private function get_data_reference($item) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((is_a($item, 'entity') || is_a($item, 'hook_override')) && is_callable(array($item, 'to_reference'))) {
			// Convert entities to references.
			return $item->to_reference();
		} elseif ((array) $item === $item) {
			// Recurse into lower arrays.
			return array_map(array($this, 'get_data_reference'), $item);
		} elseif ((object) $item === $item) {
			foreach ($item as &$cur_property)
				$cur_property = $this->get_data_reference($cur_property);
			unset($cur_property);
		}
		// Not an entity or array, just return it.
		return $item;
	}

	public function get_sdata() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		return $this->sdata;
	}

	public function has_tag() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((array) $this->tags !== $this->tags)
			return false;
		$tag_array = func_get_args();
		if ((array) $tag_array[0] === $tag_array[0])
			$tag_array = $tag_array[0];
		foreach ($tag_array as $tag) {
			if ( !in_array($tag, $this->tags) )
				return false;
		}
		return true;
	}

	public function in_array($array, $strict = false) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((array) $array !== $array)
			return false;
		foreach ($array as $cur_entity) {
			if ($strict ? $this->equals($cur_entity) : $this->is($cur_entity))
				return true;
		}
		return false;
	}

	public function is(&$object) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if (!(is_a($object, 'entity') || is_a($object, 'hook_override')))
			return false;
		if (isset($this->guid) || isset($object->guid)) {
			return ($this->guid == $object->guid);
		} elseif (!is_callable(array($object, 'get_data'))) {
			return false;
		} else {
			$ob_data = $object->get_data();
			$my_data = $this->get_data();
			return ($ob_data == $my_data);
		}
	}

	public function put_data($data, $sdata = array()) {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((array) $data !== $data)
			$data = array();
		// Erase the entity cache.
		$this->entity_cache = array();
		foreach ($data as $name => $value) {
			if ((array) $value === $value && $value[0] === 'pines_entity_reference') {
				// Don't load the entity yet, but make the entry in the array,
				// so we know it is an entity reference. This will speed up
				// retrieving entities with lots of references, especially
				// recursive references.
				$this->entity_cache[$name] = 0;
			}
		}
		foreach ($sdata as $name => $value) {
			if (strpos($value, 'a:3:{i:0;s:22:"pines_entity_reference";') === 0) {
				// Don't load the entity yet, but make the entry in the array,
				// so we know it is an entity reference. This will speed up
				// retrieving entities with lots of references, especially
				// recursive references.
				$this->entity_cache[$name] = 0;
			}
		}
		$this->data = $data;
		$this->sdata = $sdata;
	}

	/**
	 * Set up a sleeping reference.
	 * @param array $reference The reference to use to wake.
	 */
	public function reference_sleep($reference) {
		$this->is_a_sleeping_reference = true;
		$this->sleeping_reference = $reference;
	}

	/**
	 * Check if an item is a reference, and if it is, convert it to an entity.
	 *
	 * This function will recurse into deeper arrays.
	 *
	 * @param mixed &$item The item to check.
	 * @param mixed $key Unused.
	 * @access private
	 */
	private function reference_to_entity(&$item, $key) {
		global $pines;
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if ((array) $item === $item) {
			if ($item[0] === 'pines_entity_reference') {
				if (!isset($this->entity_cache["reference_guid: {$item[1]}"]))
					$this->entity_cache["reference_guid: {$item[1]}"] = call_user_func(array($item[2], 'factory_reference'), $item);
				$item = $this->entity_cache["reference_guid: {$item[1]}"];
			} else {
				array_walk($item, array($this, 'reference_to_entity'));
			}
		} elseif ((object) $item === $item && !(((is_a($item, 'entity') || is_a($item, 'hook_override'))) && is_callable(array($item, 'to_reference')))) {
			// Only do this for non-entity objects.	
			foreach ($item as &$cur_property)
				$this->reference_to_entity($cur_property, null);
			unset($cur_property);
		}
	}

	/**
	 * Wake from a sleeping reference.
	 * 
	 * @return bool True on success, false on failure.
	 */
	private function reference_wake() {
		global $pines;
		if (!$this->is_a_sleeping_reference)
			return true;
		$entity = $pines->entity_manager->get_entity(array('class' => $this->sleeping_reference[2], 'skip_ac' => (bool) $this->_p_use_skip_ac), array('&', 'guid' => $this->sleeping_reference[1]));
		if (!isset($entity))
			return false;
		$this->is_a_sleeping_reference = false;
		$this->sleeping_reference = null;
		$this->guid = $entity->guid;
		$this->tags = $entity->tags;
		$this->put_data($entity->get_data(), $entity->get_sdata());
		return true;
	}

	public function refresh() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		if (!isset($this->guid))
			return false;
		global $pines;
		$refresh = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('guid' => $this->guid));
		if (!isset($refresh))
			return 0;
		$this->tags = $refresh->tags;
		$this->put_data($refresh->get_data(), $refresh->get_sdata());
		return true;
	}

	public function remove_tag() {
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		$tag_array = func_get_args();
		if ((array) $tag_array[0] === $tag_array[0])
			$tag_array = $tag_array[0];
		foreach ($tag_array as $tag) {
			// Can't use array_search, because $tag may exist more than once.
			foreach ($this->tags as $cur_key => $cur_tag) {
				if ( $cur_tag === $tag )
					unset($this->tags[$cur_key]);
			}
		}
		$this->tags = array_values($this->tags);
	}

	public function save() {
		global $pines;
		if ($this->is_a_sleeping_reference)
			$this->reference_wake();
		return $pines->entity_manager->save_entity($this);
	}

	public function to_reference() {
		if ($this->is_a_sleeping_reference)
			return $this->sleeping_reference;
		if (!isset($this->guid))
			return $this;
		return array('pines_entity_reference', $this->guid, get_class($this));
	}
}

?>
