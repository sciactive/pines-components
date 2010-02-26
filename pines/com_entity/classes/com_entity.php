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
		global $pines;
		$query = sprintf("DELETE e, d FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` WHERE e.`guid`=%u;",
			$pines->config->com_mysql->prefix,
			$pines->config->com_mysql->prefix,
			(int) $guid);
		if ( !(mysql_query($query, $pines->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return false;
		}
		return true;
	}

	/**
	 * Search through a value for an entity reference.
	 *
	 * @param mixed $value Any value to search.
	 * @param array|entity|int $entity An entity, GUID, or array of either to search for.
	 * @return bool True if the reference is found, false otherwise.
	 */
	private function entity_reference_search($value, $entity) {
		if (!is_array($value) || is_null($entity))
			return false;
		// Get the GUID, if the passed $entity is an object.
		if (is_array($entity)) {
			foreach($entity as &$cur_entity) {
				if (is_object($cur_entity))
					$cur_entity = $cur_entity->guid;
			}
			unset($cur_entity);
		} elseif (is_object($entity)) {
			$entity = array($entity->guid);
		} else {
			$entity = array((int) $entity);
		}
		if ($value[0] == 'pines_entity_reference') {
			return in_array($value[1], $entity);
		} else {
			// Search through multidimensional arrays looking for the reference.
			foreach ($value as $cur_value) {
				if ($this->entity_reference_search($cur_value, $entity))
					return true;
			}
		}
		return false;
	}

	/**
	 * Get an array of entities.
	 *
	 * GUIDs are integers and start at one (1).
	 *
	 * $options can contain the following key - values:
	 *
	 * - guid - A GUID or array of GUIDs.
	 * - tags - An array of tags. The entity must have each one.
	 * - tags_i - An array of inclusive tags. The entity must have at least one.
	 * - data - An array of key/values corresponding to var/values.
	 * - data_i - An array of inclusive key/values corresponding to var/values.
	 * - match - An array of key/regex corresponding to var/values.
	 * - match_i - An array of inclusive key/regex corresponding to var/values.
	 * - gt - An array of key/numbers corresponding to var/values.
	 * - gt_i - An array of inclusive key/numbers corresponding to var/values.
	 * - gte - An array of key/numbers corresponding to var/values.
	 * - gte_i - An array of inclusive key/numbers corresponding to var/values.
	 * - lt - An array of key/numbers corresponding to var/values.
	 * - lt_i - An array of inclusive key/numbers corresponding to var/values.
	 * - lte - An array of key/numbers corresponding to var/values.
	 * - lte_i - An array of inclusive key/numbers corresponding to var/values.
	 * - ref - An array of key/values corresponding to var/values.
	 * - ref_i - An array of inclusive key/values corresponding to var/values.
	 * - class - The class to create each entity with.
	 * - limit - The limit of entities to be returned.
	 * - offset - The offset from the first (0) to start retrieving entities.
	 *
	 * For regex matching, preg_match() is used.
	 *
	 * The gt/gte/lt/lte options check whether a variable is greater than/
	 * greater or equal to/less than/less than or equal to a number,
	 * respectively.
	 *
	 * For reference searching, the values can be an entity, GUID, or an array
	 * of either. Inclusive ref can't be a single value, as that wouldn't make
	 * sense.
	 *
	 * If a class is specified, it must have a factory() static method which
	 * returns a new instance.
	 *
	 * @param array $options The options to search for.
	 * @return array|null An array of entities, or null on failure.
	 */
	public function get_entities($options = array()) {
		global $pines;
		$entities = array();
		$query_parts = array();
		$class = isset($options['class']) ? $options['class'] : entity;
		$count = $limit = $ocount = $offset = 0;

		foreach ($options as $key => $option) {
			$cur_query = '';
			// Any options having to do with data only return if the entity has
			// the specified variables.
			switch ($key) {
				case 'guid':
					if (is_array($option)) {
						foreach ($option as $cur_guid) {
							if ( $cur_query )
								$cur_query .= ' OR ';
							$cur_query .= "e.`guid`=".(int) $cur_guid;
						}
					} else {
						$cur_query = "e.`guid`=".(int) $option;
					}
					break;
				case 'tags':
					foreach ($option as $cur_tag) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'LOCATE(\'\"'.mysql_real_escape_string($cur_tag, $pines->db_manager->link).'\"\', e.`tags`)';
					}
					break;
				case 'tags_i':
					foreach ($option as $cur_tag) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'LOCATE(\'\"'.mysql_real_escape_string($cur_tag, $pines->db_manager->link).'\"\', e.`tags`)';
					}
					break;
				case 'data':
				case 'match':
				case 'gt':
				case 'gte':
				case 'lt':
				case 'lte':
				case 'ref':
					foreach ($option as $cur_name => $cur_value) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'EXISTS (SELECT * FROM `'.$pines->config->com_mysql->prefix.'com_entity_data` d WHERE e.`guid`=d.`guid` AND d.`name`=\''.mysql_real_escape_string($cur_name, $pines->db_manager->link).'\')';
					}
					break;
				case 'data_i':
				case 'match_i':
				case 'gt_i':
				case 'gte_i':
				case 'lt_i':
				case 'lte_i':
				case 'ref_i':
					foreach ($option as $cur_name => $cur_value) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'EXISTS (SELECT * FROM `'.$pines->config->com_mysql->prefix.'com_entity_data` d WHERE e.`guid`=d.`guid` AND d.`name`=\''.mysql_real_escape_string($cur_name, $pines->db_manager->link).'\')';
					}
					break;
				case 'limit':
					$limit = $option;
					break;
				case 'offset':
					$offset = $option;
					break;
			}
			if ( $cur_query )
				$query_parts[] = $cur_query;
		}

		if ($query_parts) {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING %s ORDER BY e.`guid`;",
				$pines->config->com_mysql->prefix,
				$pines->config->com_mysql->prefix,
				'('.implode(') AND (', $query_parts).')');
		} else {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` ORDER BY e.`guid`;",
				$pines->config->com_mysql->prefix,
				$pines->config->com_mysql->prefix);
		}
		if ( !($result = mysql_query($query, $pines->db_manager->link)) ) {
			if (function_exists('display_error'))
				display_error('Query failed: ' . mysql_error());
			return null;
		}

		$row = mysql_fetch_array($result);
		while ($row) {
			$guid = (int) $row['guid'];
			$tags = unserialize($row['tags']);
			$data = array();
			if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
				do {
					$data[$row['dname']] = unserialize($row['dvalue']);
					$row = mysql_fetch_array($result);
				} while (intval($row['guid']) === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
			if ($ocount < $offset) {
				$ocount++;
				continue;
			}
			// Recheck all conditions.
			$pass = true;
			foreach ($options as $key => $option) {
				switch ($key) {
					case 'guid':
						if (is_array($option)) {
							$pass = $pass && in_array($guid, $option);
						} else {
							$pass = $pass && ($guid == $option);
						}
						break;
					case 'tags':
						if (is_array($option)) {
							foreach($option as $cur_option) {
								if (!($pass = $pass && in_array($cur_option, $tags)))
									break 2;
							}
						} else {
							$pass = $pass && in_array($option, $tags);
						}
						break;
					case 'tags_i':
						$found = false;
						foreach ($option as $cur_option) {
							if (in_array($cur_option, $tags)) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'data':
						foreach ($option as $cur_key => $cur_option) {
							if ($data[$cur_key] != $cur_option) {
								$pass = false;
								break;
							}
						}
						break;
					case 'data_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && $data[$cur_key] == $cur_option) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'match':
						foreach ($option as $cur_key => $cur_option) {
							if (!preg_match($cur_option, $data[$cur_key])) {
								$pass = false;
								break;
							}
						}
						break;
					case 'match_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && preg_match($cur_option, $data[$cur_key])) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'gt':
						foreach ($option as $cur_key => $cur_option) {
							if ($data[$cur_key] <= $cur_option) {
								$pass = false;
								break;
							}
						}
						break;
					case 'gt_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && $data[$cur_key] > $cur_option) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'gte':
						foreach ($option as $cur_key => $cur_option) {
							if ($data[$cur_key] < $cur_option) {
								$pass = false;
								break;
							}
						}
						break;
					case 'gte_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && $data[$cur_key] >= $cur_option) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'lt':
						foreach ($option as $cur_key => $cur_option) {
							if ($data[$cur_key] >= $cur_option) {
								$pass = false;
								break;
							}
						}
						break;
					case 'lt_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && $data[$cur_key] < $cur_option) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'lte':
						foreach ($option as $cur_key => $cur_option) {
							if ($data[$cur_key] > $cur_option) {
								$pass = false;
								break;
							}
						}
						break;
					case 'lte_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && $data[$cur_key] <= $cur_option) {
								$found = true;
								break;
							}
						}
						$pass = $pass && $found;
						break;
					case 'ref':
						foreach ($option as $cur_key => $cur_option) {
							// If it's an array of values, make sure that each value is met.
							if (is_array($cur_option)) {
								foreach ($cur_option as $cur_cur_option) {
									if (!$this->entity_reference_search($data[$cur_key], $cur_cur_option)) {
										$pass = false;
										break 2;
									}
								}
							} else {
								if (!$this->entity_reference_search($data[$cur_key], $cur_option)) {
									$pass = false;
									break;
								}
							}
						}
						break;
					case 'ref_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && $this->entity_reference_search($data[$cur_key], $cur_option)) {
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
			if ($pass) {
				$entity = call_user_func(array($class, 'factory'));
				$entity->guid = $guid;
				$entity->tags = $tags;
				$entity->put_data($data);
				array_push($entities, $entity);
				$count++;
				if ($limit && $count >= $limit)
					break;
			}
		}

		mysql_free_result($result);
		return $entities;
	}

	/**
	 * Get the first entity to match all options.
	 *
	 * $options is the same as in get_entities().
	 *
	 * This function is equivalent to setting $options['limit'] to 1 for
	 * get_entities(), except that it will return an entity or null, instead of
	 * an array.
	 *
	 * @param mixed $options The options to search for, or just a GUID.
	 * @return mixed An entity, or null on failure and nothing found.
	 */
	public function get_entity($options) {
		if (!is_array($options))
			$options = array('guid' => (int) $options);
		$options['limit'] = 1;
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
		global $pines;
		if ( is_null($entity->guid) ) {
			// Save the created date.
			$entity->p_cdate = microtime(true);
			// And modified date.
			$entity->p_mdate = microtime(true);
			$query = sprintf("INSERT INTO `%scom_entity_entities` (`tags`) VALUES ('%s');",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(serialize($entity->tags), $pines->db_manager->link));
			if ( !(mysql_query($query, $pines->db_manager->link)) ) {
				if (function_exists('display_error'))
					display_error('Query failed: ' . mysql_error());
				return false;
			}
			$new_id = mysql_insert_id();
			$entity->guid = (int) $new_id;
			$data = $entity->get_data();
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$pines->config->com_mysql->prefix,
					(int) $new_id,
					mysql_real_escape_string($name, $pines->db_manager->link),
					mysql_real_escape_string(serialize($value), $pines->db_manager->link));
				if ( !(mysql_query($query, $pines->db_manager->link)) ) {
					if (function_exists('display_error'))
						display_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		} else {
			// Save the modified date.
			$entity->p_mdate = microtime(true);
			$query = sprintf("UPDATE `%scom_entity_entities` SET `tags`='%s' WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(serialize($entity->tags), $pines->db_manager->link),
				intval($entity->guid));
			if ( !(mysql_query($query, $pines->db_manager->link)) ) {
				if (function_exists('display_error'))
					display_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				intval($entity->guid));
			if ( !(mysql_query($query, $pines->db_manager->link)) ) {
				if (function_exists('display_error'))
					display_error('Query failed: ' . mysql_error());
				return false;
			}
			$data = $entity->get_data();
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$pines->config->com_mysql->prefix,
					intval($entity->guid),
					mysql_real_escape_string($name, $pines->db_manager->link),
					mysql_real_escape_string(serialize($value), $pines->db_manager->link));
				if ( !(mysql_query($query, $pines->db_manager->link)) ) {
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