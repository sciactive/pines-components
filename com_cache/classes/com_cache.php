<?php
/**
 * com_cache's class.
 *
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_cache main class.
 *
 * @package Components\cache
 */
class com_cache extends component {
	/**
	 * Whether the Cache Manager scripts have been loaded.
	 * @access private
	 * @var bool $css_loaded
	 */
	private $scripts_loaded = false;
	/**
	 * Load the CSS and JS needed for the Cache Manager.
	 */
	function load() {
		global $pines;
		if (!$this->scripts_loaded) {
			if ($pines->config->compress_cssjs) {
				$file_root = htmlspecialchars($_SERVER['DOCUMENT_ROOT'] . $pines->config->location);
				// Build CSS
				$css = (is_array($pines->config->loadcompressedcss)) ? $pines->config->loadcompressedcss : array();
				$css[] = $file_root . 'components/com_cache/includes/'. ($pines->config->debug_mode ? 'cachemanager.css' : 'cachemanager.min.css');
				$pines->config->loadcompressedcss = $css;

				$js = (is_array($pines->config->loadcompressedjs)) ? $pines->config->loadcompressedjs : array();
				$js[] = $file_root . 'components/com_cache/includes/'.($pines->config->debug_mode ? 'cachemanager.js' : 'cachemanager.min.js');
				$pines->config->loadcompressedjs = $js;
			} else
				$module = new module('com_cache', 'scripts', 'head');
			// Not needed since no other libraries are loaded.
			//$module->render();
			$this->scripts_loaded = true;
		}
	}
	
	
	
