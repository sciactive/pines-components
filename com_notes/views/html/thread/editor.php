<?php
/**
 * Provides a thread editor for an entity.
 *
 * @package Components\notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Attached Notes';
if (isset($this->entity->guid))
	$this->note = htmlspecialchars($this->entity->info('name'));
$pines->icons->load();
?>
<div id="p_muid_notes">
	<?php if (!isset($this->entity->guid)) { ?>
	Notes will be available once the <?php echo htmlspecialchars($this->entity->info('type')); ?> is saved.
	<?php } else { ?>
	<style type="text/css" scoped="scoped">
		#p_muid_current_threads.picon {
			background-repeat: no-repeat;
			background-position: top right;
			min-height: 16px;
		}
		#p_muid_current_threads .show_more {
			text-align: right;
		}
		#p_muid_current_threads .thread {
			margin-bottom: .2em;
		}
		#p_muid_current_threads .thread .ui-widget-header {
			font-size: .9em;
			margin: 0;
			border-top-width: 0;
			border-left-width: 0;
			border-right-width: 0;
		}
		#p_muid_current_threads .thread .ui-widget-header .privacy {
			float: right;
			height: 16px;
			width: 16px;
		}
		#p_muid_current_threads .thread .ui-widget-header .date {
			text-align: right;
			float: right;
		}
		#p_muid_current_threads .thread .show_more {
			margin: 0 .2em;
		}
		#p_muid_current_threads .thread .note {
			margin: 0 .2em;
			overflow: auto;
			max-height: 150px;
		}
		#p_muid_current_threads .thread .note .note-text {
			padding: .2em;
		}
		#p_muid_current_threads .thread .note .footer {
			text-align: right;
			font-size: .6em;
		}
		#p_muid_current_threads .thread .reply-box {
			margin: 0 .2em .2em;
		}
		#p_muid_current_threads .thread .reply-box textarea {
			width: 100%;
			padding: 0;
		}
	</style>
	<script type="text/javascript">
		function p_muid_load_threads() {
			var thread_box = $("#p_muid_current_threads");
			thread_box.add_item = function(item){
				<?php if ($pines->config->com_notes->editor_sort_order == 'asc') { ?>
				return thread_box.append(item);
				<?php } else { ?>
				return thread_box.prepend(item);
				<?php } ?>
			};
			// Load current threads.
			$.ajax({
				url: <?php echo json_encode(pines_url('com_notes', 'thread/get')); ?>,
				type: "GET",
				dataType: "json",
				data: {"id": <?php echo json_encode((int) $this->entity->guid); ?>},
				beforeSend: function(){
					thread_box.addClass("picon picon-throbber");
				},
				complete: function(){
					thread_box.removeClass("picon picon-throbber");
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to get the attached notes:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						pines.error("An error occured while trying to get the attached notes.");
						return;
					}
					thread_box.empty();
					var threads = data;
					if (threads.length > 5) {
						// Show only the 5 latest modified threads.
						threads = threads.slice(-5);
						// A link to show all the threads.
						thread_box.add_item($('<div class="show_more"></div>').append($('<a href="javascript:void(0);">&hellip; Show all threads.</a>').click(function(){
							thread_box.empty();
							$.each(data, function(i, thread){
								thread_box.add_item(p_muid_format_thread(thread));
							});
							thread_box.find("textarea").change();
						})));
					}
					$.each(threads, function(i, thread){
						thread_box.add_item(p_muid_format_thread(thread));
					});
					thread_box.find("textarea").change();
				}
			});
		}

		function p_muid_format_thread(thread) {
			var html = $('<div class="thread ui-widget-content"></div>').data("thread", thread);
			html.append('<div class="ui-widget-header"><div class="privacy"><i class="'+(thread.privacy == "everyone" ? "icon-globe" : (thread.privacy == "my-group" ? "icon-lock" : "icon-user"))+'" title="'+(thread.privacy == "everyone" ? "This thread is viewable to everyone." : (thread.privacy == "my-group" ? "This thread is viewable to the author's group." : "This thread is only viewable to the author."))+'"></i></div>\n\
			<div class="date">'+pines.safe(thread.date)+'</div>\n\
			<div class="user">'+pines.safe(thread.user)+'</div></div>');
			var note_div = $('<div class="notes"></div>'),
				notes = thread.notes;
			if (notes.length > 4) {
				// Show the first comment.
				note_div.append(p_muid_format_note(notes[0]));
				// Then the last two after the link.
				notes = notes.slice(-2);
				// A link to show all the notes.
				note_div.append($('<div class="show_more"></div>').append($('<a href="javascript:void(0);">&hellip; Show entire thread.</a>').click(function(){
					note_div.empty();
					$.each(thread.notes, function(i, note){
						note_div.append(p_muid_format_note(note));
					});
				})));
			}
			$.each(notes, function(i, note){
				note_div.append(p_muid_format_note(note));
			});
			html.append(note_div).append('<div class="reply-box"><textarea class="ui-widget-content" rows="1" cols="12" style="font-style: italic;">Reply</textarea></div>')
			return html;
		}

		function p_muid_format_note(note) {
			var html = $('<div class="note"></div>'),
				text = pines.safe(note.text).replace(/\n/g, '<br />')
				// Match URLs with a protocol. (http://example.com/wow)
				.replace(
					/\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%]))(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/gi,
					'<a href="$1" target="_blank">$1<i class="icon-external-link" style="text-decoration: none !important; font-size: 0.85em; margin-left: 3px;"></i></a>'
				)
				// Match things that look like URLs. (example.co.uk/wow) (No lookbehind support. Grr.)
				.replace(
					/(^|[^\/\w.@])\b((?:www\d{0,3}[.]|[a-z0-9.\-]{2,}[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/gi,
					'$1<a href="http://$2" target="_blank">$2<i class="icon-external-link" style="text-decoration: none !important; font-size: 0.85em; margin-left: 3px;"></i></a>'
				)
				// Match simple domain names. (example.com) (I didn't use the above one, because things might look like TLDs, so I used only common ones for this.)
				.replace(
					/(^|[^\/\w.@])\b([a-z0-9.\-]{2,}[.](com|net|org|edu|gov|mil|info|biz))\b(?!\/)/gi,
					'$1<a href="http://$2" target="_blank">$2<i class="icon-external-link" style="text-decoration: none !important; font-size: 0.85em; margin-left: 3px;"></i></a>'
				)
				// Match email addresses. (me@example.com)
				.replace(
					/\b([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/gi,
					'<a href="mailto://$1" target="_blank">$1<i class="icon-envelope" style="text-decoration: none !important; font-size: 0.85em; margin-left: 3px;"></i></a>'
				);
			html.append('<div class="note-text">'+text+'</div>\n\
			<div class="footer">- <strong>'+pines.safe(note.user)+'</strong> on <span title="'+pines.safe(note.time)+'">'+pines.safe(note.date)+'</span></div>');
			return html;
		}

		pines(function(){
			var thread_box = $("#p_muid_current_threads").delegate("textarea", "keydown keyup change", function(){
				var textarea = $(this);
				if (textarea.prop("scrollHeight"))
					textarea.css("overflow", "hidden").height(1).height(textarea.prop("scrollHeight"));
			}).delegate("textarea", "focus", function(){
				var textarea = $(this);
				if (textarea.css("font-style") == "italic")
					textarea.val("").css("font-style", "normal");
			}).delegate("textarea", "blur", function(){
				var textarea = $(this);
				if (textarea.val() == "")
					textarea.val("Reply").css("font-style", "italic");
			}).delegate("textarea", "keypress", function(e){
				if (e.keyCode != 13 || e.shiftKey)
					return;
				// Submit a reply.
				var textarea = $(this),
					thread = textarea.closest(".thread").data("thread");
				if (!thread) {
					alert("Thread location is invalid.");
					return;
				}
				var text = textarea.val();
				if (text == "" || textarea.css("font-style") == "italic") {
					alert("Please enter a reply first.");
					return;
				}
				$.ajax({
					url: <?php echo json_encode(pines_url('com_notes', 'thread/continue')); ?>,
					type: "POST",
					dataType: "json",
					data: {"text": text, "id": thread.guid},
					beforeSend: function(){
						textarea.attr("disabled", "disabled");
						thread_box.addClass("picon picon-throbber");
					},
					complete: function(){
						textarea.removeAttr("disabled");
						thread_box.removeClass("picon picon-throbber");
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to save the note:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							alert("An error occured. Please try again.");
							return;
						}
						textarea.val("Reply").css("font-style", "italic").blur();
						p_muid_load_threads();
					}
				});
			});
			p_muid_load_threads();
		});
	</script>
	<div id="p_muid_current_threads">Loading...</div>
	<?php if (gatekeeper('com_notes/newthread')) { ?>
	<hr />
	<script type="text/javascript">
		pines(function(){
			// New thread box.
			var new_thread = $("#p_muid_new_thread");
			new_thread.focus(function(){
				if (new_thread.css("font-style") == "italic")
					new_thread.val("").css("font-style", "normal");
			}).blur(function(){
				if (new_thread.val() == "")
					new_thread.val("New Thread").css("font-style", "italic");
			}).keypress(function(e){
				if (e.keyCode != 13 || e.shiftKey)
					return;
				e.preventDefault();
				submit_button.click();
			});
			if (new_thread.prop("scrollHeight")) {
				new_thread.css("overflow", "hidden").bind("keydown keyup change", function(){
					new_thread.height(1).height(new_thread.prop("scrollHeight"));
				});
			}
			// Thread privacy.
			var privacy_button = $("#p_muid_new_thread_privacy").data("thread-privacy", "everyone");
			// Save the new thread.
			var submit_button = $("#p_muid_new_thread_submit").click(function(){
				var text = new_thread.val(),
					privacy = privacy_button.data("thread-privacy");
				if (text.match(/^\s*$/) || new_thread.css("font-style") == "italic") {
					alert("Please enter a note first.");
					return;
				}
				$.ajax({
					url: <?php echo json_encode(pines_url('com_notes', 'thread/start')); ?>,
					type: "POST",
					dataType: "json",
					data: {"text": text, "privacy": privacy, "id": <?php echo json_encode((int) $this->entity->guid); ?>, "context": <?php echo json_encode(str_replace('hook_override_', '', get_class($this->entity))); ?>},
					beforeSend: function(){
						new_thread.attr("disabled", "disabled");
						thread_buttons.hide();
						$("#p_muid_thread_throbber").show();
					},
					complete: function(){
						new_thread.removeAttr("disabled");
						$("#p_muid_thread_throbber").hide();
						thread_buttons.show();
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to save the note:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							alert("An error occured. Please try again.");
							return;
						}
						new_thread.val("New Thread").css("font-style", "italic").blur();
						p_muid_load_threads();
					}
				});
			});
			var thread_buttons = $("#p_muid_new_thread_buttons");
		});
	</script>
	<div style="width: 100%;" class="clearfix">
		<textarea id="p_muid_new_thread" rows="1" cols="12" style="width: 100%; padding: 0; margin: .5em 0 .1em; font-style: italic;">New Thread</textarea>
		<div id="p_muid_new_thread_buttons" class="btn-group dropup pull-right" style="font-size: .8em;">
			<button id="p_muid_new_thread_submit" class="btn">Save</button>
			<button id="p_muid_new_thread_privacy" class="btn dropdown-toggle" data-toggle="dropdown" title="Everyone."><i class="icon-globe"></i></button>
			<ul class="dropdown-menu pull-right">
				<li><a href="javascript:void(0);" onclick="$('#p_muid_new_thread_privacy').data('thread-privacy', 'everyone').html('&lt;i class=&quot;icon-globe&quot;&gt;&lt;/i&gt;').attr('title', 'Everyone.');"><i class="icon-globe"></i> Share with everyone.</a></li>
				<li><a href="javascript:void(0);" onclick="$('#p_muid_new_thread_privacy').data('thread-privacy', 'my-group').html('&lt;i class=&quot;icon-lock&quot;&gt;&lt;/i&gt;').attr('title', 'My group.');"><i class="icon-lock"></i> Share with my group.</a></li>
				<li><a href="javascript:void(0);" onclick="$('#p_muid_new_thread_privacy').data('thread-privacy', 'only-me').html('&lt;i class=&quot;icon-user&quot;&gt;&lt;/i&gt;').attr('title', 'Only me.');"><i class="icon-user"></i> Keep private to me.</a></li>
			</ul>
		</div>
		<div id="p_muid_thread_throbber" class="picon picon-throbber" style="display: none; height: 16px; background-repeat: no-repeat; background-position: top right;"></div>
	</div>
	<?php } } ?>
</div>