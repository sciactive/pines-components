<?php
/**
 * com_pgentity class.
 *
 * @package Pines
 * @subpackage com_pgentity
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_pgentity main class.
 *
 * Provides a PostgreSQL based entity manager for Pines.
 *
 * @package Pines
 * @subpackage com_pgentity
 */
class com_pgentity extends component implements entity_manager_interface {
	/**
	 * A cache to make entity retrieval faster.
	 * @access private
	 * @var array
	 */
	private $entity_cache = array();
	/**
	 * A counter for the entity cache to determine the most accessed entities.
	 * @access private
	 * @var array
	 */
	private $entity_count = array();
	/**
	 * Sort case sensitively.
	 * @access private
	 * @var bool
	 */
	private $sort_case_sensitive;
	/**
	 * Parent property to sort by.
	 * @access private
	 * @var string
	 */
	private $sort_parent;
	/**
	 * Property to sort by.
	 * @access private
	 * @var string
	 */
	private $sort_property;
	/**
	 * Whether to use PL/Perl.
	 * @access private
	 * @var string
	 */
	private $use_plperl;

	/**
	 * Load the entity manager.
	 */
	public function __construct() {
		global $pines;
		$this->use_plperl = $pines->config->com_pgentity->use_plperl;
	}

	/**
	 * Remove all copies of an entity from the cache.
	 *
	 * @param int $guid The GUID of the entity to remove.
	 * @access private
	 */
	private function clean_cache($guid) {
		unset($this->entity_cache[$guid]);
	}

