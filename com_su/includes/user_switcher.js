pines(function(){
	var notice;
	$.ajax({
		url: pines.com_su_loginpage_url,
		type: "GET",
		dataType: "html",
		beforeSend: function(){
			notice = $.pnotify({
				text: "Loading login page...",
				title: "Switch User",
				icon: "picon picon-throbber",
				hide: false,
				history: false
			});
		},
		error: function(XMLHttpRequest, textStatus){
			notice.pnotify_remove();
			pines.error("An error occured while trying to load login page:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
		},
		success: function(data){
			notice.pnotify({
				title: "Switch User",
				text: data,
				icon: "picon picon-dialog-password",
				insert_brs: false
			}).find("input").eq(0).focus();
		}
	});
});