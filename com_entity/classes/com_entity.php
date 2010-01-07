<?php
/**
 * com_entity class.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_entity main class.
 *
 * Provides a MySQL based entity manager for Pines.
 *
 * @package Pines
 * @subpackage com_entity
 */
class com_entity extends component {
	/**
	 * Delete an entity from the database.
	 *
	 * @param entity &$entity The entity to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_entity(&$entity) {
		$return = $this->delete_entity_by_id($entity->guid);
		if ( $return )
			$entity->guid = null;
		return $return;
	}
	
	/**
	 * Delete an entity by its GUID.
	 *
	 * @param int $guid The GUID of the entity.
	 * @return bool True on success, false on failure.
	 */
	public function delete_entity_by_id($guid) {
		global $config;
		$query = sprintf("DELETE FROM `%scom_entity_entities` WHERE `guid`=%u;",
			$config->com_mysql->prefix,
			intval($guid));
		if ( !(mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return false;
		}
		$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
			$config->com_mysql->prefix,
			intval($guid));
		if ( !(mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return false;
		}
		return true;
	}
	
	/**
	 * Get an array of entities by their data.
	 *
	 * If $wildcards is set to true, the characters % and _ will be wildcards
	 * for any number of characters and one single character, respectively,
	 * unless escaped with a backslash. A literal backslash character must also
	 * be escaped with a backslash.
	 *
	 * Ex: '%Time_/Goal\_100\%%\\' will match:
	 *
	 * - '1: Time4/Goal_100%	 \'
	 * - 'Times/Goal_100%g9ds76gf%F86&(F%*^59&^F6"/lIH_(8pUGHLIUgygh9*\'
	 * - '\\\\Time\/Goal_100%\\\\\\\\'
	 * - 'Time_/Goal_100%\'
	 *
	 * Note: If a class is specified, it must be a descendent of the entity
	 * class.
	 *
	 * @param array $data An array of name=>value pairs of variables.
	 * @param array $required_tags An array of tags the entities must have.
	 * @param bool $wildcards Whether to compare strings with wildcards.
	 * @param mixed $class The name of the class to use for the entities.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities_by_data($data, $required_tags = array(), $wildcards = false, $class = entity) {
		global $config;
		$entities = array();
		
		if ( !is_array($data) ) {
			if (function_exists('display_error'))
				display_error('Call to get_entities_by_data without data array.');
			return null;
		}
		
		$query = sprintf("SELECT `guid` FROM `%scom_entity_data` WHERE `name`='%s' AND %s",
			$config->com_mysql->prefix,
			mysql_real_escape_string(key($data), $config->db_manager->link),
			$this->pwildcard_to_mysql(current($data), $wildcards));
		
		for (next($data); !is_null(key($data)); next($data)) {
			$query .= sprintf(" UNION SELECT `guid` FROM `%scom_entity_data` WHERE `name`='%s' AND %s",
				$config->com_mysql->prefix,
				mysql_real_escape_string(key($data), $config->db_manager->link),
				$this->pwildcard_to_mysql(current($data), $wildcards));
		}
		
		$query .= ";";
		
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}
		
		while ($row = mysql_fetch_array($result)) {
			$entity = $this->get_entity($row['guid'], array(), $class);
			if ( empty($required_tags) || $entity->has_tag($required_tags) )
				array_push($entities, $entity);
		}
		
		mysql_free_result($result);
		return $entities;
	}
	
	/**
	 * Get an array of entities by their parent.
	 *
	 * Note: If a class is specified, it must be a descendent of the entity
	 * class.
	 *
	 * @param int $parent_guid The GUID of the parent entity.
	 * @param mixed $class The name of the class to use for the entities.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities_by_parent($parent_guid, $class = entity) {
		global $config;
		$entities = array();
		
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING e.`parent`=%u ORDER BY e.`guid`;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			intval($parent_guid));
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}
		
		$row = mysql_fetch_array($result);
		while ($row) {
			$entity = call_user_func(array($class, 'factory'));
			$entity->guid = intval($row['guid']);
			$entity->parent = (is_null($row['parent']) ? NULL : intval($row['parent']));
			$entity->tags = unserialize($row['tags']);
			$data = array();
			if (!is_null($row['dname'])) {
			// This do will keep going and adding the data until the
			// next entity is reached. $row will end on the next entity.
				do {
					$data[$row['dname']] = unserialize($row['dvalue']);
					$row = mysql_fetch_array($result);
				} while (intval($row['guid']) === $entity->guid);
			} else {
			// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
			$entity->put_data($data);
			array_push($entities, $entity);
		}
		
		mysql_free_result($result);
		return $entities;
	}
	
	/**
	 * A shortcut to get_entities_by_tags_exclusive.
	 *
	 * Note: Entity managers must provide this shortcut.
	 *
	 * @return mixed The result of get_entities_by_tags_exclusive().
	 */
	public function get_entities_by_tags() {
		if (is_array(func_get_arg(0))) {
			$arg1 = func_get_arg(0);
			$arg2 = func_get_arg(1);
			return $this->get_entities_by_tags_exclusive($arg1, $arg2);
		} else {
			$args = func_get_args();
			return call_user_func_array(array($this, 'get_entities_by_tags_exclusive'), $args);
		}
	}
	
	/**
	 * Get an array of entities which contain *all* of the specified tags.
	 *
	 * Note: If a class is specified, it must be a descendent of the entity
	 * class.
	 *
	 * @param mixed $tags,... A list or array of tags.
	 * @param mixed $class The name of the class to use for the entities.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities_by_tags_exclusive() {
		global $config;
		if (is_array(func_get_arg(0))) {
			$tag_array = func_get_arg(0);
			if (func_num_args() > 1 && is_subclass_of(func_get_arg(1), 'entity')) {
				$class = func_get_arg(1);
			} else {
				$class = entity;
			}
		} else {
			$tag_array = func_get_args();
			if (class_exists($tag_array[count($tag_array)-1]) && is_subclass_of($tag_array[count($tag_array)-1], 'entity')) {
				$class = $tag_array[count($tag_array)-1];
				unset($tag_array[count($tag_array)-1]);
			} else {
				$class = entity;
			}
		}
		$entities = array();
		
		if (empty($tag_array)) {
			if (function_exists('display_error'))
				display_error('Call to get_entities_by_tags_exclusive without tag argument!');
			return null;
		}
		foreach ($tag_array as $cur_tag) {
			if ( !empty($tag_query) )
				$tag_query .= ' AND ';
			$tag_query .= "e.`tags` LIKE '%\\\"$cur_tag\\\"%'";
		}
		
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING (%s) ORDER BY e.`guid`;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			$tag_query);
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}
		
		$row = mysql_fetch_array($result);
		while ($row) {
		// Just to make sure that the entity really is tagged with
			$return_tag_array = unserialize($row['tags']);
			$match = true;
			foreach ($tag_array as $tag) {
				if ( !in_array($tag, $return_tag_array) ) {
					$match = false;
					break;
				}
			}
			if ( $match === true ) {
				$entity = call_user_func(array($class, 'factory'));
				$entity->guid = intval($row['guid']);
				$entity->parent = (is_null($row['parent']) ? NULL : intval($row['parent']));
				$entity->tags = $return_tag_array;
				$data = array();
				if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
					do {
						$data[$row['dname']] = unserialize($row['dvalue']);
						$row = mysql_fetch_array($result);
					} while (intval($row['guid']) === $entity->guid);
				} else {
				// Make sure that $row is incremented :)
					$row = mysql_fetch_array($result);
				}
				$entity->put_data($data);
				array_push($entities, $entity);
			} else {
			// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
		}
		
		mysql_free_result($result);
		return $entities;
	}
	
	/**
	 * Get an array of entities which contain *any* of the specified tags.
	 *
	 * Note: If a class is specified, it must be a descendent of the entity
	 * class.
	 *
	 * @param mixed $tags,... A list or array of tags.
	 * @param mixed $class The name of the class to use for the entities.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities_by_tags_inclusive() {
		global $config;
		if (is_array(func_get_arg(0))) {
			$tag_array = func_get_arg(0);
			if (func_num_args() > 1 && is_subclass_of(func_get_arg(1), 'entity')) {
				$class = func_get_arg(1);
			} else {
				$class = entity;
			}
		} else {
			$tag_array = func_get_args();
			if (class_exists($tag_array[count($tag_array)-1]) && is_subclass_of($tag_array[count($tag_array)-1], 'entity')) {
				$class = $tag_array[count($tag_array)-1];
				unset($tag_array[count($tag_array)-1]);
			} else {
				$class = entity;
			}
		}
		$entities = array();
		
		if (empty($tag_array)) {
			if (function_exists('display_error'))
				display_error('Call to get_entities_by_tags_inclusive without tag argument!');
			return null;
		}
		foreach ($tag_array as $cur_tag) {
			if ( !empty($tag_query) )
				$tag_query .= ' OR ';
			$tag_query .= "e.`tags` LIKE '%\\\"$cur_tag\\\"%'";
		}
		
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING (%s) ORDER BY e.`guid`;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			$tag_query);
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}
		
		$row = mysql_fetch_array($result);
		while ($row) {
		// Just to make sure that the entity really is tagged with
			$return_tag_array = unserialize($row['tags']);
			$match = false;
			foreach ($tag_array as $tag) {
				if ( in_array($tag, $return_tag_array) ) {
					$match = true;
					break;
				}
			}
			if ( $match === true ) {
				$entity = call_user_func(array($class, 'factory'));
				$entity->guid = intval($row['guid']);
				$entity->parent = (is_null($row['parent']) ? NULL : intval($row['parent']));
				$entity->tags = $return_tag_array;
				$data = array();
				if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
					do {
						$data[$row['dname']] = unserialize($row['dvalue']);
						$row = mysql_fetch_array($result);
					} while (intval($row['guid']) === $entity->guid);
				} else {
				// Make sure that $row is incremented :)
					$row = mysql_fetch_array($result);
				}
				$entity->put_data($data);
				array_push($entities, $entity);
			} else {
			// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
		}
		
		mysql_free_result($result);
		return $entities;
	}
	
	/**
	 * Get an array of entities which contain *all* of the exclusive_tags and
	 * *any* of the inclusive_tags.
	 *
	 * Note: If a class is specified, it must be a descendent of the entity
	 * class.
	 *
	 * @param array $exclusive_tags An array of tags.
	 * @param array $inclusive_tags An array of tags.
	 * @param mixed $class The name of the class to use for the entities.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities_by_tags_mixed($exclusive_tags = array(), $inclusive_tags = array(), $class = entity) {
		global $config;
		$entities = array();
		
		if (!is_array($exclusive_tags) || !is_array($inclusive_tags)) {
			if (function_exists('display_error'))
				display_error('Call to get_entities_by_tags_mixed with invalid arguments!');
			return null;
		}
		
		foreach ($exclusive_tags as $cur_tag) {
			if ( !empty($excl_tag_query) )
				$excl_tag_query .= ' AND ';
			$excl_tag_query .= "e.`tags` LIKE '%\\\"$cur_tag\\\"%'";
		}
		
		foreach ($inclusive_tags as $cur_tag) {
			if ( !empty($incl_tag_query) )
				$incl_tag_query .= ' OR ';
			$incl_tag_query .= "e.`tags` LIKE '%\\\"$cur_tag\\\"%'";
		}
		
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING ((%s) AND (%s)) ORDER BY e.`guid`;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			$excl_tag_query,
			$incl_tag_query);
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}
		
		$row = mysql_fetch_array($result);
		while ($row) {
			// Just to make sure that the entity really is tagged with
			$return_tag_array = unserialize($row['tags']);
			$match = false;
			foreach ($inclusive_tags as $tag) {
				if ( in_array($tag, $return_tag_array) ) {
					$match = true;
					break;
				}
			}
			foreach ($exclusive_tags as $tag) {
				if ( !in_array($tag, $return_tag_array) ) {
					$match = false;
					break;
				}
			}
			if ( $match === true ) {
				$entity = call_user_func(array($class, 'factory'));
				$entity->guid = intval($row['guid']);
				$entity->parent = (is_null($row['parent']) ? NULL : intval($row['parent']));
				$entity->tags = $return_tag_array;
				$data = array();
				if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
					do {
						$data[$row['dname']] = unserialize($row['dvalue']);
						$row = mysql_fetch_array($result);
					} while (intval($row['guid']) === $entity->guid);
				} else {
				// Make sure that $row is incremented :)
					$row = mysql_fetch_array($result);
				}
				$entity->put_data($data);
				array_push($entities, $entity);
			} else {
			// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
		}
		
		mysql_free_result($result);
		return $entities;
	}
	
	/**
	 * Get an entity by GUID.
	 *
	 * Note: If a class is specified, it must be a descendent of the entity
	 * class.
	 *
	 * @param int $guid The GUID.
	 * @param array $exclusive_tags An array of tags, which the entity must have.
	 * @param mixed $class The name of the class to use for the entities.
	 * @return mixed The entity, or null on failure.
	 */
	public function get_entity($guid, $exclusive_tags = array(), $class = entity) {
		global $config;
		
		$methods = get_class_methods($class);
		$entity = call_user_func(array($class, 'factory'));
		$data = array();
		
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING e.`guid`=%u;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			intval($guid));
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}
		
