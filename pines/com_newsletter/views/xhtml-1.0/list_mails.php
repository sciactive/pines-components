<?php
/**
 * Lists mailings.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Mails';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_newsletter', 'new'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_newsletter', 'edit', array('mail_id' => '__title__')); ?>'},
				{type: 'button', text: 'Send', extra_class: 'icon picon_16x16_actions_mail-message-new', url: '<?php echo pines_url('com_newsletter', 'sendprep', array('mail_id' => '__title__')); ?>'},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_newsletter', 'delete', array('mail_id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'mails',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_newsletter/list_mails", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#mail_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="mail_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Subject</th>
			<th>Attachments</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->mails as $mail) { ?>
		<tr title="<?php echo $mail->guid; ?>">
			<td><?php echo $mail->name; ?></td>
			<td><?php echo $mail->subject; ?></td>
			<td><?php echo (is_array($mail->attachments) ? implode(', ', $mail->attachments) : ''); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>