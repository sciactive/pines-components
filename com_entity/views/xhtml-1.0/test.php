<?php
/**
 * Displays the results of an entity manager test.
 *
 * @package Pines
 * @subpackage com_entity
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<p>This entity manager tester will test the current entity manager for required
functionality. If the entity manager fails any of the tests, it is not
considered to be a compatible entity manager. Please note that this tester does
not test all aspects of an entity manager, and even if it passes, it may still
have bugs.</p>
<br />
<?php if ($this->error) { ?>
<br />Error: Either there is no entity manager installed, or it hasn't
registered itself as the system's entity manager! Test cannot continue!
<?php } else { ?>
<div style="font-family: monospace; font-size: 1.5em; margin-left: 35px;">Test is starting...
<ol>
<li style="white-space: pre;">Creating entity...                       <span style="color: <? echo ($this->tests[0]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[0]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Saving entity...                         <span style="color: <? echo ($this->tests[1]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[1]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Checking entity's has_tag method...      <span style="color: <? echo ($this->tests[2]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[2]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by GUID...             <span style="color: <? echo ($this->tests[3]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[3]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong GUID...                    <span style="color: <? echo ($this->tests[4]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[4]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by parent...           <span style="color: <? echo ($this->tests[5]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[5]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong parent...                  <span style="color: <? echo ($this->tests[6]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[6]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags...             <span style="color: <? echo ($this->tests[7]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[7]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong tags...                    <span style="color: <? echo ($this->tests[8]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[8]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags exclusively... <span style="color: <? echo ($this->tests[9]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[9]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong exclusive tags...          <span style="color: <? echo ($this->tests[10]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[10]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags inclusively... <span style="color: <? echo ($this->tests[11]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[11]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong inclusive tags...          <span style="color: <? echo ($this->tests[12]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[12]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by mixed tags...       <span style="color: <? echo ($this->tests[13]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[13]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong inclusive mixed tags...    <span style="color: <? echo ($this->tests[14]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[14]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong exclusive mixed tags...    <span style="color: <? echo ($this->tests[15]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[15]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by data...             <span style="color: <? echo ($this->tests[16]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[16]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong data...                    <span style="color: <? echo ($this->tests[17]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[17]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by data wildcards...   <span style="color: <? echo ($this->tests[18]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[18]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong data wildcards...          <span style="color: <? echo ($this->tests[19]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[19]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags and data...    <span style="color: <? echo ($this->tests[20]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[20]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong tags and right data...     <span style="color: <? echo ($this->tests[21]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[21]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing right tags and wrong data...     <span style="color: <? echo ($this->tests[22]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[22]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong tags and wrong data...     <span style="color: <? echo ($this->tests[23]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[23]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Deleting entity...                       <span style="color: <? echo ($this->tests[24]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[24]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Resaving entity...                       <span style="color: <? echo ($this->tests[25]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[25]) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Deleting entity by GUID...               <span style="color: <? echo ($this->tests[26]) ? 'green' : 'red'; ?>;">[<? echo ($this->tests[26]) ? 'PASS' : 'FAIL'; ?>]</span></li>
</ol>
The test is now complete. It took <?php echo $this->time; ?> second(s).
</div>
<?php } ?>