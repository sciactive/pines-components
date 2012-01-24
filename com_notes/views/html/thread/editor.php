<?php
/**
 * Provides a thread editor for an entity.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Attached Notes';
$pines->icons->load();
?>
<div id="p_muid_notes">
	<?php if (!isset($this->entity->guid)) { ?>
	Notes will be available once this page is saved.
	<?php } else { ?>
	<style type="text/css" scoped="scoped">
		/* <![CDATA[ */
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
			margin: .2em .2em 0;
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
		/* ]]> */
	</style>
	<script type="text/javascript">
		// <![CDATA[
		function p_muid_load_threads() {
			var thread_box = $("#p_muid_current_threads");
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
						thread_box.append($("<div class=\"show_more\"></div>").append($("<a href=\"javascript:void(0);\">... Show all threads.</a>").click(function(){
							thread_box.empty();
							$.each(data, function(i, thread){
								thread_box.append(p_muid_format_thread(thread));
							});
							thread_box.find("textarea").change();
						})));
					}
					$.each(threads, function(i, thread){
						thread_box.append(p_muid_format_thread(thread));
					});
					thread_box.find("textarea").change();
				}
			});
		}

		function p_muid_format_thread(thread) {
			var html = $("<div class=\"thread ui-widget-content\"></div>").data("thread", thread);
			html.append("<div class=\"ui-widget-header\"><div class=\"privacy ui-icon "+(thread.privacy == "everyone" ? "ui-icon-unlocked" : (thread.privacy == "my-group" ? "ui-icon-locked" : "ui-icon-person"))+"\" title=\""+(thread.privacy == "everyone" ? "This thread is viewable to everyone." : (thread.privacy == "my-group" ? "This thread is viewable to the author's group." : "This thread is only viewable to the author."))+"\"></div>\n\
			<div class=\"date\">"+pines.safe(thread.date)+"</div>\n\
			<div class=\"user\">"+pines.safe(thread.user)+"</div></div>");
			var note_div = $("<div class=\"notes\"></div>");
			var notes = thread.notes;
			if (notes.length > 4) {
				// Show the first comment.
				note_div.append(p_muid_format_note(notes[0]));
				// Then the last two after the link.
				notes = notes.slice(-2);
				// A link to show all the notes.
				note_div.append($("<div class=\"show_more\"></div>").append($("<a href=\"javascript:void(0);\">... Show entire thread.</a>").click(function(){
					note_div.empty();
					$.each(thread.notes, function(i, note){
						note_div.append(p_muid_format_note(note));
					});
				})));
			}
			$.each(notes, function(i, note){
				note_div.append(p_muid_format_note(note));
			});
			html.append(note_div).append($("<div class=\"reply-box\"><textarea class=\"ui-widget-content\" rows=\"1\" cols=\"12\" style=\"font-style: italic;\">Reply</textarea></div>"))
			return html;
		}

		function p_muid_format_note(note) {
			var html = $("<div class=\"note\"></div>");
			html.append("<div class=\"note-text\">"+pines.safe(note.text).replace(/\n/g, "<br />")+"</div>\n\
			<div class=\"footer\">- <strong>"+pines.safe(note.user)+"</strong> on <span title=\""+pines.safe(note.time)+"\">"+pines.safe(note.date)+"</span></div>");
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
				var textarea = $(this);
				var thread = textarea.closest(".thread").data("thread");
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
						textarea.attr("disabled", "disabled").addClass("ui-state-disabled");
						thread_box.addClass("picon picon-throbber");
					},
					complete: function(){
						textarea.removeAttr("disabled").removeClass("ui-state-disabled");
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
		// ]]>
	</script>
	<div id="p_muid_current_threads">Loading...</div>
	<?php if (gatekeeper('com_notes/newthread')) { ?>
	<hr />
	<script type="text/javascript">
		// <![CDATA[
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
				submit_button.click();
			});
			if (new_thread.prop("scrollHeight")) {
				new_thread.css("overflow", "hidden").bind("keydown keyup change", function(){
					new_thread.height(1).height(new_thread.prop("scrollHeight"));
				});
			}
			// Thread privacy.
			var detail_hider_timer;
			var privacy_button = $("#p_muid_new_thread_privacy").data("thread-privacy", "everyone").button({
				text: false,
				icons: {primary: "ui-icon-unlocked"}
			}).toggle(function(){
				privacy_button.data("thread-privacy", "my-group").button("option", {"label": "My Group", "icons": {primary: "ui-icon-locked"}});
			}, function(){
				privacy_button.data("thread-privacy", "only-me").button("option", {"label": "Only Me", "icons": {primary: "ui-icon-person"}});
			}, function(){
				privacy_button.data("thread-privacy", "everyone").button("option", {"label": "Everyone", "icons": {primary: "ui-icon-unlocked"}});
			}).click(function(){
				var details = $("#p_muid_thread_privacy_details").children().hide()
				.filter("."+privacy_button.data("thread-privacy")).show().parent();
				if (detail_hider_timer)
					clearTimeout(detail_hider_timer);
				details.stop(true, true);
				if (details.is(":not(:visible)"))
					details.slideDown("fast");
				detail_hider_timer = setTimeout(function(){
					details.slideUp("fast");
				}, 2000);
			});
			// Save the new thread.
			var submit_button = $("#p_muid_new_thread_submit").click(function(){
				var text = new_thread.val();
				var privacy = privacy_button.data("thread-privacy");
				if (text == "" || new_thread.css("font-style") == "italic") {
					alert("Please enter a note first.");
					return;
				}
				$.ajax({
					url: <?php echo json_encode(pines_url('com_notes', 'thread/start')); ?>,
					type: "POST",
					dataType: "json",
					data: {"text": text, "privacy": privacy, "id": <?php echo json_encode((int) $this->entity->guid); ?>},
					beforeSend: function(){
						new_thread.attr("disabled", "disabled").addClass("ui-state-disabled");
						thread_buttons.hide();
						$("#p_muid_thread_throbber").show();
					},
					complete: function(){
						new_thread.removeAttr("disabled").removeClass("ui-state-disabled");
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
			var thread_buttons = $("#p_muid_new_thread_buttons").buttonset();
		});
		// ]]>
	</script>
	<div style="width: 100%;">
		<textarea class="ui-widget-content" id="p_muid_new_thread" rows="1" cols="12" style="width: 100%; padding: 0; margin: .5em 0 .1em; font-style: italic;">New Thread</textarea>
		<div id="p_muid_new_thread_buttons" style="text-align: right; font-size: .8em;">
			<button id="p_muid_new_thread_privacy" class="ui-state-default ui-corner-all">Everyone</button>
			<button id="p_muid_new_thread_submit" class="ui-state-default ui-corner-all">Save</button>
		</div>
		<div id="p_muid_thread_throbber" class="picon picon-throbber" style="display: none; height: 16px; background-repeat: no-repeat; background-position: top right;"></div>
		<div id="p_muid_thread_privacy_details" style="display: none;">
			<div class="only-me">Only you can see this thread.</div>
			<div class="my-group">Anyone in your group can see this thread.</div>
			<div class="everyone">Anyone can see this thread.</div>
		</div>
	</div>
	<?php } } ?>
</div>