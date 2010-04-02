<?php
/**
 * com_entity class.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
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
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return false;
		}
		return true;
	}

	/**
	 * Delete a unique ID.
	 *
	 * @param string $name The UID's name.
	 * @return bool True on success, false on failure.
	 */
	public function delete_uid($name) {
		if (!$name)
			return false;
		global $pines;
		$query = sprintf("DELETE FROM `%scom_entity_uids` WHERE `name`='%s';",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
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
	 * Export entities to the client as a downloadable file.
	 * 
	 * An entity manager doesn't necessarily need to provide export and import
	 * functions, however if it does, this is the file format:
	 * 
	 * <pre>
	 * # Comments begin with #
	 *    # And can have white space before them.
	 * # This defines a UID.
	 * &lt;name/of/uid&gt;[5]
	 * &lt;another uid&gt;[8000]
	 * # For UIDs, the name is in angle brackets (&lt;&gt;) and the value follows in
	 * #  square brackets ([]).
	 * # This starts a new entity.
	 * {1}[tag,list,with,commas]
	 * # For entities, the GUID is in curly brackets ({}) and the comma
	 * #  separated tag list follows in square brackets ([]).
	 * # Variables are stored like this:
	 * # varname=json_encode(serialize(value))
	 *     abilities="a:1:{i:0;s:10:\"system\/all\";}"
	 *     groups="a:0:{}"
	 *     inherit_abilities="b:0;"
	 *     name="s:5:\"admin\";"
	 * # White space before/after "=" and at beginning/end of line is ignored.
	 *         username  =     "s:5:\"admin\";"
	 * {2}[tag,list]
	 *     another="s:23:\"This is another entity.\";"
	 *     newline="s:1:\"\n\";"
	 * </pre>
	 *
	 * @return bool True on success, false on failure.
	 * @todo Replace the version number.
	 */
	public function export() {
		global $pines;
		$pines->page->override = true;
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=entities.pex;');
		// End all output buffering.
		while (@ob_end_clean());
		echo "# Pines Entity Export\n";
		echo "# com_entity version 1.0 alpha\n";
		echo "# sciactive.com\n";
		echo "#\n";
		echo "# Generation Time: ".date('r')."\n";
		echo "# Pines Version: {$pines->info->version}\n\n";

		echo "#\n";
		echo "# UIDs\n";
		echo "#\n\n";

		// Export UIDs.
		$query = sprintf("SELECT * FROM `%scom_entity_uids`;",
			$pines->config->com_mysql->prefix,
			$pines->config->com_mysql->prefix);
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return false;
		}
		$row = mysql_fetch_array($result);
		while ($row) {
			$row['name'];
			$row['cur_uid'];
			echo "<{$row['name']}>[{$row['cur_uid']}]\n";
			// Make sure that $row is incremented :)
			$row = mysql_fetch_array($result);
		}

		echo "#\n";
		echo "# Entities\n";
		echo "#\n\n";

		// Export entities.
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` ORDER BY e.`guid`;",
			$pines->config->com_mysql->prefix,
			$pines->config->com_mysql->prefix);
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return false;
		}
		$row = mysql_fetch_array($result);
		while ($row) {
			$guid = (int) $row['guid'];
			$tags = explode(',', substr($row['tags'], 1, -1));
			echo "{{$guid}}[".implode(',', $tags)."]\n";
			if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
				do {
					echo "\t{$row['dname']}=".json_encode($row['dvalue'])."\n";
					$row = mysql_fetch_array($result);
				} while ((int) $row['guid'] === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
		}
		return true;
	}

	/**
	 * Export entities to a local file.
	 *
	 * @param string $filename The file to export to.
	 * @return bool True on success, false on failure.
	 */
	public function export_file($filename) {
		global $pines;
		$filename = clean_filename((string) $filename);
		if (!$fhandle = fopen($filename, 'w'))
			return false;
		fwrite($fhandle, "# Pines Entity Export\n# Generated by com_entity on ".date('r')."\n\n");
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` ORDER BY e.`guid`;",
			$pines->config->com_mysql->prefix,
			$pines->config->com_mysql->prefix);
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return false;
		}

		$row = mysql_fetch_array($result);
		while ($row) {
			$guid = (int) $row['guid'];
			$tags = explode(',', substr($row['tags'], 1, -1));
			fwrite($fhandle, "{{$guid}}[".implode(',', $tags)."]\n");
			if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
				do {
					fwrite($fhandle, "\t{$row['dname']}=".json_encode($row['dvalue'])."\n");
					$row = mysql_fetch_array($result);
				} while ((int) $row['guid'] === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
		}
		return true;
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
						$cur_query .= 'LOCATE(\','.mysql_real_escape_string($cur_tag, $pines->com_mysql->link).',\', e.`tags`)';
					}
					break;
				case 'tags_i':
					foreach ($option as $cur_tag) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'LOCATE(\','.mysql_real_escape_string($cur_tag, $pines->com_mysql->link).',\', e.`tags`)';
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
						$cur_query .= 'LOCATE(\','.mysql_real_escape_string($cur_name, $pines->com_mysql->link).',\', e.`varlist`)';
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
						$cur_query .= 'LOCATE(\','.mysql_real_escape_string($cur_name, $pines->com_mysql->link).',\', e.`varlist`)';
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
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}

		$row = mysql_fetch_array($result);
		while ($row) {
			$guid = (int) $row['guid'];
			// Don't bother getting the tags unless we're at/past the offset.
			if ($ocount >= $offset)
				$tags = explode(',', substr($row['tags'], 1, -1));
			$data = array();
			if (!is_null($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
				do {
					// Only remember this entity's data if we're at/past the offset.
					if ($ocount >= $offset)
						$data[$row['dname']] = unserialize($row['dvalue']);
					$row = mysql_fetch_array($result);
				} while ((int) $row['guid'] === $guid);
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
								break 2;
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
								break 2;
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
								break 2;
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
								break 2;
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
								break 2;
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
								break 2;
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
	 * Get the current value of a unique ID.
	 *
	 * @param string $name The UID's name.
	 * @return int|null The UID's value, or null on failure and if it doesn't exist.
	 */
	public function get_uid($name) {
		if (!$name)
			return null;
		global $pines;
		$query = sprintf("SELECT `cur_uid` FROM `%scom_entity_uids` WHERE `name`='%s';",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$row = mysql_fetch_row($result);
		mysql_free_result($result);
		return isset($row[0]) ? (int) $row[0] : null;
	}

	/**
	 * Import entities from a file.
	 *
	 * @param string $filename The file to import from.
	 * @return bool True on success, false on failure.
	 */
	public function import($filename) {
		global $pines;
		$filename = clean_filename((string) $filename);
		if (!$fhandle = fopen($filename, 'r'))
			return false;
		$line = '';
		$data = array();
		while (!feof($fhandle)) {
			$line .= fgets($fhandle, 8192);
			if (substr($line, -1) != "\n")
				continue;
			if (preg_match('/^\s*#/', $line)) {
				$line = '';
				continue;
			}
			$matches = array();
			if (preg_match('/^\s*{(\d+)}\[([\w,]+)\]\s*$/', $line, $matches)) {
				// Save the current entity.
				if ($guid) {
					$query = sprintf("REPLACE INTO `%scom_entity_entities` (`guid`, `tags`, `varlist`) VALUES (%u, '%s', '%s');",
						$pines->config->com_mysql->prefix,
						$guid,
						mysql_real_escape_string(','.$tags.',', $pines->com_mysql->link),
						mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link));
					if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
						if (function_exists('pines_error'))
							pines_error('Query failed: ' . mysql_error());
						return false;
					}
					$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
						$pines->config->com_mysql->prefix,
						$guid);
					if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
						if (function_exists('pines_error'))
							pines_error('Query failed: ' . mysql_error());
						return false;
					}
					if ($data) {
						$query = "INSERT INTO `{$pines->config->com_mysql->prefix}com_entity_data` (`guid`, `name`, `value`) VALUES ";
						foreach ($data as $name => $value) {
							$query .= sprintf("(%u, '%s', '%s'),",
								$guid,
								mysql_real_escape_string($name, $pines->com_mysql->link),
								mysql_real_escape_string($value, $pines->com_mysql->link));
						}
						$query = substr($query, 0, -1).';';
						if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
							if (function_exists('pines_error'))
								pines_error('Query failed: ' . mysql_error());
							return false;
						}
					}
					$guid = null;
					$tags = array();
					$data = array();
				}
				// Record the new entity's info.
				$guid = (int) $matches[1];
				$tags = $matches[2];
			} elseif (preg_match('/^\s*([\w,]+)\s*=\s*(\S.*\S)\s*$/', $line, $matches)) {
				// Add the variable to the new entity.
				if ($guid)
					$data[$matches[1]] = json_decode($matches[2]);
			} elseif (preg_match('/^\s*<([^>]+)>\[(\d+)\]\s*$/', $line, $matches)) {
				// Add the UID.
				$query = sprintf("INSERT INTO `%scom_entity_uids` (`name`, `cur_uid`) VALUES ('%s', %u) ON DUPLICATE KEY UPDATE `cur_uid`=%u;",
					$pines->config->com_mysql->prefix,
					mysql_real_escape_string($matches[1], $pines->com_mysql->link),
					(int) $matches[2],
					(int) $matches[2]);
				if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			$line = '';
		}
		// Save the last entity.
		if ($guid) {
			$query = sprintf("REPLACE INTO `%scom_entity_entities` (`guid`, `tags`, `varlist`) VALUES (%u, '%s', '%s');",
				$pines->config->com_mysql->prefix,
				$guid,
				mysql_real_escape_string(','.$tags.',', $pines->com_mysql->link),
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				$guid);
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			if ($data) {
				$query = "INSERT INTO `{$pines->config->com_mysql->prefix}com_entity_data` (`guid`, `name`, `value`) VALUES ";
				foreach ($data as $name => $value) {
					$query .= sprintf("(%u, '%s', '%s'),",
						$guid,
						mysql_real_escape_string($name, $pines->com_mysql->link),
						mysql_real_escape_string($value, $pines->com_mysql->link));
				}
				$query = substr($query, 0, -1).';';
				if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . mysql_error());
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Increment or create a unique ID and return the new value.
	 *
	 * Unique IDs, or UIDs, are ID numbers, similar to GUIDs, but without any
	 * constraints on how they are used. UIDs can be named anything, however a
	 * good naming convention, in order to avoid conflicts, is to use your
	 * component's name, a slash, then a descriptive name of the objects being
	 * identified. E.g. "com_example/widget" or "com_hrm/employee".
	 *
	 * A UID can be used to identify an object when the GUID doesn't suffice. On
	 * a system where a new entity is created many times per second, referring
	 * to something by its GUID may be unintuitive. However, the component
	 * designer is responsible for assigning UIDs to the component's entities.
	 * Beware that if a UID is incremented for an entity, and the entity cannot
	 * be saved, there is no safe, and therefore, no recommended way to
	 * decrement the UID back to its previous value.
	 *
	 * If new_uid() is passed the name of a UID which does not exist yet, one
	 * will be created with that name, and assigned the value 1. If the UID
	 * already exists, its value will be incremented. The new value will be
	 * returned.
	 *
	 * @param string $name The UID's name.
	 * @return int|null The UID's new value, or null on failure.
	 */
	public function new_uid($name) {
		if (!$name)
			return null;
		global $pines;
		$query = sprintf("SELECT GET_LOCK('%scom_entity_uids_%s', 10);",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$query = sprintf("INSERT INTO `%scom_entity_uids` (`name`, `cur_uid`) VALUES ('%s', 1) ON DUPLICATE KEY UPDATE `cur_uid`=`cur_uid`+1;",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$query = sprintf("SELECT `cur_uid` FROM `%scom_entity_uids` WHERE `name`='%s';",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$row = mysql_fetch_row($result);
		mysql_free_result($result);
		$query = sprintf("SELECT RELEASE_LOCK('%scom_entity_uids_%s');",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		return isset($row[0]) ? (int) $row[0] : null;
	}

	/**
	 * Rename a unique ID.
	 *
	 * @param string $old_name The old name.
	 * @param string $new_name The new name.
	 * @return bool True on success, false on failure.
	 */
	public function rename_uid($old_name, $new_name) {
		if (!$old_name || !$new_name)
			return false;
		global $pines;
		$query = sprintf("UPDATE `%scom_entity_uids` SET `name`='%s' WHERE `name`='%s';",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($new_name, $pines->com_mysql->link),
			mysql_real_escape_string($old_name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return false;
		}
		return true;
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
			$data = $entity->get_data();
			$query = sprintf("INSERT INTO `%scom_entity_entities` (`tags`, `varlist`) VALUES ('%s', '%s');",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(','.implode(',', $entity->tags).',', $pines->com_mysql->link),
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			$new_id = mysql_insert_id();
			$entity->guid = (int) $new_id;
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$pines->config->com_mysql->prefix,
					(int) $new_id,
					mysql_real_escape_string($name, $pines->com_mysql->link),
					mysql_real_escape_string(serialize($value), $pines->com_mysql->link));
				if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		} else {
			// Save the modified date.
			$entity->p_mdate = microtime(true);
			$data = $entity->get_data();
			$query = sprintf("UPDATE `%scom_entity_entities` SET `tags`='%s', `varlist`='%s' WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(','.implode(',', $entity->tags).',', $pines->com_mysql->link),
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link),
				intval($entity->guid));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				intval($entity->guid));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$pines->config->com_mysql->prefix,
					intval($entity->guid),
					mysql_real_escape_string($name, $pines->com_mysql->link),
					mysql_real_escape_string(serialize($value), $pines->com_mysql->link));
				if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		}
	}

	/**
	 * Set the value of a UID.
	 *
	 * @param string $name The UID's name.
	 * @param int $value The value.
	 * @return bool True on success, false on failure.
	 */
	public function set_uid($name, $value) {
		if (!$name)
			return false;
		global $pines;
		$query = sprintf("INSERT INTO `%scom_entity_uids` (`name`, `cur_uid`) VALUES ('%s', %u) ON DUPLICATE KEY UPDATE `cur_uid`=%u;",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link),
			(int) $value,
			(int) $value);
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return false;
		}
		return true;
	}
}

?>