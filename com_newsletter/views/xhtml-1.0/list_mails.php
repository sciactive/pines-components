<?php
/**
 * Lists mailings.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
    // <![CDATA[
    var mail_grid;
    var mail_grid_state;

    $(document).ready(function(){
        mail_grid = $("#mail_grid").pgrid({
            pgrid_toolbar: true,
            pgrid_toolbar_contents: [
                {type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo $config->template->url('com_newsletter', 'new'); ?>'},
                {type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo $config->template->url('com_newsletter', 'edit', array('mail_id' => '#title#')); ?>'},
                {type: 'button', text: 'Send', extra_class: 'icon picon_16x16_actions_mail-message-new', url: '<?php echo $config->template->url('com_newsletter', 'sendprep', array('mail_id' => '#title#')); ?>'},
                {type: 'separator'},
                {type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo $config->template->url('com_newsletter', 'delete', array('mail_id' => '#title#')); ?>', delimiter: ','},
                {type: 'separator'},
                {type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
                {type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true}
            ],
            pgrid_sort_col: 'col_1',
            pgrid_sort_ord: 'asc'
        });
    });

    function save_state() {
        mail_grid_state = mail_grid.export_state();
    }

    function load_state() {
         mail_grid.import_state(mail_grid_state);
    }
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