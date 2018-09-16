$(function() {
	
});

function userlogin() {
	var username = $.trim($('#username').val());
	var password = $.trim($('#password').val());
	var remember = $('#remember:checked').size() < 1 ? 'OFF' : 'ON';
	if (username == '') {
		$('#username').trigger('focus');
		return false;
	}
	else if (username.length < 4 || username.length > 50) {
		$('#username').trigger('focus');
		return false;
	}
	if (password == '') {
		$('#password').trigger('focus');
		return false;
	}
	$.commlib.ajax({
		'url': '/user/login',
		'type': 'post',
		'datatype': 'json',
		'data': {
			username: username,
			password: password,
			remember: remember
		},
		'success': function(rp) {
			if (rp.code != 0) {
				return $.messager.alert('提示信息', rp.info, 'error');
			}
			window.location.href = '/';
		}
	});
}

function formclear() {
	
}