		if ( !($row = mysql_fetch_array($result)) )
			return null;
		$entity->guid = intval($guid);
		$entity->parent = (is_null($row['parent']) ? NULL : intval($row['parent']));
		$entity->tags = unserialize($row['tags']);
		
		if ( !is_null($row['dname']) ) {
			do {
				$data[$row['dname']] = unserialize($row['dvalue']);
			} while ($row = mysql_fetch_array($result));
		}
		
		$entity->put_data($data);
		
		mysql_free_result($result);
		
		if (empty($exclusive_tags) || $entity->has_tag($exclusive_tags)) {
			return $entity;
		} else {
			return null;
		}
	}
	
	/**
	 * Convert pines style wildcards to a MySQL usable query.
	 *
	 * If $wildcards is false, or if $value is not a string, a query with an
	 * = comparison is returned, else a query using LIKE is returned.
	 *
	 * @access private
	 * @param mixed $value The data value to use.
	 * @param bool $wilcards Whether wildcards were actually requested.
	 * @return string The generated query part.
	 */
	private function pwildcard_to_mysql($value, $wilcards) {
		global $config;
		if (!$wilcards || !is_string($value))
			return sprintf("`value`='%s'", mysql_real_escape_string(serialize($value), $config->db_manager->link));
		$return = mysql_real_escape_string($value, $config->db_manager->link);
		// Because that function escapes things that may already be escaped, we need to fix them:
		// Replace \\% with \%
		$return = str_replace('\\\\%', '\\%', $return);
		// Replace \\_ with \_
		$return = str_replace('\\\\_', '\\_', $return);
		// Replace \\ with \\\\. (http://dev.mysql.com/doc/refman/5.0/en/string-comparison-functions.html)
		$return = str_replace('\\\\', '\\\\\\\\', $return);
		return "`value` LIKE BINARY 's:%:\"".$return."\";' ESCAPE '\\\\'";
	}
	
	/**
	 * Save an entity to the database.
	 *
	 * If the entity has never been saved (has no GUID), a variable "p_cdate"
	 * is set on it with the current Unix timestamp.
	 *
	 * The variable "p_mdate" is set to the current Unix timestamp.
	 *
	 * @todo Use one big insert query.
	 * @param mixed &$entity The entity.
	 * @return bool True on success, false on failure.
	 */
	public function save_entity(&$entity) {
		global $config;
		if ( is_null($entity->guid) ) {
			$entity->p_cdate = time();
			$entity->p_mdate = time();
			$query = sprintf("INSERT INTO `%scom_entity_entities` (`parent`, `tags`) VALUES (%s, '%s');",
				$config->com_mysql->prefix,
				(is_null($entity->parent) ? 'NULL' : intval($entity->parent)),
				mysql_real_escape_string(serialize($entity->tags), $config->db_manager->link));
			if ( !(mysql_query($query, $config->db_manager->link)) ) {
				if (function_exists('display_error'))
					display_error('Query failed: ' . mysql_error());
				return false;
			}
			$new_id = mysql_insert_id();
			$entity->guid = intval($new_id);
			$data = $entity->get_data();
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$config->com_mysql->prefix,
					intval($new_id),
					mysql_real_escape_string($name, $config->db_manager->link),
					mysql_real_escape_string(serialize($value), $config->db_manager->link));
				if ( !(mysql_query($query, $config->db_manager->link)) ) {
					if (function_exists('display_error'))
						display_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		} else {
			$entity->p_mdate = time();
			$query = sprintf("UPDATE `%scom_entity_entities` SET `parent`=%s, `tags`='%s' WHERE `guid`=%u;",
				$config->com_mysql->prefix,
				(is_null($entity->parent) ? 'NULL' : intval($entity->parent)),
				mysql_real_escape_string(serialize($entity->tags), $config->db_manager->link),
				intval($entity->guid));
			if ( !(mysql_query($query, $config->db_manager->link)) ) {
				if (function_exists('display_error'))
					display_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
				$config->com_mysql->prefix,
				intval($entity->guid));
			if ( !(mysql_query($query, $config->db_manager->link)) ) {
				if (function_exists('display_error'))
					display_error('Query failed: ' . mysql_error());
				return false;
			}
			$data = $entity->get_data();
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$config->com_mysql->prefix,
					intval($entity->guid),
					mysql_real_escape_string($name, $config->db_manager->link),
					mysql_real_escape_string(serialize($value), $config->db_manager->link));
				if ( !(mysql_query($query, $config->db_manager->link)) ) {
					if (function_exists('display_error'))
						display_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		}
	}
}

?>