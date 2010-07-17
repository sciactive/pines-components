<?php
/**
 * com_myentity class.
 *
 * @package Pines
 * @subpackage com_myentity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_myentity main class.
 *
 * Provides a MySQL based entity manager for Pines.
 *
 * @package Pines
 * @subpackage com_myentity
 */
class com_myentity extends component implements entity_manager_interface {
	/**
	 * Sort case sensitively.
	 * @access private
	 * @var bool
	 */
	private $sort_case_sensitive;
	/**
	 * Property to sort by.
	 * @access private
	 * @var string
	 */
	private $sort_property;

	public function delete_entity(&$entity) {
		$return = $this->delete_entity_by_id($entity->guid);
		if ( $return )
			$entity->guid = null;
		return $return;
	}

	public function delete_entity_by_id($guid) {
		global $pines;
		$query = sprintf("DELETE e, d FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` WHERE e.`guid`=%u;",
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

	public function delete_uid($name) {
		if (!$name)
			return false;
		global $pines;
		$query = sprintf("DELETE FROM `%scom_myentity_uids` WHERE `name`='%s';",
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
		if ((array) $value !== $value || !isset($entity))
			return false;
		// Get the GUID, if the passed $entity is an object.
		if ((array) $entity === $entity) {
			foreach($entity as &$cur_entity) {
				if ((object) $cur_entity === $cur_entity)
					$cur_entity = $cur_entity->guid;
			}
			unset($cur_entity);
		} elseif ((object) $entity === $entity) {
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

	public function export($filename) {
		global $pines;
		$filename = clean_filename((string) $filename);
		if (!$fhandle = fopen($filename, 'w'))
			return false;
		fwrite($fhandle, "# Pines Entity Export\n");
		fwrite($fhandle, "# com_myentity version {$pines->info->com_myentity->version}\n");
		fwrite($fhandle, "# sciactive.com\n");
		fwrite($fhandle, "#\n");
		fwrite($fhandle, "# Generation Time: ".date('r')."\n");
		fwrite($fhandle, "# Pines Version: {$pines->info->version}\n\n");

		fwrite($fhandle, "#\n");
		fwrite($fhandle, "# UIDs\n");
		fwrite($fhandle, "#\n\n");

		// Export UIDs.
		$query = sprintf("SELECT * FROM `%scom_myentity_uids`;",
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
			fwrite($fhandle, "<{$row['name']}>[{$row['cur_uid']}]\n");
			// Make sure that $row is incremented :)
			$row = mysql_fetch_array($result);
		}

		fwrite($fhandle, "#\n");
		fwrite($fhandle, "# Entities\n");
		fwrite($fhandle, "#\n\n");

		// Export entities.
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` ORDER BY e.`guid`;",
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
			$p_cdate = (float) $row['cdate'];
			$p_mdate = (float) $row['mdate'];
			fwrite($fhandle, "{{$guid}}[".implode(',', $tags)."]\n");
			fwrite($fhandle, "\tp_cdate=".json_encode(serialize($p_cdate))."\n");
			fwrite($fhandle, "\tp_mdate=".json_encode(serialize($p_mdate))."\n");
			if (isset($row['dname'])) {
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
		return fclose($fhandle);
	}

	public function export_print() {
		global $pines;
		$pines->page->override = true;
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=entities.pex;');
		// End all output buffering.
		while (@ob_end_clean());
		echo "# Pines Entity Export\n";
		echo "# com_myentity version {$pines->info->com_myentity->version}\n";
		echo "# sciactive.com\n";
		echo "#\n";
		echo "# Generation Time: ".date('r')."\n";
		echo "# Pines Version: {$pines->info->version}\n\n";

		echo "#\n";
		echo "# UIDs\n";
		echo "#\n\n";

		// Export UIDs.
		$query = sprintf("SELECT * FROM `%scom_myentity_uids`;",
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
		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` ORDER BY e.`guid`;",
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
			$p_cdate = (float) $row['cdate'];
			$p_mdate = (float) $row['mdate'];
			echo "{{$guid}}[".implode(',', $tags)."]\n";
			echo "\tp_cdate=".json_encode(serialize($p_cdate))."\n";
			echo "\tp_mdate=".json_encode(serialize($p_mdate))."\n";
			if (isset($row['dname'])) {
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

	public function get_entities() {
		global $pines;
		// Set up options and selectors.
		$selectors = func_get_args();
		if (!$selectors) {
			$options = $selectors = array();
		} else {
			$options = $selectors[0];
			unset($selectors[0]);
		}

		$entities = array();
		$class = isset($options['class']) ? $options['class'] : entity;
		$count = $ocount = 0;

		$query_parts = array();
		foreach ($selectors as &$cur_selector) {
			$cur_selector_query = '';
			foreach ($cur_selector as $key => &$value) {
				if ($key === 0) {
					$type = $value;
					$type_is_not = ($type == '!&' || $type == '!|');
					$type_is_or = ($type == '|' || $type == '!|');
					continue;
				}
				$cur_query = '';
				if ((array) $value !== $value)
					$value = array(array($value));
				elseif ((array) $value[0] !== $value[0])
					$value = array($value);
				// Any options having to do with data only return if the entity has
				// the specified variables.
				foreach ($value as $cur_value) {
					switch ($key) {
						case 'guid':
							foreach ($cur_value as $cur_guid) {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`guid`='.(int) $cur_guid;
							}
							break;
						case 'tag':
							foreach ($cur_value as $cur_tag) {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'LOCATE(\','.mysql_real_escape_string($cur_tag, $pines->com_mysql->link).',\', e.`tags`)';
							}
							break;
						case 'data':
						case 'strict':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`cdate`='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`mdate`='.((float) $cur_value[1]);
								break;
							}
						case 'gt':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`cdate`>'.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`mdate`>'.((float) $cur_value[1]);
								break;
							}
						case 'gte':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`cdate`>='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`mdate`>='.((float) $cur_value[1]);
								break;
							}
						case 'lt':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`cdate`<'.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`mdate`<'.((float) $cur_value[1]);
								break;
							}
						case 'lte':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`cdate`<='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e.`mdate`<='.((float) $cur_value[1]);
								break;
							}
						case 'array':
						case 'match':
						case 'ref':
							if (!$type_is_not) {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= 'LOCATE(\','.mysql_real_escape_string($cur_value[0], $pines->com_mysql->link).',\', e.`varlist`)';
							}
							break;
					}
				}
				if ( $cur_query ) {
					if ($cur_selector_query)
						$cur_selector_query .= $type_is_or ? ' OR ' : ' AND ';
					$cur_selector_query .= $cur_query;
				}
			}
			unset($value);
			if ($cur_selector_query)
				$query_parts[] = $cur_selector_query;
		}
		unset($cur_selector);

		if ($query_parts) {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` HAVING %s ORDER BY %s;",
				$pines->config->com_mysql->prefix,
				$pines->config->com_mysql->prefix,
				'('.implode(') AND (', $query_parts).')',
				$options['reverse'] ? 'e.`guid` DESC' : 'e.`guid`');
		} else {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` ORDER BY %s;",
				$pines->config->com_mysql->prefix,
				$pines->config->com_mysql->prefix,
				$options['reverse'] ? 'e.`guid` DESC' : 'e.`guid`');
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
			if ($ocount >= $options['offset'])
				$tags = explode(',', substr($row['tags'], 1, -1));
			$data = array('p_cdate' => (float) $row['cdate'], 'p_mdate' => (float) $row['mdate']);
			if (isset($row['dname'])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
				do {
					// Only remember this entity's data if we're at/past the offset.
					if ($ocount >= $options['offset'])
						$data[$row['dname']] = unserialize($row['dvalue']);
					$row = mysql_fetch_array($result);
				} while ((int) $row['guid'] === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = mysql_fetch_array($result);
			}
			if ($ocount < $options['offset']) {
				$ocount++;
				continue;
			}
			// Recheck all conditions.
			$pass_all = true;
			foreach ($selectors as &$cur_selector) {
				$pass = false;
				foreach ($cur_selector as $key => &$value) {
					if ($key === 0) {
						$type = $value;
						$type_is_not = ($type == '!&' || $type == '!|');
						$type_is_or = ($type == '|' || $type == '!|');
						$pass = !$type_is_or;
						continue;
					}
					// Check if it doesn't pass any for &, check if it
					// passes any for |.
					foreach ($value as $cur_value) {
						switch ($key) {
							case 'guid':
							case 'tag':
								// These are handled by the query.
								$pass = true;
								break;
							case 'data':
								if ($type_is_or xor $type_is_not) {
									if ($data[$cur_value[0]] == $cur_value[1])
										$pass = !$type_is_not;
								} else {
									if ($data[$cur_value[0]] != $cur_value[1])
										$pass = $type_is_not;
								}
								break;
							case 'strict':
								if ($type_is_or xor $type_is_not) {
									if ($data[$cur_value[0]] === $cur_value[1])
										$pass = !$type_is_not;
								} else {
									if ($data[$cur_value[0]] !== $cur_value[1])
										$pass = $type_is_not;
								}
								break;
							case 'array':
								if ($type_is_or xor $type_is_not) {
									if ((array) $data[$cur_value[0]] === $data[$cur_value[0]] && in_array($cur_value[1], $data[$cur_value[0]]))
										$pass = !$type_is_not;
								} else {
									if ((array) $data[$cur_value[0]] !== $data[$cur_value[0]] || !in_array($cur_value[1], $data[$cur_value[0]]))
										$pass = $type_is_not;
								}
								break;
							case 'match':
								if ($type_is_or xor $type_is_not) {
									if (isset($data[$cur_value[0]]) && preg_match($cur_value[1], $data[$cur_value[0]]))
										$pass = !$type_is_not;
								} else {
									if (!isset($data[$cur_value[0]]) || !preg_match($cur_value[1], $data[$cur_value[0]]))
										$pass = $type_is_not;
								}
								break;
							case 'gt':
								if ($type_is_or xor $type_is_not) {
									if ($data[$cur_value[0]] > $cur_value[1])
										$pass = !$type_is_not;
								} else {
									if (!($data[$cur_value[0]] > $cur_value[1]))
										$pass = $type_is_not;
								}
								break;
							case 'gte':
								if ($type_is_or xor $type_is_not) {
									if ($data[$cur_value[0]] >= $cur_value[1])
										$pass = !$type_is_not;
								} else {
									if (!($data[$cur_value[0]] >= $cur_value[1]))
										$pass = $type_is_not;
								}
								break;
							case 'lt':
								if ($type_is_or xor $type_is_not) {
									if ($data[$cur_value[0]] < $cur_value[1])
										$pass = !$type_is_not;
								} else {
									if (!($data[$cur_value[0]] < $cur_value[1]))
										$pass = $type_is_not;
								}
								break;
							case 'lte':
								if ($type_is_or xor $type_is_not) {
									if ($data[$cur_value[0]] <= $cur_value[1])
										$pass = !$type_is_not;
								} else {
									if (!($data[$cur_value[0]] <= $cur_value[1]))
										$pass = $type_is_not;
								}
								break;
							case 'ref':
								if ($type_is_or xor $type_is_not) {
									if (isset($data[$cur_value[0]]) && (array) $data[$cur_value[0]] === $data[$cur_value[0]] && $this->entity_reference_search($data[$cur_value[0]], $cur_value[1]))
										$pass = !$type_is_not;
								} else {
									if (!isset($data[$cur_value[0]]) || (array) $data[$cur_value[0]] !== $data[$cur_value[0]] || !$this->entity_reference_search($data[$cur_value[0]], $cur_value[1]))
										$pass = $type_is_not;
								}
								break;
						}
						if ($type_is_or) {
							if ($pass)
								break;
						} else {
							if (!$pass)
								break;
						}
					}
					if ($type_is_or) {
						if ($pass)
							break;
					} else {
						if (!$pass)
							break;
					}
				}
				if (!$pass) {
					$pass_all = false;
					break;
				}
			}
			if ($pass_all) {
				$entity = call_user_func(array($class, 'factory'));
				$entity->guid = $guid;
				$entity->tags = $tags;
				$entity->put_data($data);
				$entities[] = $entity;
				$count++;
				if ($options['limit'] && $count >= $options['limit'])
					break;
			}
		}

		mysql_free_result($result);
		return $entities;
	}

	public function get_entity() {
		// Set up options and selectors.
		$args = func_get_args();
		if (!$args)
			$args = array(array());
		if ((array) $args[0] !== $args[0])
			$args = array(array(), array('&', 'guid' => (int) $args[0]));
		$args[0]['limit'] = 1;
		$entities = call_user_func_array(array($this, 'get_entities'), $args);
		if (!$entities)
			return null;
		return $entities[0];
	}

	public function get_uid($name) {
		if (!$name)
			return null;
		global $pines;
		$query = sprintf("SELECT `cur_uid` FROM `%scom_myentity_uids` WHERE `name`='%s';",
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
			if (preg_match('/^\s*#/S', $line)) {
				$line = '';
				continue;
			}
			$matches = array();
			if (preg_match('/^\s*{(\d+)}\[([\w,]+)\]\s*$/S', $line, $matches)) {
				// Save the current entity.
				if ($guid) {
					$query = sprintf("REPLACE INTO `%scom_myentity_entities` (`guid`, `tags`, `varlist`, `cdate`, `mdate`) VALUES (%u, '%s', '%s', %F, %F);",
						$pines->config->com_mysql->prefix,
						$guid,
						mysql_real_escape_string(','.$tags.',', $pines->com_mysql->link),
						mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link),
						unserialize($data['p_cdate']),
						unserialize($data['p_mdate']));
					if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
						if (function_exists('pines_error'))
							pines_error('Query failed: ' . mysql_error());
						return false;
					}
					$query = sprintf("DELETE FROM `%scom_myentity_data` WHERE `guid`=%u;",
						$pines->config->com_mysql->prefix,
						$guid);
					if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
						if (function_exists('pines_error'))
							pines_error('Query failed: ' . mysql_error());
						return false;
					}
					unset($data['p_cdate'], $data['p_mdate']);
					if ($data) {
						$query = "INSERT INTO `{$pines->config->com_mysql->prefix}com_myentity_data` (`guid`, `name`, `value`) VALUES ";
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
			} elseif (preg_match('/^\s*([\w,]+)\s*=\s*(\S.*\S)\s*$/S', $line, $matches)) {
				// Add the variable to the new entity.
				if ($guid)
					$data[$matches[1]] = json_decode($matches[2]);
			} elseif (preg_match('/^\s*<([^>]+)>\[(\d+)\]\s*$/S', $line, $matches)) {
				// Add the UID.
				$query = sprintf("INSERT INTO `%scom_myentity_uids` (`name`, `cur_uid`) VALUES ('%s', %u) ON DUPLICATE KEY UPDATE `cur_uid`=%u;",
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
			$query = sprintf("REPLACE INTO `%scom_myentity_entities` (`guid`, `tags`, `varlist`, `cdate`, `mdate`) VALUES (%u, '%s', '%s', %F, %F);",
				$pines->config->com_mysql->prefix,
				$guid,
				mysql_real_escape_string(','.$tags.',', $pines->com_mysql->link),
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link),
				unserialize($data['p_cdate']),
				unserialize($data['p_mdate']));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_myentity_data` WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				$guid);
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			if ($data) {
				$query = "INSERT INTO `{$pines->config->com_mysql->prefix}com_myentity_data` (`guid`, `name`, `value`) VALUES ";
				unset($data['p_cdate'], $data['p_mdate']);
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

	public function new_uid($name) {
		if (!$name)
			return null;
		global $pines;
		$query = sprintf("SELECT GET_LOCK('%scom_myentity_uids_%s', 10);",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$query = sprintf("INSERT INTO `%scom_myentity_uids` (`name`, `cur_uid`) VALUES ('%s', 1) ON DUPLICATE KEY UPDATE `cur_uid`=`cur_uid`+1;",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$query = sprintf("SELECT `cur_uid` FROM `%scom_myentity_uids` WHERE `name`='%s';",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !($result = mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		$row = mysql_fetch_row($result);
		mysql_free_result($result);
		$query = sprintf("SELECT RELEASE_LOCK('%scom_myentity_uids_%s');",
			$pines->config->com_mysql->prefix,
			mysql_real_escape_string($name, $pines->com_mysql->link));
		if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . mysql_error());
			return null;
		}
		return isset($row[0]) ? (int) $row[0] : null;
	}

	public function rename_uid($old_name, $new_name) {
		if (!$old_name || !$new_name)
			return false;
		global $pines;
		$query = sprintf("UPDATE `%scom_myentity_uids` SET `name`='%s' WHERE `name`='%s';",
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
	 * @todo Use one big insert query.
	 */
	public function save_entity(&$entity) {
		global $pines;
		if ( !isset($entity->guid) ) {
			// Save the created date.
			$entity->p_cdate = microtime(true);
			// And modified date.
			$entity->p_mdate = microtime(true);
			$data = $entity->get_data();
			$query = sprintf("INSERT INTO `%scom_myentity_entities` (`tags`, `varlist`, `cdate`, `mdate`) VALUES ('%s', '%s', %F, %F);",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(','.implode(',', $entity->tags).',', $pines->com_mysql->link),
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link),
				$data['p_cdate'],
				$data['p_mdate']);
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			$new_id = mysql_insert_id();
			$entity->guid = (int) $new_id;
			unset($data['p_cdate'], $data['p_mdate']);
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_myentity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
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
			$query = sprintf("UPDATE `%scom_myentity_entities` SET `tags`='%s', `varlist`='%s', `cdate`=%F, `mdate`=%F WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(','.implode(',', $entity->tags).',', $pines->com_mysql->link),
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link),
				$data['p_cdate'],
				$data['p_mdate'],
				intval($entity->guid));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_myentity_data` WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				intval($entity->guid));
			if ( !(mysql_query($query, $pines->com_mysql->link)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . mysql_error());
				return false;
			}
			unset($data['p_cdate'], $data['p_mdate']);
			foreach ($data as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_myentity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
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

	public function set_uid($name, $value) {
		if (!$name)
			return false;
		global $pines;
		$query = sprintf("INSERT INTO `%scom_myentity_uids` (`name`, `cur_uid`) VALUES ('%s', %u) ON DUPLICATE KEY UPDATE `cur_uid`=%u;",
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

	public function sort(&$array, $property = null, $parent_property = null, $case_sensitive = false, $reverse = false) {
		// First sort by the requested property.
		if (isset($property)) {
			$this->sort_property = $property;
			$this->sort_case_sensitive = $case_sensitive;
			@usort($array, array($this, 'sort_property'));
		}
		if ($reverse)
			$array = array_reverse($array);
		if (!isset($parent_property))
			return;
		// Now sort by children.
		$new_array = array();
		// Count the children.
		$child_counter = array();
		while ($array) {
			// Look for entities ready to go in order.
			$changed = false;
			foreach ($array as $key => &$cur_entity) {
				// Must break after adding one, so any following children don't go in the wrong order.
				if (!isset($cur_entity->$parent_property) || !$cur_entity->$parent_property->in_array(array_merge($new_array, $array))) {
					// If they have no parent (or their parent isn't in the array), they go on the end.
					$new_array[] = $cur_entity;
					unset($array[$key]);
					$changed = true;
					break;
				} else {
					// Else find the parent.
					$pkey = $cur_entity->$parent_property->array_search($new_array);
					if ($pkey !== false) {
						// And insert after the parent.
						// This makes entities go to the end of the child list.
						$cur_ancestor = $cur_entity->$parent_property;
						while (isset($cur_ancestor)) {
							$child_counter[$cur_ancestor->guid]++;
							$cur_ancestor = $cur_ancestor->$parent_property;
						}
						// Where to place the entity.
						$new_key = $pkey + $child_counter[$cur_entity->$parent_property->guid];
						if (isset($new_array[$new_key])) {
							// If it already exists, we have to splice it in.
							array_splice($new_array, $new_key, 0, array($cur_entity));
							$new_array = array_values($new_array);
						} else {
							// Else just add it.
							$new_array[$new_key] = $cur_entity;
						}
						unset($array[$key]);
						$changed = true;
						break;
					}
				}
			}
			unset($cur_entity);
			if (!$changed) {
				// If there are any unexpected errors and the array isn't changed, just stick the rest on the end.
				$entities_left = array_splice($array, 0);
				$new_array = array_merge($new_array, $entities_left);
			}
		}
		// Now push the new array out.
		$array = $new_array;
	}

	/**
	 * Determine the sort order between two entities.
	 *
	 * @param entity $a Entity A.
	 * @param entity $b Entity B.
	 * @return int Sort order.
	 * @access private
	 */
	private function sort_property($a, $b) {
		$property = $this->sort_property;
		if (!$this->sort_case_sensitive && is_string($a->$property) && is_string($b->$property)) {
			$aprop = strtoupper($a->$property);
			$bprop = strtoupper($b->$property);
			if ($aprop > $bprop)
				return 1;
			if ($aprop < $bprop)
				return -1;
		} else {
			if ($a->$property > $b->$property)
				return 1;
			if ($a->$property < $b->$property)
				return -1;
		}
		return 0;
	}
}

?>