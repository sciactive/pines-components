pines(function(){
	var notice;
	$.ajax({
		url: pines.com_su_loginpage_url,
		type: "GET",
		dataType: "html",
		beforeSend: function(){
			notice = $.pnotify({
				pnotify_text: "Loading login page...",
				pnotify_title: "Switch User",
				pnotify_notice_icon: "picon picon-throbber",
				pnotify_hide: false,
				pnotify_history: false
			});
		},
		error: function(XMLHttpRequest, textStatus){
			notice.pnotify_remove();
			pines.error("An error occured while trying to load login page:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
		},
		success: function(data){
			notice.pnotify({
				pnotify_title: "Switch User",
				pnotify_text: data,
				pnotify_notice_icon: "picon picon-dialog-password",
				pnotify_insert_brs: false
			}).find("input").eq(0).focus();
		}
	});
});