	/**
	 * Save the current configuration
	 */
	function saveconfig($directive, $component, $action, $domain, $delete = false, $edit = false) {
		// Save the config to core/system
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		$cachelist = $cacheoptions['cachelist'];
		
		if (!$edit) {
			// Trying to add a new one.
			if (isset($cachelist[$component][$action][$domain]))
				return false;
			if (isset($cachelist[$component][$action]['all']))
				return false;
			if (!empty($cachelist[$component][$action]) && $domain == 'all')
				return false; // Cannot add an all key to an action with specific domains
			// This one is wrong too, because it means we already have an
			// all set on domain - so if the $domain is all, it would have been
			// wrong from the first if, and if it's something else specific it 
			// breaks the rule of only having all, or specifics. 
		}
		
		// If the component exists, be careful not to ruin other actions.
		if (isset($cachelist[$component])) {
			// We have this component with possibly other actions than
			// this one. So we can't replace the entire component directive.
			// We need to insert the action.
			if ($delete) {
				// You can only delete if you have a component for it. You 
				// wouldnt be able to delete something that doesn't exists.
				$temp_cachelist = $cachelist;
				// only unset the action
				unset($temp_cachelist[$component][$action][$domain]); 
				if (empty($temp_cachelist[$component][$action])) {
					unset($temp_cachelist[$component][$action]);
				}
				if (empty($temp_cachelist[$component])) {
					unset($temp_cachelist[$component]);
				}
			} else {
				$options = $directive[$component][$action][$domain];
				$temp_cachelist = $cachelist;
				
				// Need to be careful about domains.
				// Rule 1. the domain can be EITHER 'all' OR several specific 
				// domain keys, but NOT both.
				if (isset($temp_cachelist[$component][$action]['all']) && $domain != 'all') {
					// Replacing the all with a specific domain.
					$temp_cachelist[$component][$action][$domain] = $options;
					unset($temp_cachelist[$component][$action]['all']);
				} else {
					// This specific domain can be added because either there aren't any,
					// or there is a specific one or more.
					$temp_cachelist[$component][$action][$domain] = $options;
				}
			}
			$new_cachelist = $temp_cachelist;
		} else {
			// We have never had this component before, so we can just merge
			// it to the cachelist.
			$new_cachelist = array_merge($cachelist, $directive);
		}
		
		if ($cachelist === $new_cachelist) 
			return true; // Nothing to write.
		else {
			// Write changes.
			$cacheoptions['cachelist'] = $new_cachelist;
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($cacheoptions, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			if ($delete) {
				// Also need to "refresh" aka delete cached files.
				// No point in leaving them around.
				$file_name = ((($component.'.'.$action) == '.') ? 'home' : ($component.'.'.$action)).'.html';
				$file_name = preg_replace('#\.$#', '', $file_name);
				$this->refreshconfig('all', $file_name);
			}
			return true;
		}
	}
	
	
	/**
	 * Save the exceptions.
	 */
	function save_exceptions($component, $action, $domain, $exceptions) {
		// Save the config to core/system
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		$cachelist = $cacheoptions['cachelist'];
		
		$new_cachelist = $cachelist;
		if (!isset($new_cachelist[$component][$action][$domain]))
			return false;
		
		$new_cachelist[$component][$action][$domain]['exceptions'] = $exceptions;
		
		if ($cachelist === $new_cachelist) 
			return true; // Nothing to write.
		else {
			// Write changes.
			$cacheoptions['cachelist'] = $new_cachelist;
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($cacheoptions, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			return true;
		}
	}
	
	/**
	 * Save the exceptions.
	 */
	function save_users($component, $action, $domain, $all_unique, $unique_users) {
		// Save the config to core/system
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		$cachelist = $cacheoptions['cachelist'];
		
		$new_cachelist = $cachelist;
		if (!isset($new_cachelist[$component][$action][$domain]))
			return false;
		
		$new_cachelist[$component][$action][$domain]['unique_users'] = $unique_users;
		$new_cachelist[$component][$action][$domain]['all_unique'] = $all_unique;
		
		if ($cachelist === $new_cachelist) 
			return true; // Nothing to write.
		else {
			// Write changes.
			$cacheoptions['cachelist'] = $new_cachelist;
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($cacheoptions, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			return true;
		}
	}
	
	/**
	 * Look up ability hash by username.
	 */
	function lookup($username) {
		global $pines;
		// Save the config to core/system
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		// Determine the user's ability hash from user info:
		$user = $pines->entity_manager->get_entity(
					array('class' => user),
					array('&',
						'tag' => array('user', 'com_user'),
						'data' => array('username', $username)
					)
				);
		if (!isset($user->guid))
			return false;
		
		$ability_hash = $this->get_ability_hash($user);
		
		$cacheoptions = include('system/cacheoptions.php');
		$cachelist = $cacheoptions['cachelist'];
		
		// We were going to check if unique hash is necessary - but
		// we will just automatically show it:
		$unique = md5($username);
		
		$ability_folder = glob($cacheoptions['parent_directory'].'*/a'.$ability_hash); // All Domains -> Ability hash
		$unique_folder = glob($cacheoptions['parent_directory'].'*/a'.md5($ability_hash.$unique)); // All Domains -> Ability hash
		$ability_count = 0;
		foreach($ability_folder as $cur_folder) {
			$ability_count += $this->get_file_count($cur_folder);
		}
		$unique_count = 0;
		foreach($unique_folder as $cur_folder) {
			$unique_count += $this->get_file_count($cur_folder);
		}
		
		$result = array();
		$result['ability_hash'] = 'a'.$ability_hash;
		$result['ability_count'] = $ability_count;
		if ($unique != false)
			$result['unique_hash'] = 'a'.md5($ability_hash.$unique);
			$result['unique_count'] = $unique_count;
		return $result;
	}
	
	/**
	 * Get Ability hash - used by lookup
	 */
	function get_ability_hash($user_entity) {
		$abilities_array = array();
		if ($user_entity->inherit_abilities) {
			$abilities_array['inherited_abilities'] = $user_entity->abilities;
			foreach ($user_entity->groups as $cur_group) {
				// Check that any group conditions are met before adding the abilities.
				if ($cur_group->conditions && $pines->config->com_user->conditional_groups) {
					$pass = true;
					foreach ($cur_group->conditions as $cur_type => $cur_value) {
						if (!$pines->depend->check($cur_type, $cur_value)) {
							$pass = false;
							break;
						}
					}
					if (!$pass)
						continue;
				}
				// Any conditions are met, so add this group's abilities.
				$abilities_array['inherited_abilities'] = array_merge($abilities_array['inherited_abilities'], $cur_group->abilities);
			}
			if (isset($user_entity->group)) {
				// Check that any group conditions are met before adding the abilities.
				$pass = true;
				if ($user_entity->group->conditions && $pines->config->com_user->conditional_groups) {
					foreach ($user_entity->group->conditions as $cur_type => $cur_value) {
						if (!$pines->depend->check($cur_type, $cur_value)) {
							$pass = false;
							break;
						}
					}
				}
				// If all conditions are met, add this group's abilities.
				if ($pass)
					$abilities_array['inherited_abilities'] = array_merge($abilities_array['inherited_abilities'], $user_entity->group->abilities);
			}
		} else {
			$abilities_array['abilities'] = $user_entity->abilities;
		}
		$abilities = (isset($abilities_array['inherited_abilities'])) ? $abilities_array['inherited_abilities'] : $abilities_array['abilities'];
		asort($abilities);
		return md5(serialize($abilities));
	}
	
	
	/**
	 * Save the exceptions.
	 */
	function save_global_exceptions($users, $groups) {
		// Save the config to core/system
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		$global_exceptions = $cacheoptions['global_exceptions'];
		
		$new_exceptions = array('users' => array_values(array_unique($users)), 'groups' => array_values(array_unique($groups)));
		
		if ($global_exceptions === $new_exceptions) 
			return true; // Nothing to write.
		else {
			// Write changes.
			$cacheoptions['global_exceptions'] = $new_exceptions;
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($cacheoptions, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			return true;
		}
	}
	
	/**
	 * Load the current configuration for com_cache.
	 */
	function loadconfig($use_generic = false, $import = null, $get_file_count = false) {
		// Load the file
		$module = new module('com_cache', 'manager', 'content');
		if (!file_exists('system/cacheoptions.php') && $use_generic) {
			// Use the Sample Cache Options
			$gen_file = include('system/sample_cacheoptions.php');
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($gen_file, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			$cacheoptions = include('system/cacheoptions.php');
		} else if (!empty($import)) {
			// Use the Sample Cache Options
			// Hopefully you give the manage ability to users you trust...
			// Import, exclude the extension.
			$gen_file = include($import.'.php'); 
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($gen_file, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			$cacheoptions = include('system/cacheoptions.php');
		} else if (file_exists('system/cacheoptions.php')) {
			$cacheoptions = include('system/cacheoptions.php');
		}
		
		if (isset($cacheoptions)) {
			$module->cacheoptions = $cacheoptions;
			
			// Check if the cache folder exists.
			if (is_dir($cacheoptions['parent_directory'])) {
				$domains = array();
				$path = $cacheoptions['parent_directory'];
				$results = scandir($path);
				foreach ($results as $result) {
					if ($result === '.' or $result === '..') continue;

					if (is_dir($path . '/' . $result)) {
						//code to use if directory
						$domains[] = array($result, $this->get_file_count($cacheoptions['parent_directory'].$result));
					}
				}
				if (!empty($domains))
					$module->domains = $domains;
				
				if ($get_file_count) {
					$module->get_file_count = true;
				}
			}
		}
		
		if ($use_generic || isset($import)) {
			// Pines Redirect without variables in url.
			pines_redirect(pines_url('com_cache', 'manager'));
		} else {
			return $module;
		}
	}
	
	
	/**
	 * Refresh "Deletes" cached files for a domain or all domains.
	 */
	function refreshconfig($domain = 'all', $file_name = null, $ability_hash = null) {
		// Need to know where the cache dir is. If the cacheoptions file
		// does not exist or is wrong - then it won't refresh/delete files
		// and must be done manually.
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		// Should not be hard because it's either a singular domain,
		// or all domains.
		
		if ($ability_hash != null) {
			// We want to delete one user's hash on all domains.
			$ability_folder = glob($cacheoptions['parent_directory'].'*/'.$ability_hash); // All Domains -> Ability hash
			$count = 0;
			foreach($ability_folder as $cur_folder) {
				$count += $this->get_file_count($cur_folder);
				$this->destroy_dir($cur_folder);
			}
			// Done, Leave.
			return $count;
		}
		
		if ($file_name == null) {
			// Delete domain folders.
			if ($domain == 'all') {
				// Delete cachefolder to delete all domains.
				$this->destroy_dir($cacheoptions['parent_directory']);
				// Make the cache dir again.
				if (is_dir($cacheoptions['parent_directory']))
					mkdir($cacheoptions['parent_directory'], 0700, true);
			} else {
				$this->destroy_dir($cacheoptions['parent_directory'].$domain.'/');
			}
			return true;
		}
		
		// Clean file name
		$file_name = preg_replace('#/#', '.', $file_name);
		
		// Here we just want to refresh certain file patterns.
		if ($domain == 'all') {
			// Notice generic *
			$domain_files = glob($cacheoptions['parent_directory'].'*/'.$file_name); // Domain -> File
			$query_or_ability_files = glob($cacheoptions['parent_directory'].'*/*/'.$file_name); // Domain -> Query OR Ability hashes -> File
			$query_and_ability_files = glob($cacheoptions['parent_directory'].'*/*/*/'.$file_name); // Domain -> Query/Ability hashes -> File
		} else {
			$domain_files = glob($cacheoptions['parent_directory'].$domain.'/'.$file_name); // Specific Domain -> File
			$query_or_ability_files = glob($cacheoptions['parent_directory'].$domain.'/*/'.$file_name); // Domain -> Query OR Ability hashes -> File
			$query_and_ability_files = glob($cacheoptions['parent_directory'].$domain.'/*/*/'.$file_name); // Domain -> Query/Ability hashes -> File
		}
		
		// Always True.
		$unlink_files = array_merge($domain_files, $query_or_ability_files, $query_and_ability_files);
		//return $cacheoptions['parent_directory'].$domain.'/'.$file_name;
		foreach($unlink_files as $cur_path) {
			unlink($cur_path);
			$remove = basename($cur_path);
			$dir = preg_replace('#'.$remove.'#', '', $cur_path);
			if ($this->get_file_count($dir) == 0) {
				rmdir($dir);
			}
		}
		
		// Check domain file counts and delete the folder if there's no more.
		$path = $cacheoptions['parent_directory'];
		$results = scandir($path);
		foreach ($results as $result) {
			if ($result === '.' or $result === '..') continue;

			if (is_dir($path . '/' . $result)) {
				//code to use if directory
				if ($this->get_file_count($cacheoptions['parent_directory'].$result) == 0) {
					$this->destroy_dir($cacheoptions['parent_directory'].$result);
				}
			}
		}
		return true;
	}
	
	/**
	 * Change Cache Settings
	 */
	function changesettings($cache_on = null, $parent_directory = null) {
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		$originaloptions = $cacheoptions;
		
		if (isset($parent_directory)) {
			// Test if there's already a parent directory:
			if (is_dir($cacheoptions['parent_directory'])) {
				// If it is a dir, copy all the files over to this one.
				// If it takes a while for this to run and new items are not copied,
				// oh well - I would rather make sure that the copy went through
				// before deleting the old directory.
				$this->copyr($cacheoptions['parent_directory'], $parent_directory);
				if (is_dir($parent_directory)) {
					// Successful. Delete old one.
					$this->destroy_dir($cacheoptions['parent_directory']);
				} else {
					return false;
				}
			}
			$cacheoptions['parent_directory'] = $parent_directory;
		} else {
			$cacheoptions['cache_on'] = $cache_on;
		}
		
		// Write it:
		if ($cacheoptions == $originaloptions)
			return true;
		else {
			$file_contents = sprintf("<?php\nreturn %s;\n?>",
				var_export($cacheoptions, true)
			);
			file_put_contents('system/cacheoptions.php', $file_contents);
			return true;
		}
	}
	
	
	/**
	 * Change Cache Settings
	 */
	function domain_explore($domain = null, $path = null) {
		if (!file_exists('system/cacheoptions.php'))
			return false;
		
		$cacheoptions = include('system/cacheoptions.php');
		
		if (!isset($cacheoptions['parent_directory']))
			return false;
		
		
		if (!is_dir($cacheoptions['parent_directory'].$domain))
			return false;
		
		if (isset($path)) {
			// Get files from path
			$full_path = $cacheoptions['parent_directory'].$path;
			$files = $this->get_files($full_path);
			return $files;
		}
		
		
		// Get all paths
		$main_children_paths = $this->get_child_directories($cacheoptions['parent_directory'].$domain); // Specific Domain -> Abilities OR Query Hashe FOLDERS.
		
		// Prepare JSTREE data
		// Make Children
		$main_children = array();
		// First Children
		if (!empty($main_children_paths)) {
			foreach ($main_children_paths as $cur_path) {
				$clean_path = preg_replace('#'.$cacheoptions['parent_directory'].'#', '', $cur_path);
				$has_children = $this->dir_contains_children_dirs($cur_path);
				
				// Only have to go deep one time.
				if ($has_children) {
					$children =  $this->get_child_directories($cur_path);
					
					$child_array = array();
					foreach ($children as $cur_child_path) {
						$cur_child_array = array();
						$clean_child_path = preg_replace('#'.$cacheoptions['parent_directory'].'#', '', $cur_child_path);
						
						$cur_child_array['data'] = basename($cur_child_path);
						$cur_child_array['attr'] = (object) array('data-path' => $clean_child_path);
						
						$child_array[] = (object) $cur_child_array;
					}
				}
				
				// Current main child folder: 
				$cur_array = array();
				$cur_array['data'] = basename($cur_path);
				$cur_array['attr'] = (object) array('data-path' => $clean_path);
				if ($has_children) {
					$cur_array['children'] = $child_array;
				}
				
				$main_children[] = (object) $cur_array;
			}
		}
		
		// Add Children to Main Shell for this domain
		$json_data = (object) array(
			'data' => array(
				(object) array(
					'data' => $domain,
					'attr' => (object) array('data-path' => $domain),
					'children' => $main_children
				)
			)
		);
		
		return $json_data;
	}
	
	/**
	 * Copy parent directory to new parent directory recursively.
	 */
	function copyr($source, $dest) {
		// recursive function to copy
		// all subdirectories and contents:
		if(is_dir($source)) {
			$dir_handle=opendir($source);
			$sourcefolder = basename($source);
			mkdir($dest."/", 0700, true);
			while($file=readdir($dir_handle)){
				if($file!="." && $file!=".."){
					if(is_dir($source."/".$file)){
						$this->copyr($source."/".$file, $dest."/".$file);
					} else {
						copy($source."/".$file, $dest."/".$file);
					}
				}
			}
			closedir($dir_handle);
		} else {
			// can also handle simple copy commands
			copy($source, $dest);
		}
	}
	
	/**
	 * Destroy a folder recursively.
	 */
	function destroy_dir($dir) { 
		if (!is_dir($dir) || is_link($dir)) return unlink($dir); 
			foreach (scandir($dir) as $file) { 
				if ($file == '.' || $file == '..') continue; 
				if (!$this->destroy_dir($dir . DIRECTORY_SEPARATOR . $file)) { 
					chmod($dir . DIRECTORY_SEPARATOR . $file, 0777); 
					if (!$this->destroy_dir($dir . DIRECTORY_SEPARATOR . $file)) return false; 
				}; 
			} 
			return rmdir($dir); 
    }
	
	/**
	 * Get the number of files in directory recursively.
	 */
	function get_file_count($path) {
		$size = 0;
		$ignore = array('.','..','cgi-bin','.DS_Store');
		$files = scandir($path);
		foreach($files as $t) {
			if(in_array($t, $ignore)) continue;
			if (is_dir(rtrim($path, '/') . '/' . $t)) {
				$size += $this->get_file_count(rtrim($path, '/') . '/' . $t);
			} else {
				$size++;
			}   
		}
		return $size;
	}
	
	/**
	 * Get files in a directory (not recursive).
	 */
	function get_files($path) {
		$just_files = array();
		$files = scandir($path);
		foreach($files as $t) {
			if ($t == '.' || $t == '..' || is_dir($path.'/'.$t)) continue;
			$time = filemtime($path.'/'.$t);
			$mtime = format_date($time, 'full_sort');
			$timeago = date("c", $time);
			$cur_file = array('filename' => $t, 'mtime' => $mtime, 'timeago' => $timeago);
			$just_files[] = $cur_file;
			
		}
		return $just_files;
	}
	
	/**
	 * Check if directory contains child directories.
	 */
	function dir_contains_children_dirs($dir) {
		$result = false;
		if($dh = opendir($dir)) {
			while (!$result && ($file = readdir($dh))) {
				$result = $file !== "." && $file !== ".." && is_dir($dir.'/'.$file);
			}
			closedir($dh);
		}

		return $result;
	}
	
	/**
	 * Get children directories
	 */
	
	public function get_child_directories($dir){
		if ($handle = opendir($dir)) {
			$blacklist = array('.', '..');
			$directories = array();
			while (false !== ($file = readdir($handle))) {
				$cur_path = $dir.'/'.$file;
				if (!in_array($file, $blacklist) && is_dir($cur_path)) {
					$directories[] = $cur_path;
				}
			}
			closedir($handle);
			return $directories;
		}
	}
}

?>