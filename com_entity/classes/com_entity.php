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
	 * Get an array of entities.
	 *
	 * GUIDs start at one (1) and must be integers.
	 *
	 * $options can contain the following key/values:
	 *
	 * - guid - A GUID or array of GUIDs.
	 * - parent - A GUID or array of GUIDs.
	 * - tags - An array of tags. The entity must have each one.
	 * - tags_i - An array of inclusive tags. The entity must have at least one.
	 * - data - An array of key/values corresponding to var/values.
	 * - data_i - An array of inclusive key/values corresponding to var/values.
	 * - match - An array of key/regex corresponding to var/values.
	 * - match_i - An array of inclusive key/regex corresponding to var/values.
	 * - class - The class to create each entity with.
	 *
	 * For regex matching, preg_match() is used.
	 *
	 * If a class is specified, it must have a factory() static method which
	 * returns a new instance.
	 *
	 * @param array $options The options to search for.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities($options = array()) {
		global $config;
		$entities = array();

		$query_parts = array();
		
		$class = isset($options['class']) ? $options['class'] : entity;
		
		foreach ($options as $key => $option) {
			$cur_query = '';
			switch ($key) {
				case 'guid':
					if (is_array($option)) {
						foreach ($option as $cur_guid) {
							if ( !empty($cur_query) )
								$cur_query .= ' OR ';
							$cur_query .= "e.`guid` = ".intval($cur_guid);
						}
					} else {
						$cur_query = "e.`guid` = ".intval($option);
					}
					break;
				case 'parent':
					if (is_array($option)) {
						foreach ($option as $cur_parent) {
							if ( !empty($cur_query) )
								$cur_query .= ' OR ';
							$cur_query .= "e.`parent` = ".intval($cur_parent);
						}
					} else {
						$cur_query = "e.`parent` = ".intval($option);
					}
					break;
				case 'tags':
					foreach ($option as $cur_tag) {
						if ( !empty($cur_query) )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`tags` LIKE \'%\"'.mysql_real_escape_string($cur_tag, $config->db_manager->link).'\"%\'';
					}
					break;
				case 'tags_i':
					foreach ($option as $cur_tag) {
						if ( !empty($cur_query) )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`tags` LIKE \'%\"'.mysql_real_escape_string($cur_tag, $config->db_manager->link).'\"%\'';
					}
					break;
			}
			if (!empty($cur_query))
				$query_parts[] = $cur_query;
		}

		if (empty($query_parts)) {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid` = d.`guid` ORDER BY e.`guid`;",
				$config->com_mysql->prefix,
				$config->com_mysql->prefix);
		} else {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid` = d.`guid` HAVING (%s) ORDER BY e.`guid`;",
				$config->com_mysql->prefix,
				$config->com_mysql->prefix,
				'('.implode(') AND (', $query_parts).')');
		}
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
			// Recheck all conditions.
			$pass = true;
			foreach ($options as $key => $option) {
				switch ($key) {
					case 'guid':
						if (is_array($option)) {
							$pass = $pass && in_array($entity->guid, $option);
						} else {
							$pass = $pass && ($entity->guid == $option);
						}
						break;
					case 'parent':
						if (is_array($option)) {
							$pass = $pass && in_array($entity->parent, $option);
						} else {
							$pass = $pass && ($entity->parent == $option);
						}
						break;
					case 'tags':
						$pass = $pass && $entity->has_tag($option);
						break;
					case 'tags_i':
						$found = false;
						foreach ($option as $cur_option) {
							if ($entity->has_tag($cur_option)) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'ref':
						// Todo: Check in arrays.
						$found = true;
						foreach ($option as $cur_key => $cur_option)
							$found = $found && (is_array($data[$cur_key]) && $data[$cur_key][0] == 'pines_entity_reference' && $data[$cur_key][1] == $cur_option);
						$pass = $pass && $found;
						break;
					case 'ref_i':
						// Todo: Inclusive references.
						break;
					case 'data':
						$found = true;
						foreach ($option as $cur_key => $cur_option)
							$found = $found && ($entity->$cur_key == $cur_option);
						$pass = $pass && $found;
						break;
					case 'data_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if ($entity->$cur_key == $cur_option) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'match':
						$found = true;
						foreach ($option as $cur_key => $cur_option)
							$found = $found && preg_match($cur_option, $entity->$cur_key);
						$pass = $pass && $found;
						break;
					case 'match_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (preg_match($cur_option, $entity->$cur_key)) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
				}
				if (!$pass)
					break;
			}
			if ($pass)
				array_push($entities, $entity);
		}

		mysql_free_result($result);
		return $entities;
	}

	/**
	 * Get the first entity to match all options.
	 *
	 * $options is the same as in get_entities().
	 *
	 * @param array|int|float|string $options The options to search for, or just a GUID.
	 * @return mixed An entity, or null on failure.
	 */
	public function get_entity($options) {
		if (!is_array($options))
			$options = array('guid' => (int) $options);
		$entities = $this->get_entities($options);
		if (empty($entities))
			return null;
		return $entities[0];
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