	/**
	 * Create entity tables in the database.
	 *
	 * @return bool True on success, false on failure.
	 */
	private function create_tables() {
		global $pines;
		if ( !(@pg_query($pines->com_pgsql->link, 'BEGIN;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		// Create the entity table.
		$query = sprintf("CREATE SEQUENCE \"%scom_pgentity_entities_guid_seq\";",
			$pines->config->com_pgsql->prefix);
		$query .= sprintf(" CREATE TABLE \"%scom_pgentity_entities\" ( guid bigint NOT NULL DEFAULT nextval('%scom_pgentity_entities_guid_seq'), tags text[], varlist text[], cdate numeric(18,6) NOT NULL, mdate numeric(18,6) NOT NULL, PRIMARY KEY (guid) ) WITH ( OIDS=FALSE ); ALTER TABLE \"%scom_pgentity_entities\" OWNER TO \"%s\";",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $pines->config->com_pgsql->user));
		$query .= sprintf(" ALTER SEQUENCE \"%scom_pgentity_entities_guid_seq\" OWNED BY \"%scom_pgentity_entities\".guid;",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix);
		$query .= sprintf(" CREATE INDEX \"%scom_pgentity_entities_id_cdate\" ON \"%scom_pgentity_entities\" USING btree (cdate); CREATE INDEX \"%scom_pgentity_entities_id_mdate\" ON \"%scom_pgentity_entities\" USING btree (mdate); CREATE INDEX \"%scom_pgentity_entities_id_tags\" ON \"%scom_pgentity_entities\" USING gin (tags); CREATE INDEX \"%scom_pgentity_entities_id_varlist\" ON \"%scom_pgentity_entities\" USING gin (varlist);",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		// Create the data table.
		$query = sprintf("CREATE TABLE \"%scom_pgentity_data\" ( guid bigint NOT NULL, \"name\" text NOT NULL, \"value\" text NOT NULL, \"references\" bigint[], compare_true boolean, compare_one boolean, compare_zero boolean, compare_negone boolean, compare_emptyarray boolean, compare_string text, PRIMARY KEY (guid, \"name\"), FOREIGN KEY (guid) REFERENCES \"%scom_pgentity_entities\" (guid) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE CASCADE ) WITH ( OIDS=FALSE ); ALTER TABLE \"%scom_pgentity_data\" OWNER TO \"%s\";",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $pines->config->com_pgsql->user));
		$query .= sprintf(" CREATE INDEX \"%scom_pgentity_data_id_guid\" ON \"%scom_pgentity_data\" USING btree (\"guid\"); CREATE INDEX \"%scom_pgentity_data_id_name\" ON \"%scom_pgentity_data\" USING btree (\"name\"); CREATE INDEX \"%scom_pgentity_data_id_references\" ON \"%scom_pgentity_data\" USING gin (\"references\"); CREATE INDEX \"%scom_pgentity_data_id_guid_name_compare_true\" ON \"%scom_pgentity_data\" USING btree (\"guid\", \"name\") WHERE \"compare_true\" = TRUE; CREATE INDEX \"%scom_pgentity_data_id_guid_name_not_compare_true\" ON \"%scom_pgentity_data\" USING btree (\"guid\", \"name\") WHERE \"compare_true\" <> TRUE; CREATE INDEX \"%scom_pgentity_data_id_guid_name__user\" ON \"%scom_pgentity_data\" USING btree (\"guid\") WHERE \"name\" = 'user'::text; CREATE INDEX \"%scom_pgentity_data_id_guid_name__group\" ON \"%scom_pgentity_data\" USING btree (\"guid\") WHERE \"name\" = 'group'::text;",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		// Create the UID table.
		$query = sprintf("CREATE TABLE \"%scom_pgentity_uids\" ( \"name\" text NOT NULL, cur_uid bigint NOT NULL, PRIMARY KEY (\"name\") ) WITH ( OIDS = FALSE ); ALTER TABLE \"%scom_pgentity_uids\" OWNER TO \"%s\";",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $pines->config->com_pgsql->user));
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		if ( !(@pg_query($pines->com_pgsql->link, 'COMMIT;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		// Create the perl_match function. It's separated into two calls so
		// Postgres will ignore the error if plperl already exists.
		$query = sprintf('CREATE PROCEDURAL LANGUAGE plperl;',
			$pines->config->com_pgsql->prefix);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
		}
		$query = sprintf('CREATE OR REPLACE FUNCTION %smatch_perl( TEXT, TEXT, TEXT ) RETURNS BOOL AS $code$ my ($str, $pattern, $mods) = @_; if ($pattern eq \'\') { return true; } my $vname = "/$pattern/$mods"; if (! defined $_SHARED{$vname}) { if ($mods eq \'\') { $_SHARED{$vname} = qr/($pattern)/o; } else { $_SHARED{$vname} = qr/(?$mods)($pattern)/o; } } if ($str =~ $_SHARED{$vname}) { return true; } else { return false; } $code$ LANGUAGE plperl IMMUTABLE STRICT COST 10000;',
			$pines->config->com_pgsql->prefix);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error("Couldn't create Perl Matching function. You should turn off PL/Perl Functions in com_pgentity's functions.\n\nQuery failed: " . pg_last_error());
		}
		return true;
	}

	public function delete_entity(&$entity) {
		$return = $this->delete_entity_by_id($entity->guid);
		if ( $return )
			$entity->guid = null;
		return $return;
	}

	public function delete_entity_by_id($guid) {
		global $pines;
		$query = sprintf("DELETE FROM \"%scom_pgentity_entities\" WHERE \"guid\"=%u; DELETE FROM \"%scom_pgentity_data\" WHERE \"guid\"=%u;",
			$pines->config->com_pgsql->prefix,
			(int) $guid,
			$pines->config->com_pgsql->prefix,
			(int) $guid);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		// Removed any cached versions of this entity.
		if ($pines->config->com_pgentity->cache)
			$this->clean_cache($guid);
		return true;
	}

	public function delete_uid($name) {
		if (!$name)
			return false;
		global $pines;
		$query = sprintf("DELETE FROM \"%scom_pgentity_uids\" WHERE \"name\"='%s';",
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $name));
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
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
		fwrite($fhandle, "# com_pgentity version {$pines->info->com_pgentity->version}\n");
		fwrite($fhandle, "# sciactive.com\n");
		fwrite($fhandle, "#\n");
		fwrite($fhandle, "# Generation Time: ".date('r')."\n");
		fwrite($fhandle, "# Pines Version: {$pines->info->version}\n\n");

		fwrite($fhandle, "#\n");
		fwrite($fhandle, "# UIDs\n");
		fwrite($fhandle, "#\n\n");

		// Export UIDs.
		$query = sprintf("SELECT * FROM \"%scom_pgentity_uids\";",
			$pines->config->com_pgsql->prefix);
		if ( !($result = pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		$row = pg_fetch_assoc($result);
		while ($row) {
			$row['name'];
			$row['cur_uid'];
			fwrite($fhandle, "<{$row['name']}>[{$row['cur_uid']}]\n");
			// Make sure that $row is incremented :)
			$row = pg_fetch_assoc($result);
		}

		fwrite($fhandle, "#\n");
		fwrite($fhandle, "# Entities\n");
		fwrite($fhandle, "#\n\n");

		// Export entities.
		$query = sprintf("SELECT e.*, d.\"name\" AS \"dname\", d.\"value\" AS \"dvalue\" FROM \"%scom_pgentity_entities\" e LEFT JOIN \"%scom_pgentity_data\" d ON e.\"guid\"=d.\"guid\" ORDER BY e.\"guid\";",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix);
		if ( !($result = pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		$row = pg_fetch_assoc($result);
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
					$row = pg_fetch_assoc($result);
				} while ((int) $row['guid'] === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = pg_fetch_assoc($result);
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
		echo "# com_pgentity version {$pines->info->com_pgentity->version}\n";
		echo "# sciactive.com\n";
		echo "#\n";
		echo "# Generation Time: ".date('r')."\n";
		echo "# Pines Version: {$pines->info->version}\n\n";

		echo "#\n";
		echo "# UIDs\n";
		echo "#\n\n";

		// Export UIDs.
		$query = sprintf("SELECT * FROM \"%scom_pgentity_uids\";",
			$pines->config->com_pgsql->prefix);
		if ( !($result = pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		$row = pg_fetch_assoc($result);
		while ($row) {
			$row['name'];
			$row['cur_uid'];
			echo "<{$row['name']}>[{$row['cur_uid']}]\n";
			// Make sure that $row is incremented :)
			$row = pg_fetch_assoc($result);
		}

		echo "#\n";
		echo "# Entities\n";
		echo "#\n\n";

		// Export entities.
		$query = sprintf("SELECT e.*, d.\"name\" AS \"dname\", d.\"value\" AS \"dvalue\" FROM \"%scom_pgentity_entities\" e LEFT JOIN \"%scom_pgentity_data\" d ON e.\"guid\"=d.\"guid\" ORDER BY e.\"guid\";",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix);
		if ( !($result = pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		$row = pg_fetch_assoc($result);
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
					$row = pg_fetch_assoc($result);
				} while ((int) $row['guid'] === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = pg_fetch_assoc($result);
			}
		}
		return true;
	}

	public function get_entities() {
		global $pines;
		if (!$pines->com_pgsql->connected)
			return null;
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

		// Check if the requested entity is cached.
		if ($pines->config->com_pgentity->cache && is_int($selectors[1]['guid'])) {
			// Only safe to use the cache option with no other selectors than a GUID and tags.
			if (
					count($selectors) == 1 &&
					$selectors[1][0] == '&' &&
					(
						(count($selectors[1]) == 2) ||
						(count($selectors[1]) == 3 && isset($selectors[1]['tag']))
					)
				) {
				$entity = $this->pull_cache($selectors[1]['guid'], $class);
				if (isset($entity) && (!isset($selectors[1]['tag']) || $entity->has_tag($selectors[1]['tag']))) {
					$entity->_p_use_skip_ac = (bool) $options['skip_ac'];
					return array($entity);
				}
			}
		}

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
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid"='.(int) $cur_guid;
							}
							break;
						case 'tag':
							foreach ($cur_value as $cur_tag) {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'\'{'.pg_escape_string($pines->com_pgsql->link, $cur_tag).'}\' <@ e."tags"';
							}
							break;
						case 'isset':
							foreach ($cur_value as $cur_var) {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= '('.($type_is_not ? 'NOT ' : '' ).'\'{'.pg_escape_string($pines->com_pgsql->link, $cur_var).'}\' <@ e."varlist"';
								if ($type_is_not)
									$cur_query .= ' OR e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_var).'\' AND "value"=\'N;\')';
								$cur_query .= ')';
							}
							break;
						case 'ref':
							$guids = array();
							if ((array) $cur_value[1] === $cur_value[1]) {
								foreach ($cur_value[1] as $cur_entity) {
									if ((object) $cur_entity === $cur_entity) {
										$guids[] = (int) $cur_entity->guid;
									} else {
										$guids[] = (int) $cur_entity;
									}
								}
							} elseif ((object) $cur_value[1] === $cur_value[1]) {
								$guids[] = (int) $cur_value[1]->guid;
							} else {
								$guids[] = (int) $cur_value[1];
							}
							if ($guids) {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND (';
								//$cur_query .= '(POSITION(\'a:3:{i:0;s:22:"pines_entity_reference";i:1;i:';
								//$cur_query .= implode(';\' IN "value") != 0) '.($type_is_or ? 'OR' : 'AND').' (POSITION(\'a:3:{i:0;s:22:"pines_entity_reference";i:1;i:', $guids);
								//$cur_query .= ';\' IN "value") != 0)';
								$cur_query .= '\'{';
								$cur_query .= implode('}\' <@ "references"'.($type_is_or ? ' OR ' : ' AND ').'\'{', $guids);
								$cur_query .= '}\' <@ "references"';
								$cur_query .=  '))';
							}
							break;
						case 'strict':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate"='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate"='.((float) $cur_value[1]);
								break;
							} else {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								if (is_callable(array($cur_value[1], 'to_reference')))
									$svalue = serialize($cur_value[1]->to_reference());
								else
									$svalue = serialize($cur_value[1]);
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "value"=\''.pg_escape_string($pines->com_pgsql->link, $svalue).'\')';
							}
							break;
						case 'match':
							if ($this->use_plperl) {
								if ($cur_value[0] == 'p_cdate') {
									if ( $cur_query )
										$cur_query .= $type_is_or ? ' OR ' : ' AND ';
									$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate"='.((float) $cur_value[1]);
									break;
								} elseif($cur_value[0] == 'p_mdate') {
									if ( $cur_query )
										$cur_query .= $type_is_or ? ' OR ' : ' AND ';
									$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate"='.((float) $cur_value[1]);
									break;
								} else {
									if ( $cur_query )
										$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
									$lastslashpos = strrpos($cur_value[1], '/');
									$regex = substr($cur_value[1], 1, $lastslashpos - 1);
									$mods = substr($cur_value[1], $lastslashpos + 1);
									if (!$mods)
										$mods = '';
									$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "compare_string" IS NOT NULL AND '.$pines->config->com_pgsql->prefix.'match_perl("compare_string", E\''.pg_escape_string($pines->com_pgsql->link, $regex).'\', \''.pg_escape_string($pines->com_pgsql->link, $mods).'\'))';
								}
							} else {
								if (!$type_is_not) {
									if ( $cur_query )
										$cur_query .= $type_is_or ? ' OR ' : ' AND ';
									$cur_query .= '\'{'.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'}\' <@ e."varlist"';
								}
								break;
							}
							break;
						case 'data':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate"='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate"='.((float) $cur_value[1]);
								break;
							} elseif ($cur_value[1] === true || $cur_value[1] === false) {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "compare_true"='.($cur_value[1] ? 'TRUE' : 'FALSE').')';
								break;
							} elseif ($cur_value[1] === 1) {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "compare_one"=TRUE)';
								break;
							} elseif ($cur_value[1] === 0) {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "compare_zero"=TRUE)';
								break;
							} elseif ($cur_value[1] === -1) {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "compare_negone"=TRUE)';
								break;
							} elseif ($cur_value[1] === array()) {
								if ( $cur_query )
									$cur_query .= ($type_is_or ? ' OR ' : ' AND ');
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."guid" IN (SELECT "guid" FROM "'.$pines->config->com_pgsql->prefix.'com_pgentity_data" WHERE "name"=\''.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'\' AND "compare_emptyarray"=TRUE)';
								break;
							}
						case 'gt':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate">'.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate">'.((float) $cur_value[1]);
								break;
							}
						case 'gte':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate">='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate">='.((float) $cur_value[1]);
								break;
							}
						case 'lt':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate"<'.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate"<'.((float) $cur_value[1]);
								break;
							}
						case 'lte':
							if ($cur_value[0] == 'p_cdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."cdate"<='.((float) $cur_value[1]);
								break;
							} elseif($cur_value[0] == 'p_mdate') {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= ($type_is_not ? 'NOT ' : '' ).'e."mdate"<='.((float) $cur_value[1]);
								break;
							}
						case 'array':
							if (!$type_is_not) {
								if ( $cur_query )
									$cur_query .= $type_is_or ? ' OR ' : ' AND ';
								$cur_query .= '\'{'.pg_escape_string($pines->com_pgsql->link, $cur_value[0]).'}\' <@ e."varlist"';
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
			$query = sprintf("SELECT e.\"guid\", e.\"tags\", e.\"cdate\", e.\"mdate\", d.\"name\", d.\"value\" FROM \"%scom_pgentity_entities\" e LEFT JOIN \"%scom_pgentity_data\" d USING (\"guid\") WHERE %s ORDER BY %s;",
				$pines->config->com_pgsql->prefix,
				$pines->config->com_pgsql->prefix,
				'('.implode(') AND (', $query_parts).')',
				$options['reverse'] ? 'e."guid" DESC' : 'e."guid"');
		} else {
			$query = sprintf("SELECT e.\"guid\", e.\"tags\", e.\"cdate\", e.\"mdate\", d.\"name\", d.\"value\" FROM \"%scom_pgentity_entities\" e LEFT JOIN \"%scom_pgentity_data\" d USING (\"guid\") ORDER BY %s;",
				$pines->config->com_pgsql->prefix,
				$pines->config->com_pgsql->prefix,
				$options['reverse'] ? 'e."guid" DESC' : 'e."guid"');
		}

		//$time = microtime(true);
		if ( !(@pg_send_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		if ( !($result = @pg_get_result($pines->com_pgsql->link)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		if ($error = pg_result_error_field($result, PGSQL_DIAG_SQLSTATE)) {
			// If the tables don't exist yet, create them.
			if ($error == '42P01' && $this->create_tables()) {
				if ( !($result = @pg_query($pines->com_pgsql->link, $query)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . pg_last_error());
					return null;
				}
			} else {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return null;
			}
		}
		//$pines->log_manager->log("Query: (".(microtime(true) - $time)."s): $query", 'notice');

		$row = pg_fetch_row($result);
		while ($row) {
			$guid = (int) $row[0];
			// Don't bother getting the tags unless we're at/past the offset.
			if ($ocount >= $options['offset'])
				$tags = $row[1];
			$data = array('p_cdate' => (float) $row[2], 'p_mdate' => (float) $row[3]);
			// Serialized data.
			$sdata = array();
			if (isset($row[4])) {
				// This do will keep going and adding the data until the
				// next entity is reached. $row will end on the next entity.
				do {
					// Only remember this entity's data if we're at/past the offset.
					if ($ocount >= $options['offset'])
						$sdata[$row[4]] = $row[5];
					$row = pg_fetch_row($result);
				} while ((int) $row[0] === $guid);
			} else {
				// Make sure that $row is incremented :)
				$row = pg_fetch_row($result);
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
						if ($key === 'ref' && isset($sdata[$cur_value[0]])) {
							// If possible, do a quick entity reference check
							// instead of unserializing all the data.
							if ((array) $cur_value[1] === $cur_value[1]) {
								foreach ($cur_value[1] as $cur_entity) {
									if ((object) $cur_entity === $cur_entity) {
										$pass = ((strpos($sdata[$cur_value[0]], "a:3:{i:0;s:22:\"pines_entity_reference\";i:1;i:{$cur_entity->guid};") !== false) xor $type_is_not);
										if (!($type_is_or xor $pass))
											break;
									} else {
										$pass = ((strpos($sdata[$cur_value[0]], "a:3:{i:0;s:22:\"pines_entity_reference\";i:1;i:{$cur_entity};") !== false) xor $type_is_not);
										if (!($type_is_or xor $pass))
											break;
									}
								}
							} elseif ((object) $cur_value[1] === $cur_value[1]) {
								$pass = ((strpos($sdata[$cur_value[0]], "a:3:{i:0;s:22:\"pines_entity_reference\";i:1;i:{$cur_value[1]->guid};") !== false) xor $type_is_not);
							} else {
								$pass = ((strpos($sdata[$cur_value[0]], "a:3:{i:0;s:22:\"pines_entity_reference\";i:1;i:{$cur_value[1]};") !== false) xor $type_is_not);
							}
						} else {
							// Unserialize the data for this variable.
							if (isset($sdata[$cur_value[0]])) {
								$data[$cur_value[0]] = unserialize($sdata[$cur_value[0]]);
								unset($sdata[$cur_value[0]]);
							}
							switch ($key) {
								case 'guid':
								case 'tag':
									// These are handled by the query.
									$pass = true;
									break;
								case 'isset':
									$pass = (isset($data[$cur_value[0]]) xor $type_is_not);
									break;
								case 'data':
									$pass = (($data[$cur_value[0]] == $cur_value[1]) xor $type_is_not);
									break;
								case 'strict':
									$pass = (($data[$cur_value[0]] === $cur_value[1]) xor $type_is_not);
									break;
								case 'array':
									$pass = (((array) $data[$cur_value[0]] === $data[$cur_value[0]] && in_array($cur_value[1], $data[$cur_value[0]])) xor $type_is_not);
									break;
								case 'match':
									$pass = ((isset($data[$cur_value[0]]) && preg_match($cur_value[1], $data[$cur_value[0]])) xor $type_is_not);
									break;
								case 'gt':
									$pass = (($data[$cur_value[0]] > $cur_value[1]) xor $type_is_not);
									break;
								case 'gte':
									$pass = (($data[$cur_value[0]] >= $cur_value[1]) xor $type_is_not);
									break;
								case 'lt':
									$pass = (($data[$cur_value[0]] < $cur_value[1]) xor $type_is_not);
									break;
								case 'lte':
									$pass = (($data[$cur_value[0]] <= $cur_value[1]) xor $type_is_not);
									break;
								case 'ref':
									$pass = ((isset($data[$cur_value[0]]) && (array) $data[$cur_value[0]] === $data[$cur_value[0]] && $this->entity_reference_search($data[$cur_value[0]], $cur_value[1])) xor $type_is_not);
									break;
							}
						}
						if (!($type_is_or xor $pass))
							break;
					}
					if (!($type_is_or xor $pass))
						break;
				}
				unset($value);
				if (!$pass) {
					$pass_all = false;
					break;
				}
			}
			unset($cur_selector);
			if ($pass_all) {
				if ($pines->config->com_pgentity->cache) {
					$entity = $this->pull_cache($guid, $class);
				} else {
					$entity = null;
				}
				if (!isset($entity) || $data['p_mdate'] > $entity->p_mdate) {
					$entity = call_user_func(array($class, 'factory'));
					$entity->guid = $guid;
					$entity->tags = explode(',', substr($tags, 1, -1));
					$entity->put_data($data, $sdata);
					if ($pines->config->com_pgentity->cache)
						$this->push_cache($entity, $class);
				}
				$entity->_p_use_skip_ac = (bool) $options['skip_ac'];
				$entities[] = $entity;
				$count++;
				if ($options['limit'] && $count >= $options['limit'])
					break;
			}
		}

		pg_free_result($result);

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
		$query = sprintf("SELECT \"cur_uid\" FROM \"%scom_pgentity_uids\" WHERE \"name\"='%s';",
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $name));
		if ( !($result = pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		$row = pg_fetch_row($result);
		pg_free_result($result);
		return isset($row[0]) ? (int) $row[0] : null;
	}

	public function hsort(&$array, $property = null, $parent_property = null, $case_sensitive = false, $reverse = false) {
		// First sort by the requested property.
		$this->sort($array, $property, $case_sensitive, $reverse);
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

	public function import($filename) {
		global $pines;
		$filename = clean_filename((string) $filename);
		if (!$fhandle = fopen($filename, 'r'))
			return false;
		$line = '';
		$data = array();
		if ( !(@pg_query($pines->com_pgsql->link, 'BEGIN;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
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
					$query = sprintf("DELETE FROM \"%scom_pgentity_entities\" WHERE \"guid\"=%u; INSERT INTO \"%scom_pgentity_entities\" (\"guid\", \"tags\", \"varlist\", \"cdate\", \"mdate\") VALUES (%u, '%s', '%s', %F, %F);",
						$pines->config->com_pgsql->prefix,
						$guid,
						$pines->config->com_pgsql->prefix,
						$guid,
						pg_escape_string($pines->com_pgsql->link, '{'.$tags.'}'),
						pg_escape_string($pines->com_pgsql->link, '{'.implode(',', array_keys($data)).'}'),
						unserialize($data['p_cdate']),
						unserialize($data['p_mdate']));
					if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
						if (function_exists('pines_error'))
							pines_error('Query failed: ' . pg_last_error());
						return false;
					}
					$query = sprintf("DELETE FROM \"%scom_pgentity_data\" WHERE \"guid\"=%u;",
						$pines->config->com_pgsql->prefix,
						$guid);
					if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
						if (function_exists('pines_error'))
							pines_error('Query failed: ' . pg_last_error());
						return false;
					}
					unset($data['p_cdate'], $data['p_mdate']);
					if ($data) {
						$query = "INSERT INTO \"{$pines->config->com_pgsql->prefix}com_pgentity_data\" (\"guid\", \"name\", \"value\", \"references\", \"compare_true\", \"compare_one\", \"compare_zero\", \"compare_negone\", \"compare_emptyarray\", \"compare_string\") VALUES ";
						foreach ($data as $name => $value) {
							preg_match_all('/a:3:\{i:0;s:22:"pines_entity_reference";i:1;i:(\d+);/', $value, $references, PREG_PATTERN_ORDER);
							$uvalue = unserialize($value);
							$query .= sprintf("(%u, '%s', '%s', '%s', %s, %s, %s, %s, %s, %s),",
								$guid,
								pg_escape_string($pines->com_pgsql->link, $name),
								pg_escape_string($pines->com_pgsql->link, $value),
								pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $references[1]).'}'),
								$uvalue == true ? 'TRUE' : 'FALSE',
								$uvalue == 1 ? 'TRUE' : 'FALSE',
								$uvalue == 0 ? 'TRUE' : 'FALSE',
								$uvalue == -1 ? 'TRUE' : 'FALSE',
								$uvalue == array() ? 'TRUE' : 'FALSE',
								is_string($uvalue) ? '\''.pg_escape_string($pines->com_pgsql->link, $uvalue).'\'' : 'NULL');
						}
						$query = substr($query, 0, -1).';';
						if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
							if (function_exists('pines_error'))
								pines_error('Query failed: ' . pg_last_error());
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
				$query = sprintf("DELETE FROM \"%scom_pgentity_uids\" WHERE \"name\"='%s'; INSERT INTO \"%scom_pgentity_uids\" (\"name\", \"cur_uid\") VALUES ('%s', %u);",
					$pines->config->com_pgsql->prefix,
					pg_escape_string($pines->com_pgsql->link, $matches[1]),
					$pines->config->com_pgsql->prefix,
					pg_escape_string($pines->com_pgsql->link, $matches[1]),
					(int) $matches[2]);
				if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . pg_last_error());
					return false;
				}
			}
			$line = '';
			// Clear the entity cache.
			$this->entity_cache = array();
		}
		// Save the last entity.
		if ($guid) {
			$query = sprintf("DELETE FROM \"%scom_pgentity_entities\" WHERE \"guid\"=%u; INSERT INTO \"%scom_pgentity_entities\" (\"guid\", \"tags\", \"varlist\", \"cdate\", \"mdate\") VALUES (%u, '%s', '%s', %F, %F);",
				$pines->config->com_pgsql->prefix,
				$guid,
				$pines->config->com_pgsql->prefix,
				$guid,
				pg_escape_string($pines->com_pgsql->link, '{'.$tags.'}'),
				pg_escape_string($pines->com_pgsql->link, '{'.implode(',', array_keys($data)).'}'),
				unserialize($data['p_cdate']),
				unserialize($data['p_mdate']));
			if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
			$query = sprintf("DELETE FROM \"%scom_pgentity_data\" WHERE \"guid\"=%u;",
				$pines->config->com_pgsql->prefix,
				$guid);
			if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
			if ($data) {
				$query = "INSERT INTO \"{$pines->config->com_pgsql->prefix}com_pgentity_data\" (\"guid\", \"name\", \"value\", \"references\", \"compare_true\", \"compare_one\", \"compare_zero\", \"compare_negone\", \"compare_emptyarray\", \"compare_string\") VALUES ";
				unset($data['p_cdate'], $data['p_mdate']);
				foreach ($data as $name => $value) {
					preg_match_all('/a:3:\{i:0;s:22:"pines_entity_reference";i:1;i:(\d+);/', $value, $references, PREG_PATTERN_ORDER);
					$uvalue = unserialize($value);
					$query .= sprintf("(%u, '%s', '%s', '%s', %s, %s, %s, %s, %s, %s),",
						$guid,
						pg_escape_string($pines->com_pgsql->link, $name),
						pg_escape_string($pines->com_pgsql->link, $value),
						pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $references[1]).'}'),
						$uvalue == true ? 'TRUE' : 'FALSE',
						$uvalue == 1 ? 'TRUE' : 'FALSE',
						$uvalue == 0 ? 'TRUE' : 'FALSE',
						$uvalue == -1 ? 'TRUE' : 'FALSE',
						$uvalue == array() ? 'TRUE' : 'FALSE',
						is_string($uvalue) ? '\''.pg_escape_string($pines->com_pgsql->link, $uvalue).'\'' : 'NULL');
				}
				$query = substr($query, 0, -1).';';
				if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
					if (function_exists('pines_error'))
						pines_error('Query failed: ' . pg_last_error());
					return false;
				}
			}
		}
		// Update the GUID sequence.
		$query = sprintf("SELECT setval('%scom_pgentity_entities_guid_seq', (SELECT max(\"guid\") FROM \"%scom_pgentity_entities\"));",
			$pines->config->com_pgsql->prefix,
			$pines->config->com_pgsql->prefix);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		if ( !(@pg_query($pines->com_pgsql->link, 'COMMIT;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		return true;
	}

	public function new_uid($name) {
		if (!$name)
			return null;
		global $pines;
		if ( !(@pg_query($pines->com_pgsql->link, 'BEGIN;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		$query = sprintf("SELECT \"cur_uid\" FROM \"%scom_pgentity_uids\" WHERE \"name\"='%s' FOR UPDATE;",
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $name));
		if ( !($result = @pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		$row = pg_fetch_row($result);
		$cur_uid = (int) $row[0];
		pg_free_result($result);
		if (!$cur_uid) {
			$cur_uid = 1;
			$query = sprintf("INSERT INTO \"%scom_pgentity_uids\" (\"name\", \"cur_uid\") VALUES ('%s', {$cur_uid});",
				$pines->config->com_pgsql->prefix,
				pg_escape_string($pines->com_pgsql->link, $name));
			if ( !(@pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return null;
			}
		} else {
			$cur_uid++;
			$query = sprintf("UPDATE \"%scom_pgentity_uids\" SET \"cur_uid\"={$cur_uid} WHERE \"name\"='%s';",
				$pines->config->com_pgsql->prefix,
				pg_escape_string($pines->com_pgsql->link, $name));
			if ( !(@pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return null;
			}
		}
		if ( !(@pg_query($pines->com_pgsql->link, 'COMMIT;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return null;
		}
		return $cur_uid;
	}

	public function psort(&$array, $property = null, $parent_property = null, $case_sensitive = false, $reverse = false) {
		// Sort by the requested property.
		if (isset($property)) {
			$this->sort_property = $property;
			$this->sort_parent = $parent_property;
			$this->sort_case_sensitive = $case_sensitive;
			@usort($array, array($this, 'sort_property'));
		}
		if ($reverse)
			$array = array_reverse($array);
	}

	/**
	 * Pull an entity from the cache.
	 *
	 * @param int $guid The entity's GUID.
	 * @param string $class The entity's class.
	 * @return entity|null The entity or null if it's not cached.
	 * @access private
	 */
	private function pull_cache($guid, $class) {
		// Increment the entity access count.
		if (!isset($this->entity_count[$guid]))
			$this->entity_count[$guid] = 0;
		$this->entity_count[$guid]++;
		if (isset($this->entity_cache[$guid][$class]))
			return (clone $this->entity_cache[$guid][$class]);
		return null;
	}

	/**
	 * Push an entity onto the cache.
	 *
	 * @param entity &$entity The entity to push onto the cache.
	 * @param string $class The class of the entity.
	 * @access private
	 */
	private function push_cache(&$entity, $class) {
		global $pines;
		if (!isset($entity->guid))
			return;
		// Increment the entity access count.
		if (!isset($this->entity_count[$entity->guid]))
			$this->entity_count[$entity->guid] = 0;
		$this->entity_count[$entity->guid]++;
		// Check the threshold.
		if ($this->entity_count[$entity->guid] < $pines->config->com_pgentity->cache_threshold)
			return;
		// Cache the entity.
		if ((array) $this->entity_cache[$entity->guid] === $this->entity_cache[$entity->guid]) {
			$this->entity_cache[$entity->guid][$class] = clone $entity;
		} else {
			while ($pines->config->com_pgentity->cache_limit && count($this->entity_cache) >= $pines->config->com_pgentity->cache_limit) {
				// Find which entity has been accessed the least.
				asort($this->entity_count);
				foreach ($this->entity_count as $key => $val) {
					if (isset($this->entity_cache[$key]))
						break;
				}
				// Remove it.
				if (isset($this->entity_cache[$key]))
					unset($this->entity_cache[$key]);
			}
			$this->entity_cache[$entity->guid] = array($class => (clone $entity));
		}
		$this->entity_cache[$entity->guid][$class]->clear_cache();
	}

	public function rename_uid($old_name, $new_name) {
		if (!$old_name || !$new_name)
			return false;
		global $pines;
		$query = sprintf("UPDATE \"%scom_pgentity_uids\" SET \"name\"='%s' WHERE \"name\"='%s';",
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $new_name),
			pg_escape_string($pines->com_pgsql->link, $old_name));
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		return true;
	}

	/**
	 * @todo Check that the big insert query doesn't fail.
	 */
	public function save_entity(&$entity) {
		global $pines;
		// Save the created date.
		if ( !isset($entity->guid) )
			$entity->p_cdate = microtime(true);
		// Save the modified date.
		$entity->p_mdate = microtime(true);
		$data = $entity->get_data();
		$sdata = $entity->get_sdata();
		$varlist = array_merge(array_keys($data), array_keys($sdata));
		if ( !(@pg_query($pines->com_pgsql->link, 'BEGIN;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		if ( !isset($entity->guid) ) {
			$query = sprintf("INSERT INTO \"%scom_pgentity_entities\" (\"tags\", \"varlist\", \"cdate\", \"mdate\") VALUES ('%s', '%s', %F, %F) RETURNING \"guid\";",
				$pines->config->com_pgsql->prefix,
				pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $entity->tags).'}'),
				pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $varlist).'}'),
				(float) $data['p_cdate'],
				(float) $data['p_mdate']);
			if ( !($result = pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
			$row = pg_fetch_row($result);
			$new_id = (int) $row[0];
			pg_free_result($result);
			$entity->guid = (int) $new_id;
			unset($data['p_cdate'], $data['p_mdate']);
			$values = array();
			foreach ($data as $name => $value) {
				$svalue = serialize($value);
				preg_match_all('/a:3:\{i:0;s:22:"pines_entity_reference";i:1;i:(\d+);/', $svalue, $references, PREG_PATTERN_ORDER);
				$values[] = sprintf('(%u, \'%s\', \'%s\', \'%s\', %s, %s, %s, %s, %s, %s)',
					$new_id,
					pg_escape_string($pines->com_pgsql->link, $name),
					pg_escape_string($pines->com_pgsql->link, $svalue),
					pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $references[1]).'}'),
					$value == true ? 'TRUE' : 'FALSE',
					$value == 1 ? 'TRUE' : 'FALSE',
					$value == 0 ? 'TRUE' : 'FALSE',
					$value == -1 ? 'TRUE' : 'FALSE',
					$value == array() ? 'TRUE' : 'FALSE',
					is_string($value) ? '\''.pg_escape_string($pines->com_pgsql->link, $value).'\'' : 'NULL');
			}
			foreach ($sdata as $name => $value) {
				preg_match_all('/a:3:\{i:0;s:22:"pines_entity_reference";i:1;i:(\d+);/', $value, $references, PREG_PATTERN_ORDER);
				$uvalue = unserialize($value);
				$values[] = sprintf('(%u, \'%s\', \'%s\', \'%s\', %s, %s, %s, %s, %s, %s)',
					$new_id,
					pg_escape_string($pines->com_pgsql->link, $name),
					pg_escape_string($pines->com_pgsql->link, $value),
					pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $references[1]).'}'),
					$uvalue == true ? 'TRUE' : 'FALSE',
					$uvalue == 1 ? 'TRUE' : 'FALSE',
					$uvalue == 0 ? 'TRUE' : 'FALSE',
					$uvalue == -1 ? 'TRUE' : 'FALSE',
					$uvalue == array() ? 'TRUE' : 'FALSE',
					is_string($uvalue) ? '\''.pg_escape_string($pines->com_pgsql->link, $uvalue).'\'' : 'NULL');
			}
			$query = sprintf("INSERT INTO \"%scom_pgentity_data\" (\"guid\", \"name\", \"value\", \"references\", \"compare_true\", \"compare_one\", \"compare_zero\", \"compare_negone\", \"compare_emptyarray\", \"compare_string\") VALUES %s;",
				$pines->config->com_pgsql->prefix,
				implode(',', $values));
			if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
		} else {
			// Removed any cached versions of this entity.
			if ($pines->config->com_pgentity->cache)
				$this->clean_cache($entity->guid);
			$query = sprintf("UPDATE \"%scom_pgentity_entities\" SET \"tags\"='%s', \"varlist\"='%s', \"cdate\"=%F, \"mdate\"=%F WHERE \"guid\"=%u;",
				$pines->config->com_pgsql->prefix,
				pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $entity->tags).'}'),
				pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $varlist).'}'),
				(float) $data['p_cdate'],
				(float) $data['p_mdate'],
				(int) $entity->guid);
			if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
			$query = sprintf("DELETE FROM \"%scom_pgentity_data\" WHERE \"guid\"=%u;",
				$pines->config->com_pgsql->prefix,
				(int) $entity->guid);
			if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
			unset($data['p_cdate'], $data['p_mdate']);
			$values = array();
			foreach ($data as $name => $value) {
				$svalue = serialize($value);
				preg_match_all('/a:3:\{i:0;s:22:"pines_entity_reference";i:1;i:(\d+);/', $svalue, $references, PREG_PATTERN_ORDER);
				$values[] = sprintf('(%u, \'%s\', \'%s\', \'%s\', %s, %s, %s, %s, %s, %s)',
					(int) $entity->guid,
					pg_escape_string($pines->com_pgsql->link, $name),
					pg_escape_string($pines->com_pgsql->link, $svalue),
					pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $references[1]).'}'),
					$value == true ? 'TRUE' : 'FALSE',
					$value == 1 ? 'TRUE' : 'FALSE',
					$value == 0 ? 'TRUE' : 'FALSE',
					$value == -1 ? 'TRUE' : 'FALSE',
					$value == array() ? 'TRUE' : 'FALSE',
					is_string($value) ? '\''.pg_escape_string($pines->com_pgsql->link, $value).'\'' : 'NULL');
			}
			foreach ($sdata as $name => $value) {
				preg_match_all('/a:3:\{i:0;s:22:"pines_entity_reference";i:1;i:(\d+);/', $value, $references, PREG_PATTERN_ORDER);
				$uvalue = unserialize($value);
				$values[] = sprintf('(%u, \'%s\', \'%s\', \'%s\', %s, %s, %s, %s, %s, %s)',
					(int) $entity->guid,
					pg_escape_string($pines->com_pgsql->link, $name),
					pg_escape_string($pines->com_pgsql->link, $value),
					pg_escape_string($pines->com_pgsql->link, '{'.implode(',', $references[1]).'}'),
					$uvalue == true ? 'TRUE' : 'FALSE',
					$uvalue == 1 ? 'TRUE' : 'FALSE',
					$uvalue == 0 ? 'TRUE' : 'FALSE',
					$uvalue == -1 ? 'TRUE' : 'FALSE',
					$uvalue == array() ? 'TRUE' : 'FALSE',
					is_string($uvalue) ? '\''.pg_escape_string($pines->com_pgsql->link, $uvalue).'\'' : 'NULL');
			}
			$query = sprintf("INSERT INTO \"%scom_pgentity_data\" (\"guid\", \"name\", \"value\", \"references\", \"compare_true\", \"compare_one\", \"compare_zero\", \"compare_negone\", \"compare_emptyarray\", \"compare_string\") VALUES %s;",
				$pines->config->com_pgsql->prefix,
				implode(',', $values));
			if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
				if (function_exists('pines_error'))
					pines_error('Query failed: ' . pg_last_error());
				return false;
			}
		}
		if ( !(@pg_query($pines->com_pgsql->link, 'COMMIT;')) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		// Cache the entity.
		if ($pines->config->com_pgentity->cache) {
			$class = get_class($entity);
			// Replace hook override in the class name.
			if (strpos($class, 'hook_override_') === 0)
				$class = substr($class, 14);
			$this->push_cache($entity, $class);
		}
		return true;
	}

	public function set_uid($name, $value) {
		if (!$name)
			return false;
		global $pines;
		$query = sprintf("DELETE FROM \"%scom_pgentity_uids\" WHERE \"name\"='%s'; INSERT INTO \"%scom_pgentity_uids\" (\"name\", \"cur_uid\") VALUES ('%s', %u);",
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $name),
			$pines->config->com_pgsql->prefix,
			pg_escape_string($pines->com_pgsql->link, $name),
			(int) $value,
			(int) $value);
		if ( !(pg_query($pines->com_pgsql->link, $query)) ) {
			if (function_exists('pines_error'))
				pines_error('Query failed: ' . pg_last_error());
			return false;
		}
		return true;
	}

	public function sort(&$array, $property = null, $case_sensitive = false, $reverse = false) {
		// Sort by the requested property.
		if (isset($property)) {
			$this->sort_property = $property;
			$this->sort_parent = null;
			$this->sort_case_sensitive = $case_sensitive;
			@usort($array, array($this, 'sort_property'));
		}
		if ($reverse)
			$array = array_reverse($array);
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
		$parent = $this->sort_parent;
		if (isset($parent) && (isset($a->$parent->$property) || isset($b->$parent->$property))) {
			if (!$this->sort_case_sensitive && is_string($a->$parent->$property) && is_string($b->$parent->$property)) {
				$aprop = strtoupper($a->$parent->$property);
				$bprop = strtoupper($b->$parent->$property);
				if ($aprop > $bprop)
					return 1;
				if ($aprop < $bprop)
					return -1;
			} else {
				if ($a->$parent->$property > $b->$parent->$property)
					return 1;
				if ($a->$parent->$property < $b->$parent->$property)
					return -1;
			}
		}
		// If they have the same parent, order them by their own property.
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