<?php
/**
 * Displays the results of an entity manager benchmark.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Entity Manager Benchmark';
$last_time = $this->time_start;
?>
<p>This entity manager benchmark will create, retrieve and delete 100,000
entities and display the timing results here.</p>
<?php if ($this->error) { ?>
<p>Error: Either there is no entity manager installed, or it hasn't
registered itself as the system's entity manager! Benchmark cannot continue!</p>
<?php } else { ?>
<div style="font-family: monospace; font-size: .9em; margin-left: 35px; margin-bottom: 6px; margin-top: 6px;">Test is starting...
<ol><?php foreach ($this->tests as $cur_test) { ?>
<li style="white-space: pre;"><?php echo str_pad($cur_test[2], 48, ' '); ?><? echo ($cur_test[0]) ? '<span style="color: green;">[PASS]</span>' : '<span style="color: red;">[FAIL]</span>'; ?> <?php printf('%5.5f', $cur_test[1] - $last_time); ?>s</li>
<?php $last_time = $cur_test[1];
} ?>
</ol>
The benchmark is now complete. Run time was <?php echo $this->time_end - $this->time_start; ?>s.
</div>
<?php } ?>