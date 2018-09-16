var Pro = (function() {
	this.loader = function(param,success,error) {
		var self = this;
		var opts = $(self).datagrid('options');
		if (!opts.url) return false;
		$.ajax({
			type: opts.method,
			url: opts.url,
			data: param,
			dataType: "json",
			success: function(rp) {
				if (rp.code != 0) {
					success([]);
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				var i = rp.data.rows.length;
				for ( ; i < param.rows; i++) {
					rp.data.rows.push({
						id: '',
						username: '',
						realname: '',
						time: '',
						stat: ''
					});
				}
				success(rp.data);
			},
			error: function() {
				error.apply(this, arguments);
			}
		});
	};
	this.showForm = function(id) {
		if (!id) id = 0;
		$.commlib.ajax({
			url: '/user/manage',
			data: {
				command: 'info',
				id: id
			},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('div#InfoForm').dialog({
					title: '账号设置',
					width: 350,
					closed: false,
					modal: true,
					buttons: [{
						text: '保存',
						iconCls: 'icon-ok',
						handler: Pro.saveform
					}, {
						text: '取消',
						iconCls: 'icon-cancel',
						handler: function() {
							$('div#InfoForm').dialog('close');
						}
					}],
					content: rp.data.formdata
				});
			}
		});
	};
	this.saveform = function() {
		var xform = document.forms['x-form'];
		var formdata = $(xform).serialize();
		if (xform.username.value == '') {
			return $.messager.alert('提示信息', '登录账号不能为空！', 'error');
		}
		$.commlib.ajax({
			url: '/user/manage',
			data: formdata,
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('div#InfoForm').dialog('close');
				$('#FormRow').datagrid('reload');
			}
		});
	};
	this.deleteRow = function(id) {
		$.messager.confirm('提示信息', '你确定要删除所选账号吗？<br /><span style="font-weight: bold; color: #FF0000;">注：删除操作不可恢复！</span>',
			function(r) {
				if (!r) return;
				$.commlib.ajax({
					url: '/user/manage',
					data: {
						command: 'del',
						id: id
					},
					success: function(rp) {
						if (rp.code != 0) {
							return $.messager.alert('提示信息', rp.info, 'error');
						}
						$.messager.alert('提示信息', '删除账号成功！', 'info');
						$('#FormRow').datagrid('reload');
					}
				});
			}
		);
	};
	this.chgPassword = function(id) {
		$('#PasswordForm').dialog({
			title: '重置账号密码',
			width: 250,
			height: 130,
			closed: false,
			cache: false,
			resizable: false,
			modal: true,
			buttons: [{
				handler: function() {
					var newpassword = $.trim($(':input#RenewPassword').val());
					if (newpassword == '') {
						return $.messager.alert('提示信息', '请输入新密码！', 'error');
					}
					$.commlib.ajax({
						url: '/user/manage',
						data: {
							command: 'passwdreset',
							id: id,
							password: newpassword
						},
						success: function(rp) {
							if (rp.code != 0) {
								return $.messager.alert('提示信息', rp.info, 'error');
							}
							$.messager.alert('提示信息', '重置密码成功！', 'info');
							$('#PasswordForm').dialog('close');
						},
						error: function() {}
					});
				},
				text: '保存',
				iconCls: 'icon-ok'
			}, {
				handler: function() {
					$('div#PasswordForm').dialog('close');
				},
				text: '取消',
				iconCls: 'icon-cancel'
			}],
			content: '<div style="margin: 15px auto;"><span>新密码：</span><input type="text" id="RenewPassword" value="" /><div>'
		});
	};
	this.onloaded = function() {
		$('a.my-link-tip').linkbutton({
			iconCls: 'icon-tip',
			plain: true
		});
		$('a.my-link-edit').linkbutton({
			iconCls: 'icon-edit',
			plain: true
		});
		$('a.my-link-remove').linkbutton({
			iconCls: 'icon-remove',
			plain: true
		});
		$('a.my-icon-stat-ok').linkbutton({
			iconCls: 'icon-ok',
			plain: true
		});
		$('a.my-icon-stat-no').linkbutton({
			iconCls: 'icon-no',
			plain: true
		});
	};
	this.staticon = function(t, val) {
		var icon = 'my-icon-stat-no';
		var data = val.split(':');
		if (data[0] & 1) icon = 'my-icon-stat-ok';
		return '<a class="' + icon + '" onclick="Pro.chg(this, \'' + t + '\', \'' + data[1] + '\')"></a>';
	};
	this.chg = function(o, t, id) {
		id = $.trim(id);
		$.commlib.ajax({
			url: '/user/manage',
			data: {
				command: 'chg',
				id:   id,
				flag: t
			},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				if (rp.data) {
					if (rp.data.icon == 'ok') {
						$('span.l-btn-empty', $(o)).removeClass('icon-no').addClass('icon-ok');
					} else if (rp.data.icon == 'no') {
						$('span.l-btn-empty', $(o)).removeClass('icon-ok').addClass('icon-no');
					}
				}
			}
		});
	};
	return this;
})(jQuery);
var RowFormat = {
	status: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return Pro.staticon('stat', val);
	},
	operat: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		var ophtml = '<a class="my-link-tip" onclick="Pro.chgPassword('+val+')">重置密码</a>';
		ophtml += '<a class="my-link-edit" onclick="Pro.showForm('+val+')">编辑</a>';
		ophtml += '<a class="my-link-remove" onclick="Pro.deleteRow('+val+')">删除</a>';
		return ophtml;
	}
};
$(function() {
	$('#FormRow').datagrid({
		url: '/user/manage',
		queryParams: {
			json_tag__: 'ON',
			command: 'list'
		},
		columns: [[
			{title: '账号', field: 'username', align: 'center', width: 250},
			{title: '姓名', field: 'realname', align: 'center', width: 135},
			{title: '时间', field: 'time', align: 'center', width: 150},
			{title: '状态', field: 'stat', align: 'center', width: 50, formatter: RowFormat.status},
			{title: '操作', field: 'id', align: 'center', formatter: RowFormat.operat}
		]],
		toolbar: [{
			iconCls: 'icon-add',
			handler: function() { Pro.showForm(0); }
		},'-',{
			iconCls: 'icon-help',
			handler: function(){
				$.messager.alert('关于……', '用户管理：<br />超级管理设置，账号启动禁用，密码初始化等！');
			}
		}],
		pageSize: 20,
		pagination: true,
		loader: Pro.loader,
		singleSelect: true,
		onLoadSuccess: Pro.onloaded
	});
});