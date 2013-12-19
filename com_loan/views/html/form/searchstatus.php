<?php
/**
 * View for selecting a status to search loans by.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	#p_muid_form {
		padding-top:20px;
	}
	#p_muid_form .item {
		border-bottom: 1px dotted #CCCCCC;
		padding: 5px;
		margin-bottom: 2px;
		cursor: pointer;
		transition: background 125ms linear 0s;
	}
	#p_muid_form .item:not(.btn-success):hover, #p_muid_form .form-btn:hover {
		background: #ddd;
		color: #777;
		text-shadow: none;
	}
	#p_muid_form .name {
		line-height: 30px;
		font-weight:bold;
	}
	#p_muid_form .form-btn {
		width: 49%;
		background: #eee;
		padding: 10px 0;
		border-bottom: 4px solid #ccc;
		cursor: pointer;
		text-transform: uppercase;
		font-weight:bold;
		color: #777;
		transition: background 125ms linear 0s;
	}
	#p_muid_form .form-btn-container {
		text-align:center;
		margin-bottom: 5px;
	}
</style>
<script type='text/javascript'>
	pines(function(){
		var cur_state = <?php echo (isset($this->cur_state) ? json_encode($this->cur_state) : '{}');?>;
		var form = $('#p_muid_form');
		var items = form.find('.item');
		var select_none = form.find('.select-none');
		var select_all = form.find('.select-all');
		var status_tags = form.find('[name=status_tags]');
		
		
		var toggle_on = function(items){
			items.each(function(){
				$(this).addClass('btn-success');
			});
		};
		var toggle_off = function(items){
			items.each(function(){
				$(this).removeClass('btn-success');
			});
		};
		var create_tag_array = function(){
			var tags = [];
			items.each(function(){
				var cur_item = $(this);
				if (cur_item.hasClass('btn-success'))
					tags.push(cur_item.attr('data-value'));
			});
			status_tags.val(tags.join(","));
		}
		
		items.click(function(){
			var item = $(this);
			if (item.hasClass('btn-success')) {
				toggle_off(item);
			} else {
				toggle_on(item);
			}
			create_tag_array();
		});
		
		select_none.click(function(){
			toggle_off(items);
			create_tag_array();
		});
		select_all.click(function(){
			toggle_on(items);
			create_tag_array();
		});
		
		create_tag_array();
	});
</script>
<div class="pf-form" id="p_muid_form">
	<div class="form-btn-container clearfix">
		<div class="form-btn select-none pull-left"><i class="icon-ban-circle"></i> Select None</div>
		<div class="form-btn select-all pull-right"><i class="icon-copy"></i> Select All</div>
	</div>
	<div class="item clearfix <?php echo (in_array('active',$this->cur_state->status_tags)) ? 'btn-success' : ''; ?>" data-value="active">
		<div class="name row-fluid">
			<div class="span8">
				Active
			</div>
            <div class="results text-success span4"></div>
		</div>
	</div>
	<div class="item clearfix <?php echo (in_array('paidoff', $this->cur_state->status_tags)) ? 'btn-success' : ''; ?>" data-value="paidoff">
		<div class="name row-fluid">
			<div class="span8">
				Paid in Full
			</div>
            <div class="results text-success span4"></div>
		</div>
	</div>
	<div class="item clearfix <?php echo (in_array('writeoff', $this->cur_state->status_tags)) ? 'btn-success' : ''; ?>" data-value="writeoff">
		<div class="name row-fluid">
			<div class="span8">
				Written Off
			</div>
            <div class="results text-success span4"></div>
		</div>
	</div>
	<div class="item clearfix <?php echo (in_array('cancelled', $this->cur_state->status_tags)) ? 'btn-success' : ''; ?>" data-value="cancelled">
		<div class="name row-fluid">
			<div class="span8">
				Cancelled
			</div>
            <div class="results text-success span4"></div>
		</div>
	</div>
	<div class="item clearfix <?php echo (in_array('sold', $this->cur_state->status_tags)) ? 'btn-success' : ''; ?>" data-value="sold">
		<div class="name row-fluid">
			<div class="span8">
				Sold
			</div>
            <div class="results text-success span4"></div>
		</div>
	</div>
	<input name="status_tags" type="hidden"/>
</div>