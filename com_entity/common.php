<?php
/**
 * com_entity's common file.
 *
 * @package XROOM
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('X_RUN') or die('Direct access prohibited');

/**
 * com_entity main class.
 *
 * Provides a MySQL based entity manager for XROOM.
 *
 * @package XROOM
 * @subpackage com_entity
 */
class com_entity extends component {
	public function delete_entity(&$entity) {
		$return = $this->delete_entity_by_id($entity->guid);
		if ( $return )
			$entity->guid = null;
		return $return;
		//unset($entity); //no effect
	}

	public function delete_entity_by_id($guid) {
		global $config;
		$query = sprintf("DELETE FROM `%scom_entity_entities` WHERE `guid`=%u;",
			$config->com_mysql->prefix,
			intval($guid));
		if ( !(mysql_query($query, $config->db_manager->link)) ) {
			display_error('Query failed: ' . mysql_error());
			return false;
		}
		$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
			$config->com_mysql->prefix,
			intval($guid));
		if ( !(mysql_query($query, $config->db_manager->link)) ) {
			display_error('Query failed: ' . mysql_error());
			return false;
		}
		return true;
	}

	public function get_entities_by_data($data, $required_tags = array(), $class = entity) {
		global $config;
		$entities = array();

		$query = sprintf("SELECT `guid` FROM `%scom_entity_data` WHERE `name`='%s' AND `value`='%s'",
			$config->com_mysql->prefix,
			mysql_real_escape_string(key($data), $config->db_manager->link),
			mysql_real_escape_string(serialize(current($data)), $config->db_manager->link));

		for (next($data); current($data); next($data)) {
			$query .= sprintf(" UNION SELECT `guid` FROM `%scom_entity_data` WHERE `name`='%s' AND `value`='%s'",
				$config->com_mysql->prefix,
				mysql_real_escape_string(key($data), $config->db_manager->link),
				mysql_real_escape_string(serialize(current($data)), $config->db_manager->link));
		}

		$query .= ";";

		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			display_error('Query failed: ' . mysql_error());
			return false;
		}

		while ($row = mysql_fetch_array($result)) {
			$entity = $this->get_entity($row['guid'], $class);
			if ( empty($required_tags) || $entity->has_tag($required_tags) )
				array_push($entities, $entity);
		}

		mysql_free_result($result);
		return $entities;
	}

	public function get_entities_by_parent($parent_guid, $class = entity) {
		global $config;
		$entities = array();

		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING e.`parent`=%u ORDER BY e.`guid`;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			intval($parent_guid));
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			display_error('Query failed: ' . mysql_error());
			return false;
		}

		$row = mysql_fetch_array($result);
		while ($row) {
			$entity = new $class;
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

	// This needs to be a shortcut to get_entities_by_tags_exclusive
	public function get_entities_by_tags() {
		if (is_array(func_get_arg(0))) {
            return $this->get_entities_by_tags_exclusive(func_get_arg(0), func_get_arg(1));
		} else {
            return $this->get_entities_by_tags_exclusive(func_get_args());
		}
	}

	/*
	 * Will return any entities which contain *all* of the specified tags.
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
            if (is_subclass_of($tag_array[count($tag_array)], 'entity')) {
                $class = $tag_array[count($tag_array)];
                unset($tag_array[count($tag_array)]);
            } else {
                $class = entity;
            }
		}
		$entities = array();

		if (empty($tag_array)) {
			display_error('Call to get_entities_by_tags_exclusive without tag argument!');
			return false;
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
			display_error('Query failed: ' . mysql_error());
			return false;
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
				$entity = new $class;
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

	/*
	 * Will return any entities which contain *any* of the specified tags.
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
            if (is_subclass_of($tag_array[count($tag_array)], 'entity')) {
                $class = $tag_array[count($tag_array)];
                unset($tag_array[count($tag_array)]);
            } else {
                $class = entity;
            }
		}
		$entities = array();

		if (empty($tag_array)) {
			display_error('Call to get_entities_by_tags_inclusive without tag argument!');
			return false;
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
			display_error('Query failed: ' . mysql_error());
			return false;
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
				$entity = new $class;
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

	/*
	 * Will return any entities which contain *all* of the exclusive_tags *and*
	 * *any* of the inclusive_tags.
	 */
	public function get_entities_by_tags_mixed($exlusive_tags = array(), $inclusive_tags = array(), $class = entity) {
		global $config;
		$entities = array();

		if (!is_array($exlusive_tags) || !is_array($inclusive_tags)) {
			display_error('Call to get_entities_by_tags_mixed with invalid arguments!');
			return false;
		}

		foreach ($exlusive_tags as $cur_tag) {
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
			display_error('Query failed: ' . mysql_error());
			return false;
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
			foreach ($exlusive_tags as $tag) {
				if ( !in_array($tag, $return_tag_array) ) {
					$match = false;
					break;
				}
			}
			if ( $match === true ) {
				$entity = new $class;
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

	public function get_entity($guid, $class = entity) {
		global $config;

        $entity = new $class;
		$data = array();

		$query = sprintf("SELECT e.*, d.`name` AS `dname`, d.`value` AS `dvalue` FROM `%scom_entity_entities` e LEFT JOIN `%scom_entity_data` d ON e.`guid`=d.`guid` HAVING e.`guid`=%u;",
			$config->com_mysql->prefix,
			$config->com_mysql->prefix,
			intval($guid));
		if ( !($result = mysql_query($query, $config->db_manager->link)) ) {
			display_error('Query failed: ' . mysql_error());
			return false;
		}

		if ( !($row = mysql_fetch_array($result)) ) {
			return null;
		}
		$entity->guid = $guid;
		$entity->parent = (is_null($row['parent']) ? NULL : intval($row['parent']));
		$entity->tags = unserialize($row['tags']);

		if ( !is_null($row['dname']) ) {
			do {
				$data[$row['dname']] = unserialize($row['dvalue']);
			} while ($row = mysql_fetch_array($result));
		}

		$entity->put_data($data);

		mysql_free_result($result);
		return $entity;
	}

	public function save_entity($entity) {
		global $config;
		if ( is_null($entity->guid) ) {
			$query = sprintf("INSERT INTO `%scom_entity_entities` (`parent`, `tags`) VALUES (%s, '%s');",
				$config->com_mysql->prefix,
				(is_null($entity->parent) ? 'NULL' : intval($entity->parent)),
				mysql_real_escape_string(serialize($entity->tags), $config->db_manager->link));
			if ( !(mysql_query($query, $config->db_manager->link)) ) {
				display_error('Query failed: ' . mysql_error());
				return false;
			}
			$new_id = mysql_insert_id();
			$entity->guid = $new_id;
			foreach ($entity->get_data() as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$config->com_mysql->prefix,
					intval($new_id),
					mysql_real_escape_string($name, $config->db_manager->link),
					mysql_real_escape_string(serialize($value), $config->db_manager->link));
				if ( !(mysql_query($query, $config->db_manager->link)) ) {
					display_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		} else {
			$query = sprintf("UPDATE `%scom_entity_entities` SET `parent`=%s, `tags`='%s' WHERE `guid`=%u;",
				$config->com_mysql->prefix,
				(is_null($entity->parent) ? 'NULL' : intval($entity->parent)),
				mysql_real_escape_string(serialize($entity->tags), $config->db_manager->link),
				intval($entity->guid));
			if ( !(mysql_query($query, $config->db_manager->link)) ) {
				display_error('Query failed: ' . mysql_error());
				return false;
			}
			$query = sprintf("DELETE FROM `%scom_entity_data` WHERE `guid`=%u;",
				$config->com_mysql->prefix,
				intval($entity->guid));
			if ( !(mysql_query($query, $config->db_manager->link)) ) {
				display_error('Query failed: ' . mysql_error());
				return false;
			}
			foreach ($entity->get_data() as $name => $value) {
				$query = sprintf("INSERT INTO `%scom_entity_data` (`guid`, `name`, `value`) VALUES (%u, '%s', '%s');",
					$config->com_mysql->prefix,
					intval($entity->guid),
					mysql_real_escape_string($name, $config->db_manager->link),
					mysql_real_escape_string(serialize($value), $config->db_manager->link));
				if ( !(mysql_query($query, $config->db_manager->link)) ) {
					display_error('Query failed: ' . mysql_error());
					return false;
				}
			}
			return true;
		}
	}
}

$config->entity_manager = new com_entity;
/* TODO: should entities have ability management?
$config->ability_manager->add('com_entity', 'new', 'New Entities', 'Let user create new entities.');
$config->ability_manager->add('com_entity', 'edit', 'Edit Entities', 'Let user edit entities.');
$config->ability_manager->add('com_entity', 'view', 'View Entities', 'Let user view entities.');
*/

?>