function verify_form(form) {
	var target_form = document.forms[form];
	if (target_form.password.value != target_form.password2.value) {
		alert('Your passwords do not match!');
		return false;
	} else {
		return true;
	}
}