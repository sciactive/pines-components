$(function(){
	var notice;
	$.ajax({
		url: pines.com_su_loginpage_url,
		type: "GET",
		dataType: "html",
		beforeSend: function(){
			notice = pines.alert("Loading login page...", "Switch User", "icon picon_16x16_animations_throbber", {pnotify_hide: false, pnotify_history: false});
		},
		error: function(XMLHttpRequest, textStatus){
			notice.pnotify_remove();
			pines.error("An error occured while trying to load login page:\n"+XMLHttpRequest.status+": "+textStatus);
		},
		success: function(data){
			notice.pnotify({
				pnotify_title: "Switch User",
				pnotify_text: data,
				pnotify_notice_icon: "icon picon_16x16_status_dialog-password",
				pnotify_hide: false,
				pnotify_insert_brs: false
			}).find("input").eq(0).focus();
		}
	});
});