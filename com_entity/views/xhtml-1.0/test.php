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
$this->title = 'Entity Manager Tester';
?>
<p>This entity manager tester will test the current entity manager for required
functionality. If the entity manager fails any of the tests, it is not
considered to be a compatible entity manager. Please note that this tester does
not test all aspects of an entity manager, and even if it passes, it may still
have bugs.</p>
<?php if ($this->error) { ?>
<p>Error: Either there is no entity manager installed, or it hasn't
registered itself as the system's entity manager! Test cannot continue!</p>
<?php } else { ?>
<div style="font-family: monospace; font-size: .9em; margin-left: 35px; margin-bottom: 6px; margin-top: 6px;">Test is starting...
<ol>
<li style="white-space: pre;">Creating entity...                       <span style="color: <? echo ($this->tests['create']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['create']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Saving entity...                         <span style="color: <? echo ($this->tests['save']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['save']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Checking entity's has_tag method...      <span style="color: <? echo ($this->tests['has_tag']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['has_tag']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by GUID...             <span style="color: <? echo ($this->tests['by_guid']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['by_guid']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong GUID...                    <span style="color: <? echo ($this->tests['wrong_guid']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wrong_guid']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by GUID and tags...    <span style="color: <? echo ($this->tests['guid_tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['guid_tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing GUID and wrong tags...           <span style="color: <? echo ($this->tests['guid_wr_tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['guid_wr_tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by parent...           <span style="color: <? echo ($this->tests['parent']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['parent']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong parent...                  <span style="color: <? echo ($this->tests['wr_parent']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_parent']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags...             <span style="color: <? echo ($this->tests['tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong tags...                    <span style="color: <? echo ($this->tests['wr_tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags exclusively... <span style="color: <? echo ($this->tests['tags_exc']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['tags_exc']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong exclusive tags...          <span style="color: <? echo ($this->tests['wr_tags_exc']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_tags_exc']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags inclusively... <span style="color: <? echo ($this->tests['tags_inc']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['tags_inc']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong inclusive tags...          <span style="color: <? echo ($this->tests['wr_tags_inc']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_tags_inc']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by mixed tags...       <span style="color: <? echo ($this->tests['mixed_tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['mixed_tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong inclusive mixed tags...    <span style="color: <? echo ($this->tests['wr_inc_mx_tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_inc_mx_tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong exclusive mixed tags...    <span style="color: <? echo ($this->tests['wr_exc_mx_tags']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_exc_mx_tags']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by data...             <span style="color: <? echo ($this->tests['data']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['data']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong data...                    <span style="color: <? echo ($this->tests['wr_data']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_data']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by data wildcards...   <span style="color: <? echo ($this->tests['wild']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wild']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong data wildcards...          <span style="color: <? echo ($this->tests['wr_wild']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_wild']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Retrieving entity by tags and data...    <span style="color: <? echo ($this->tests['tags_data']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['tags_data']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong tags and right data...     <span style="color: <? echo ($this->tests['wr_tags_data']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_tags_data']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing right tags and wrong data...     <span style="color: <? echo ($this->tests['tags_wr_data']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['tags_wr_data']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing wrong tags and wrong data...     <span style="color: <? echo ($this->tests['wr_tags_wr_data']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['wr_tags_wr_data']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing referenced entities...           <span style="color: <? echo ($this->tests['ref']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['ref']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Testing referenced entity arrays...      <span style="color: <? echo ($this->tests['ref_array']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['ref_array']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Deleting referenced entities...          <span style="color: <? echo ($this->tests['del_ref']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['del_ref']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Deleting entity...                       <span style="color: <? echo ($this->tests['del']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['del']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Resaving entity...                       <span style="color: <? echo ($this->tests['resave']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['resave']) ? 'PASS' : 'FAIL'; ?>]</span></li>
<li style="white-space: pre;">Deleting entity by GUID...               <span style="color: <? echo ($this->tests['del_guid']) ? 'green' : 'red'; ?>;">[<? echo ($this->tests['del_guid']) ? 'PASS' : 'FAIL'; ?>]</span></li>
</ol>
The test is now complete. It took <?php echo $this->time; ?> second(s).
</div>
<?php } ?>