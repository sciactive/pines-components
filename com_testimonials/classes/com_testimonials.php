<?php
/**
 * com_testimonials class.
 *
 * @package Components\testimonials
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_testimonials main class.
 *
 * @package Components\testimonials
 */
class com_testimonials extends component {
	/**
	 * Whether the psteps JavaScript has been loaded.
	 * @access private
	 * @var bool $js_loaded
	 */
	private $js_loaded = false;

	/**
	 * Load the step transformer.
	 *
	 * This will place the required scripts into the document's head section.
	 */
	function load() {
		if (!$this->js_loaded) {
			$module = new module('com_testimonials', 'testimonials', 'head');
			$module->render();
			$this->js_loaded = true;
		}
	}
	
	/**
	 * Creates and attaches a module which lists testimonials.
	 * 
	 * @param $type string A string indicating the type of testimonial to retrieve. Pending by default.
	 * @return module The module.
	 */
	public function list_testimonials($type = 'pending') {
		global $pines;

		$module = new module('com_testimonials', 'testimonial/list', 'content');
		$module->testimonial_type = $type; 
		return $module;
	}
	
	/**
	 * Creates and attaches a module which lists reviews.
	 * 
	 * @param $type string A string indicating the type of review to retrieve. Pending by default.
	 * @return module The module.
	 */
	public function list_reviews($type = 'pending') {
		global $pines;

		$module = new module('com_testimonials', 'testimonial/list_reviews', 'content');
		$module->testimonial_type = $type; 
		return $module;
	}
	
	/**
	 * Creates and attaches a module which provides help with the testimonial component.
	 * @return module The module.
	 */
	public function print_help() {
		global $pines;
		$module = new module('com_testimonials', 'help/help', 'content');
		return $module;
	}
	
	/**
	 * Gets Testimonials.
	 * There are two types of testimonials
	 * 1. Testimonials that are approved and denied about a business or main group - that can appear on the homepage for example
	 * 2. Reviews on specific entities/names of things/items/products etc
	 * There are two ways to view testimonials 
	 * 1. A carousel of sliding testimonials, showing 1 at a time (useful for test. type 1)
	 * 2. An "infinite" list of testimonials, showing many and loading them x number at a time. Useful for test. 2)
	 * There are two ways of obtaining data for testimonials
	 * 1. Using the individual method, you get an author, testimonial, rating, date and you show it like in type 1 or 2 in method 1 or 2
	 * 2. Using the aggregate method, you get the average rating, the number of votes
	 * Ideas for expansion of component
	 * 1. Create a third way to view testimonials, and a third type of testimonial
	 * 2. The way to view is a "top X num of highest rated entities [products]". Like highest rated products
	 * 3. The type could be "highest_rated", You pass it an entity_class
	 * 4. The function returns the entity_guids, rating, & num of votes
	 * 5. You use that data to factory the guids, obtain pictures, names, descriptions, 
	 * create a box of highest rated products, make links to the actual pages and see the reviews.
	 * 
	 * @param $type string A string indicating the type of testimonial data to retrieve. individual | aggregate
	 * @param $reverse boolean True if you want the most recent entities
	 * @param $limit int A value representing the maximum number of entities to pull.
	 * @param $offset int A value representing the number of entities to offset the pull from.
	 * @param $tags array of additional tags that should be searched. Approved and Share testimonials are default tags. Useful for aggregate; ie electronics
	 * @param $entity_guid int The guid of a certain entity that the testimonial correlates to. ie A review for a product.
	 * @param $entity_class string A string representing the entity's class, useful for pulling aggregate data (ie highest rated products)
	 * @param $name string A string representing the name of the reviewed item, in case there is no guid - a specific picture name for example
	 * @param $rated_off boolean Whether or not to require ratings. When false, the rated tag is kept.
	 * 
	 * @return module The module.
	 */
	public function get_testimonials($type = 'individual', $reverse = true, $limit = 20, $offset = 0, $tags = array('approved', 'share'), $entity_guid = null, $entity_class = null, $name = null, $rated_off = false) {
		global $pines;

		$tag_args = array('com_testimonials', 'testimonial', 'rated'); // I always want rated ones.
		if ($rated_off)
			$tag_args = array_diff($tag_args, array('rated'));
		$done = false;
		
		if (!empty($entity_guid)) {
			$tag_args[] = $entity_guid;
			$done = true; // done because this is very specific to a particular product
		}
		
		if (isset($tags) && !$done) {
			foreach ($tags as $cur_tag) {
				// replace spaces with - because they were saved that way
				$formatted_tag = preg_replace('/ /', '-', $cur_tag);
				$tag_args[] = $formatted_tag;
			}
		}
		
		if (isset($entity_class) && !$done) {
			$tag_args[] = $entity_class;
		}
		
		if (isset($name) && !$done) {
			$formatted_name = preg_replace('/ /', '-', $name);
			$tag_args[] = $formatted_name;
		}
		
		// Get the entities based on all the tags and settings
		$testimonials = $pines->entity_manager->get_entities(
			array('class' => com_testimonials_testimonial, 'reverse' => $reverse, 'limit' => $limit, 'offset' => $offset),
			array('&',
				'tag' => array_unique($tag_args)
			)
		);
		
		// Now Compose the results according to aggregate or not
		if ($type == 'aggregate') {
			// DO NOT add stupid tags to this to skew the results
			//		Example stupid tags: approved, shared (because these do not represent total votes)
			//		NOT STUPID if you WANT to do that, to remove spam from being counted.
			// Get aggregate testmionial data
			// - count all testimonials by getting entities with tag testimonial 
			//		(some aren't approved and some aren't shared, but these represent total votes/rates
			// - from all these, get just the rating
			//		(going to use the rating to get average rating)
			// - do maths - divide by number of raters, and there is the average.
			$testimonials = $pines->entity_manager->get_entities(
					array('class' => com_testimonials_testimonial),
					array('&',
						'tag' => array_unique($tag_args)
						)
					);

			$votes = count($testimonials);
			$rating = 0;
			foreach($testimonials as $cur_testimonial) {
				$rating += $cur_testimonial->rating;
			}
			$average = $rating / $votes;

			$result = array('votes' => $votes, 'average' => $average);
		} elseif ($type == 'individual') {	
			$reviews = array();
			foreach ($testimonials as $cur_testimonial) {
				$review = array();
				$review['author'] = $cur_testimonial->author;
				$review['date'] = format_date($cur_testimonial->date, 'date_short');
				$review['timeago'] =  date("c", $cur_testimonial->date);
				$review['testimonial'] = (!empty($cur_testimonial->quotefeedback)) ?  htmlspecialchars($cur_testimonial->quotefeedback) : htmlspecialchars($cur_testimonial->feedback);
				$review['rating'] = $cur_testimonial->rating;
				$review['tags'] = json_encode($cur_testimonial->tags);
				if (gatekeeper('com_testimonials/showentityhelp')) {
					$review['guid'] = $cur_testimonial->guid;
					$review['customer_name'] = $cur_testimonial->customer->name_first;
				}
				$reviews[] = $review;
			}

			$result = $reviews;
		}
		
		if ( empty($testimonials) )
			$result = 'No testimonials found.';

		return $result;
	}
	
	

}

?>