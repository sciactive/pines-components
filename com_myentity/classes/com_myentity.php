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
		if (!is_array($value) || !isset($entity))
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
							$cur_query .= 'e.`guid`='.(int) $cur_guid;
						}
					} else {
						$cur_query = 'e.`guid`='.(int) $option;
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
//					$tmp_query = sprintf('SELECT GROUP_CONCAT(`guid`) AS `guids` FROM `%scom_myentity_data` WHERE ',
//						$pines->config->com_mysql->prefix);
//					$tmp_query_parts = array();
//					foreach ($option as $cur_name => $cur_value) {
//						$tmp_query_parts[] = '(`name`=\''.mysql_real_escape_string($cur_name, $pines->com_mysql->link).'\' AND `value`=\''.mysql_real_escape_string(serialize($cur_value), $pines->com_mysql->link).'\')';
//					}
//					$tmp_query .= implode(' AND ', $tmp_query_parts).';';
//					if ( !($result = mysql_query($tmp_query, $pines->com_mysql->link)) ) {
//						if (function_exists('pines_error'))
//							pines_error('Query failed: ' . mysql_error());
//						return null;
//					}
//					$row = mysql_fetch_array($result);
//					mysql_free_result($result);
//					if (isset($row['guids']))
//						$cur_query .= "e.`guid` IN ({$row['guids']})";
//					break;
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`cdate`='.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`mdate`='.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'array':
				case 'match':
				case 'gt':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`cdate`>'.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`mdate`>'.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'gte':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`cdate`>='.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`mdate`>='.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'lt':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`cdate`<'.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`mdate`<'.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'lte':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`cdate`<='.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'e.`mdate`<='.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'ref':
					foreach ($option as $cur_name => $cur_value) {
						if ( $cur_query )
							$cur_query .= ' AND ';
						$cur_query .= 'LOCATE(\','.mysql_real_escape_string($cur_name, $pines->com_mysql->link).',\', e.`varlist`)';
					}
					break;
				case 'data_i':
//					$tmp_query = sprintf('SELECT GROUP_CONCAT(`guid`) AS `guids` FROM `%scom_myentity_data` WHERE ',
//						$pines->config->com_mysql->prefix);
//					$tmp_query_parts = array();
//					foreach ($option as $cur_name => $cur_value) {
//						$tmp_query_parts[] = '(`name`=\''.mysql_real_escape_string($cur_name, $pines->com_mysql->link).'\' AND `value`=\''.mysql_real_escape_string(serialize($cur_value), $pines->com_mysql->link).'\')';
//					}
//					$tmp_query .= implode(' OR ', $tmp_query_parts).';';
//					if ( !($result = mysql_query($tmp_query, $pines->com_mysql->link)) ) {
//						if (function_exists('pines_error'))
//							pines_error('Query failed: ' . mysql_error());
//						return null;
//					}
//					$row = mysql_fetch_array($result);
//					mysql_free_result($result);
//					if (isset($row['guids']))
//						$cur_query .= "e.`guid` IN ({$row['guids']})";
//					break;
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`cdate`='.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`mdate`='.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'array_i':
				case 'match_i':
				case 'gt_i':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`cdate`>'.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`mdate`>'.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'gte_i':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`cdate`>='.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`mdate`>='.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'lt_i':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`cdate`<'.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`mdate`<'.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
				case 'lte_i':
					if (isset($option['p_cdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`cdate`<='.((float) $option['p_cdate']);
						unset($option['p_cdate']);
					}
					if (isset($option['p_mdate'])) {
						if ( $cur_query )
							$cur_query .= ' OR ';
						$cur_query .= 'e.`mdate`<='.((float) $option['p_mdate']);
						unset($option['p_cdate']);
					}
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
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` HAVING %s ORDER BY e.`guid`;",
				$pines->config->com_mysql->prefix,
				$pines->config->com_mysql->prefix,
				'('.implode(') AND (', $query_parts).')');
		} else {
			$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_myentity_entities` e LEFT JOIN `%scom_myentity_data` d ON e.`guid`=d.`guid` ORDER BY e.`guid`;",
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
			$data = array('p_cdate' => (float) $row['cdate'], 'p_mdate' => (float) $row['mdate']);
			if (isset($row['dname'])) {
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
//						if (is_array($option)) {
//							$pass = $pass && in_array($guid, $option);
//						} else {
//							$pass = $pass && ($guid == $option);
//						}
//						break;
					case 'tags':
//						if (is_array($option)) {
//							foreach($option as $cur_option) {
//								if (!($pass = $pass && in_array($cur_option, $tags)))
//									break 2;
//							}
//						} else {
//							$pass = $pass && in_array($option, $tags);
//						}
//						break;
					case 'tags_i':
//						$found = false;
//						foreach ($option as $cur_option) {
//							if (in_array($cur_option, $tags)) {
//								$found = true;
//								break;
//							}
//						}
//						$pass = $pass && $found;
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
					case 'array':
						foreach ($option as $cur_key => $cur_option) {
							if (!is_array($data[$cur_key]) || !in_array($cur_option, $data[$cur_key])) {
								$pass = false;
								break 2;
							}
						}
						break;
					case 'array_i':
						$found = false;
						foreach ($option as $cur_key => $cur_option) {
							if (key_exists($cur_key, $data) && is_array($data[$cur_key]) && in_array($cur_option, $data[$cur_key])) {
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

	public function get_entity($options) {
		if (!is_array($options))
			$options = array('guid' => (int) $options);
		$options['limit'] = 1;
		$entities = $this->get_entities($options);
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
			if (preg_match('/^\s*#/', $line)) {
				$line = '';
				continue;
			}
			$matches = array();
			if (preg_match('/^\s*{(\d+)}\[([\w,]+)\]\s*$/', $line, $matches)) {
				// Save the current entity.
				if ($guid) {
					$query = sprintf("REPLACE INTO `%scom_myentity_entities` (`guid`, `tags`, `cdate`, `mdate`, `varlist`) VALUES (%u, '%s', %F, %F, '%s');",
						$pines->config->com_mysql->prefix,
						$guid,
						mysql_real_escape_string(','.$tags.',', $pines->com_mysql->link),
						$data['p_cdate'],
						$data['p_mdate'],
						mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link));
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
			} elseif (preg_match('/^\s*([\w,]+)\s*=\s*(\S.*\S)\s*$/', $line, $matches)) {
				// Add the variable to the new entity.
				if ($guid)
					$data[$matches[1]] = json_decode($matches[2]);
			} elseif (preg_match('/^\s*<([^>]+)>\[(\d+)\]\s*$/', $line, $matches)) {
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
			$query = sprintf("REPLACE INTO `%scom_myentity_entities` (`guid`, `tags`, `cdate`, `mdate`, `varlist`) VALUES (%u, '%s', %F, %F, '%s');",
				$pines->config->com_mysql->prefix,
				$guid,
				mysql_real_escape_string(','.$tags.',', $pines->com_mysql->link),
				$data['p_cdate'],
				$data['p_mdate'],
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link));
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
			$query = sprintf("INSERT INTO `%scom_myentity_entities` (`tags`, `cdate`, `mdate`, `varlist`) VALUES ('%s', %F, %F, '%s');",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(','.implode(',', $entity->tags).',', $pines->com_mysql->link),
				$data['p_cdate'],
				$data['p_mdate'],
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link));
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
			$query = sprintf("UPDATE `%scom_myentity_entities` SET `tags`='%s', `cdate`=%F, `mdate`=%F, `varlist`='%s' WHERE `guid`=%u;",
				$pines->config->com_mysql->prefix,
				mysql_real_escape_string(','.implode(',', $entity->tags).',', $pines->com_mysql->link),
				$data['p_cdate'],
				$data['p_mdate'],
				mysql_real_escape_string(','.implode(',', array_keys($data)).',', $pines->com_mysql->link),
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
}

?>