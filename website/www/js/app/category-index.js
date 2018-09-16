$(function() {
	var panel = $.commlib.main_get_size();
	var contain = $.commlib.contain_get_size();
	var tagname	= $(':input#curr_tagname').val();
	$('#CategoryTree').treegrid({
		animate: true,
		fitColumns: true,
		url: $.commlib.json_uri_('/category/tree/'+tagname),
		idField:'id',
		treeField:'name',
		toolbar: $('#FormTool'),
		columns:[[
			{field:'name', title:'分类名称', width: 200, editor:'text'}
		]],
		onDblClickRow: ProCat.editRow,
		onContextMenu: function(e, row){
			e.preventDefault();
			$(this).treegrid('select', row.id);
			$('#ConMenu').menu('show',{
				left: e.pageX,
				top: e.pageY
			});
		}
	});
	ProCat.init();
	$('#CategoryTree').treegrid('resize', {width: contain.width, height: panel.height-50});
});
var ProCat = (function($) {
	var self = this;
	var $editingId = 0;
	var $tree;
	var $form;
	var $root;
	this.init = function() {
		$tree = $('table#CategoryTree');
		$form = $('div#ProCatForm');
		$root = $(':input#curr_root_id').val();
	};
	this.showform = function(rootid) {
		if (rootid == -1) {
			var row = $tree.treegrid('getSelected');
			$('#root').val(row.id);
			$('#rootname').html(row.name);
		} else {
			$('#root').val($root);
			$('#rootname').html('-');
		}
		$('#name').val('');
		$form.dialog({
			title: '新增菜单',
			width: 245,
			closed: false,
			modal: true,
			buttons: [{
				text: '保存',
				iconCls: 'icon-ok',
				handler: self.dosave
			}, {
				text: '取消',
				iconCls: 'icon-cancel',
				handler: function() { $form.dialog('close'); }
			}]
		});
	};
	this.dosave = function() {
		var name = $.trim($('#name').val());
		var root = $.trim($('#root').val());
		var data = {
			root: root,
			name: name
		};
		$.commlib.ajax({
			'url': '/category/save/',
			'data': data,
			'success': function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$tree.treegrid('append', {
					parent: root,
					data: [{
						id: rp.data.id,
						name: rp.data.name
					}]
				});
				$form.dialog('close');
				$tree.treegrid('select', rp.data.id);
			}
		});
	};
	this.editRow = function(row, data) {
		if ($editingId != 0)
		{
			$tree.treegrid('select', $editingId);
			return false;
		}
		if (row == -1) {
			var row = $tree.treegrid('getSelected');
		}
		$editingId = row.id;
		$tree.treegrid('beginEdit', row.id);
	};
	this.saveRow = function() {
		if ($editingId == 0) {
			return false;
		}
		$tree.treegrid('select', $editingId);
		var row = $tree.treegrid('getSelected');
		var name = $.trim($($tree.treegrid('getEditor', {id: row.id, field: 'name'}).target).val());
		if ($.trim(row.name) == name) self.resetRow();
		var data = {
			id: $editingId,
			name: name,
			root: $root
		};
		$.commlib.ajax({
			'url': '/category/save/',
			'data': data,
			'success': function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				row.name = name;
				$tree.treegrid('reloadFooter');
				$tree.treegrid('cancelEdit', $editingId);
				$editingId = 0;
			}
		});
	};
	this.resetRow = function() {
		if ($editingId == 0) {
			return false;
		}
		$tree.treegrid('cancelEdit', $editingId);
		$editingId = 0;
	};
	this.dodelete = function() {
		if ($editingId != 0) {
			return $.messager.alert('提示信息', '请先处理当前编辑中的分类！', 'warning');
		}
		var row = $tree.treegrid('getSelected');
		if (!row) {
			return $.messager.alert('提示信息', '请选择要删除的分类！', 'info');
		}
		$.messager.confirm('提示信息', '你确定要删除所选项吗？<br />下级分类同时被删除！<br /><span style="font-weight: bold; color: #FF0000;">注：删除操作不可恢复！</span>',
			function(r) {
				if (!r) return;
				$.commlib.ajax({
					url: '/category/del',
					data: {id: row.id},
					success: function(rp) {
						if (rp.code != 0) {
							return $.messager.alert('提示信息', rp.info, 'error');
						}
						$tree.treegrid('remove', row.id);
					}
				});
			}
		);
	};
	this.dosearch = function(tagname) {
		var selsystem = $('div#FormTool :input.system').val();
		var oForm = document.createElement('form');
		oForm.setAttribute('method', 'post');
		oForm.setAttribute('action', 'category/'+tagname);
		var oInput = document.createElement('input');
		oInput.setAttribute('type', 'hidden');
		oInput.setAttribute('name', '_selected_system');
		oInput.setAttribute('value', selsystem);
		oForm.appendChild(oInput);
		document.body.appendChild(oForm);
		oForm.submit();
	};
	return this;
})(jQuery);