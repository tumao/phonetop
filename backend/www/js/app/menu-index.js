$(function() {
	var panel = $.commlib.main_get_size();
	var contain = $.commlib.contain_get_size();
	$('#MenuTree').treegrid({
		animate: true,
		fitColumns: true,
		url: $.commlib.json_uri_('/sysadmin/menu/tree'),
		idField:'id',
		treeField:'name',
		columns:[[
			{title:'菜单名称', field:'name', width: 200, editor:'text'},
			{field:'path', title:'访问路径', width:230, editor:'text'},
			{field:'show', title:'显示', width:60, align:'center'},
			{field:'icon', title:'图标', width:120, align:'center', editor:'text'},
			{field:'memo', title:'备注', width:200, align:'center', editor:'text'}
		]],
		onContextMenu: function(e, row){
			e.preventDefault();
			$(this).treegrid('select', row.id);
			$('#ConMenu').menu('show',{
				left: e.pageX,
				top: e.pageY
			});
		}
	});
	$('#MenuTree').treegrid('resize', {width: contain.width, height: panel.height-50});
});
var Menu = {
	editingId: 0,
	contextMenu: 
		'<a data-options="iconCls:\'icon-ok\',plain:true" title="" class="easyui-linkbutton easyui-tooltip tooltip-f l-btn l-btn-plain" href="#" onclick="Menu.updateMenu()" group="" id="">' +
			'<span class="l-btn-left">' +
				'<span class="l-btn-text icon-ok l-btn-icon-left">确定</span>' +
			'</span>' +
		'</a>' +
		'<a data-options="iconCls:\'icon-cancel\',plain:true" title="" class="easyui-linkbutton easyui-tooltip tooltip-f l-btn l-btn-plain" href="#" onclick="Menu.docancel()" group="" id="">' +
			'<span class="l-btn-left">' +
				'<span class="l-btn-text icon-cancel l-btn-icon-left">取消</span>' +
			'</span>' +
		'</a>',
	edit: function() {
		if (Menu.editingId != 0) {
			$('#MenuTree').treegrid('select', Menu.editingId);
			return false;
		}
		var row = $('#MenuTree').treegrid('getSelected');
		Menu.editingId = row.id;
		if (row) $('#MenuTree').treegrid('beginEdit', row.id);
		$('input.datagrid-editable-input').tooltip({
			hideEvent: 'none',
			content: Menu.contextMenu,
			onShow: function(){
				var t = $(this);
				t.tooltip('tip').focus().unbind();
			}
		});
	},
	append: function(rid) {
		if (rid == -1) {
			var row = $('#MenuTree').treegrid('getSelected');
			if (!row) {
				return $.messager.alert('提示信息', '没找到对应的上级菜单！', 'error');
				return false;
			}
			rid = row.id;
		}
		$('#name').val('');
		$('#path').val('');
		$('#icon').val('');
		$('#memo').val('');
		$('#FormAdd').dialog({
			title: '新增菜单',
			width: 235,
			closed: false,
			modal: true,
			buttons: [{
				'text': '保存',
				'iconCls': 'icon-ok',
				'handler': function() {
					var data = {
						rid: rid,
						name: $.trim($('#name').val()),
						path: $.trim($('#path').val()),
						icon: $.trim($('#icon').val()),
						memo: $.trim($('#memo').val())
					};
					if (data.name == '') {
						$('#name').trigger('focus');
						return false;
					}
					if (data.path == '') {
						$('#path').trigger('focus');
						return false;
					}
					$.commlib.ajax({
						'url': '/sysadmin/menu/add',
						'data': data,
						'success': function(rp) {
							if (rp.code != 0) {
								return $.messager.alert('提示信息', rp.info, 'error');
							}
							$('#MenuTree').treegrid('append', {
								parent: rp.data.root,
								data: [{
									id: rp.data.id,
									name: rp.data.name,
									path: rp.data.path,
									show: 1,
									icon: rp.data.icon,
									memo: rp.data.memo
								}]
							});
							$('#FormAdd').dialog('close');
							$('#MenuTree').treegrid('select', rp.data.id);
						}
					});
				}
			}, {
				'text': '取消',
				'iconCls': 'icon-no',
				'handler': function() {
					$('#FormAdd').dialog('close');
				}
			}]
		});
	},
	removeIt: function() {
		var row = $('#MenuTree').treegrid('getSelected');
		if (!row) {
			return $.messager.alert('提示信息', '请选择要删除的项！', 'error');
		}
		$.messager.confirm('提示信息', '你确定要删除所选项吗？<br />下级菜单同时被删除！<br /><span style="font-weight: bold; color: #FF0000;">注：删除操作不可恢复！</span>',
			function(r) {
				if (!r) return;
				$.commlib.ajax({
					url: '/sysadmin/menu/del',
					data: {id: row.id},
					success: function(rp) {
						if (rp.code != 0) {
							return $.messager.alert('提示信息', rp.info, 'error');
						}
						$('#MenuTree').treegrid('remove', row.id);
					}
				});
			}
		);
	},
	collapse: function() {
		var node = $('#MenuTree').treegrid('getSelected');
		$('#MenuTree').treegrid('collapse', node.id);
	},
	expand: function() {
		var node = $('#MenuTree').treegrid('getSelected');
		$('#MenuTree').treegrid('expand', node.id);
	},
	reload: function() {
		$.commlib.ajax({
			url: '/sysadmin/menu/refresh',
			data: {},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('#MenuTree').treegrid('reload');
			}
		});
	},
	moveup: function() {
		var row = $('#MenuTree').treegrid('getSelected');
		if (!row) {
			return $.messager.alert('提示信息', '请选择要移动的项！');
		}
		$.commlib.ajax({
			url: '/sysadmin/menu/move',
			data: {
				id: row.id,
				pos: -1
			},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				if (rp.data.reload) $('#MenuTree').treegrid('reload');
			}
		});
	},
	movedown: function() {
		var row = $('#MenuTree').treegrid('getSelected');
		if (!row) {
			return $.messager.alert('提示信息', '请选择要移动的项！');
		}
		$.commlib.ajax({
			url: '/sysadmin/menu/move',
			data: {
				id: row.id,
				pos: 1
			},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				if (rp.data.reload) $('#MenuTree').treegrid('reload');
			}
		});
	},
	updateMenu: function() {
		if (Menu.editingId == 0) {
			return false;
		}
		$('#MenuTree').treegrid('select', Menu.editingId);
		var row = $('#MenuTree').treegrid('getSelected');
		if (!row) return false;
		var name = $.trim($($('#MenuTree').treegrid('getEditor', {id: row.id, field: 'name'}).target).val());
		var path = $.trim($($('#MenuTree').treegrid('getEditor', {id: row.id, field: 'path'}).target).val());
		var icon = $.trim($($('#MenuTree').treegrid('getEditor', {id: row.id, field: 'icon'}).target).val());
		var memo = $.trim($($('#MenuTree').treegrid('getEditor', {id: row.id, field: 'memo'}).target).val());
		if (name == '') {
			return $.messager.alert('提示信息', '菜单名称不能为空！', 'error');
		}
		if (path == '') {
			return $.messager.alert('提示信息', '菜单路径不能为空！', 'error');
		}
		$.commlib.ajax({
			url: '/sysadmin/menu/edit',
			data: {
				id: row.id,
				name: name,
				path: path,
				icon: icon,
				memo: memo
			},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				Menu.editingId = 0;
				$('input.datagrid-editable-input').tooltip('hide');
				row.name = name;
				row.path = path;
				row.icon = icon;
				row.memo = memo;
				$('#MenuTree').treegrid('cancelEdit', row.id);
				$('#MenuTree').treegrid('reloadFooter');
			}
		});
	},
	docancel: function() {
		if (Menu.editingId == 0) {
			return false;
		}
		Menu.editingId = 0;
		var row = $('#MenuTree').treegrid('getSelected');
		$('input.datagrid-editable-input').tooltip('hide');
		$('#MenuTree').treegrid('cancelEdit', row.id);
	}
};