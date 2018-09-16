var cardview = $.extend({}, $.fn.datagrid.defaults.view, {
	renderRow: function(target, fields, frozen, rowIndex, rowData){
		var cc = [];
		cc.push('<td colspan=' + fields.length + ' style="padding: 10px 5px; border: 0; border-bottom: 1px solid #CCCCCC;" bindid="'+rowData['id']+'">');
		if (!frozen) {
			cc.push('<div class="drag"><img src="' + rowData.logo + '" style="max-width: 205px; max-height: 68px;" /></div>');
			cc.push('<div style="float: left; margin-left: 20px;">');
			cc.push('<p class="cc-row">');
			cc.push('<span class="c-label">工具名称:</span> ' + rowData['name']);
			cc.push('</p><p class="cc-row">');
			cc.push('<span class="c-label">进度名称:</span> ' + rowData['url']);
			cc.push('</p><p class="cc-row">');
			cc.push('<span class="c-label">是否可用:</span> ');
			var icon = rowData['is_used'] == 1 ? 'ok' : 'no';
			cc.push('<a href="javascript: void(0);" onclick="Pro.chg(this, \''+rowData['id']+'\', \'is_used\')" class="easyui-linkbutton" iconCls="icon-'+icon+'" plain="true"></a>');
			cc.push('<span class="c-label">页面显示:</span> ');
			icon = rowData['isshow'] == 1 ? 'ok' : 'no';
			cc.push('<a href="javascript: void(0);" onclick="Pro.chg(this, \''+rowData['id']+'\', \'isshow\')" class="easyui-linkbutton" iconCls="icon-'+icon+'" plain="true"></a>');
			cc.push('<span class="c-label">是否锁定:</span> ');
			icon = rowData['islock'] == 1 ? 'ok' : 'no';
			cc.push('<a href="javascript: void(0);" onclick="Pro.chg(this, \''+rowData['id']+'\', \'islock\')" class="easyui-linkbutton" iconCls="icon-'+icon+'" plain="true"></a>');
			cc.push('<span class="c-label">是否可删:</span> ');
			icon = rowData['candel'] == 1 ? 'ok' : 'no';
			cc.push('<a href="javascript: void(0);" onclick="Pro.chg(this, \''+rowData['id']+'\', \'candel\')" class="easyui-linkbutton" iconCls="icon-'+icon+'" plain="true"></a>');
			cc.push('</p></div>');
		}
		cc.push('</td>');
		return cc.join('');
	}
});
var Pro = (function($) {
	var _sorting  = false;
	this.showForm = function(xid) {
		$.commlib.ajax({
			url: '/setting/tools/form',
			data: {id: xid},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('div#InfoForm').dialog({
					title: '网站设置',
					width: 600,
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
					content: rp.data.formdata,
					onOpen: function() {}
				});
			}
		});
	};
	this.saveform = function() {
		var xform = document.forms['x-form'];
		if (xform.name.value == '') {
			return $.messager.alert('提示信息', '请填写网站名称！', 'warning');
		}
		if (xform.url.value == '') {
			return $.messager.alert('提示信息', '请填写链接地址！', 'warning');
		}
		$.commlib.ajax({
			url: '/setting/tools/save/',
			data: $(xform).serialize(),
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('div#InfoForm').dialog('close');
				$('table#TableGrid').datagrid('reload');
			}
		});
	};
	this.deleteRow = function(id) {
		$.messager.confirm('提示信息', '你确定要删除所选记录吗？<br /><span style="font-weight: bold; color: #FF0000;">注：删除操作不可恢复！</span>',
			function(r) {
				if (!r) return;
				$.commlib.ajax({
					url: '/setting/tools/del/',
					data: {id: id},
					success: function(rp) {
						if (rp.code != 0) {
							return $.messager.alert('提示信息', rp.info, 'error');
						}
						$('#TableGrid').datagrid('reload');
					}
				});
			}
		);
	};
	this.setimglogo = function(data) {
		var xform = document.forms['x-form'];
		xform.logo.value = data.url;
		$('#viewbox').attr('src', data.url);
	};
	this.setdownloadurl = function(data) {
		var xform = document.forms['x-form'];
		var procname = xform.proc.value;
		xform.url.value = data.url;
		$.commlib.ajax({
			url: '/setting/tools/filequery',
			data: {
				file: data.path,
				proc: $.trim(procname)
			},
			success: function(rp) {
				if (rp.code != 0) return;
				if (rp.data.md5proc != '') {
					xform.md5proc.value = rp.data.md5proc;
				}
				xform.md5.value = rp.data.md5;
			}
		});
	};
	this.bindSortEvent = function() {
		$('table.datagrid-btable tr').draggable({
			handle: 'div.drag',
			revert: true,
			proxy: function(source){
				var p = $('<div style="border:1px solid #CCCCCC; background: #FFFFFF;"></div>');
				p.html($(source).html()).appendTo('body');
				return p;
			},
			onStopDrag: function(e) {
				if (!_sorting) return;
				_sorting = false;
				var sortmap = '';
				$('td[bindid]').each(function(ni, mx) {
					var idx = ni + 1;
					sortmap += idx + ':' + $(this).attr('bindid') + '|';
				});
				$.commlib.ajax({
					url: '/setting/tools/sorting/',
					data: {sorting: sortmap},
					success: function(rp) {
						if (rp.code != 0) {
							$.messager.alert('提示信息', rp.info, 'error');
							$('#TableGrid').datagrid('reload');
						}
						$('div.tooltip').remove();
						Pro.setTooltopEditor();
					}
				});
			}
		}).droppable({
			accept: 'table.datagrid-btable tr',
			onDrop: function(target, from) {
				var source = $(this).html();
				$(this).html($(from).html()).find('a').html('');
				$(from).html(source).find('a').html('');
				Pro.bindSortEvent();
				_sorting = true;
			}
		});
	};
	this.setTooltopEditor = function() {
		$('.datagrid-btable td[bindid]').each(function(ni, mo) {
			var bid = $(this).attr('bindid');
			$(this).tooltip('destroy');
			$(this).tooltip({
				hideEvent: 'none',
				position: 'right',
				content: function() {
					var s = '<div>';
					s += '<a href="javascript: void(0);" onclick="Pro.showForm('+bid+')" class="easyui-linkbutton easyui-tooltip" title="编辑" data-options="iconCls:\'icon-edit\',plain:true"></a>';
					s += '<a href="javascript: void(0);" onclick="Pro.deleteRow('+bid+')" class="easyui-linkbutton easyui-tooltip" title="删除" data-options="iconCls:\'icon-remove\',plain:true"></a>';
					s += '</div>';
					return s;
				},
				onShow: function() {
					var $oTool = $(this);
					$(this).tooltip('tip').focus().unbind().bind('blur',function(){
						var t = window.setInterval(function(){
							$oTool.tooltip('hide');
							window.clearInterval(t);
						}, 50);
					});
					$(this).tooltip('tip').css({
						borderColor: '#523532',
						backgroundColor: '#000000',
						boxShadow: '1px 1px 3px #353535'
					});
					$('.easyui-linkbutton').linkbutton();
				}
			});
		});
	};
	this.chg = function(o, id, flag) {
		id = $.trim(id);
		$.commlib.ajax({
			url: '/setting/tools/chg/',
			data: {
				id:   id,
				flag: flag
			},
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('#TableGrid').datagrid('reload');
				$('div.tooltip').remove();
				Pro.setTooltopEditor();
			}
		});
	};
	return this;
})(jQuery);
$(function(){
	var heitab = $(document).height() - 135;
	$('#TableGrid').css('height', heitab);
	$('#TableGrid').datagrid({
		view: cardview,
		onLoadSuccess: function() {
			Pro.bindSortEvent();
			Pro.setTooltopEditor();
			$('.easyui-linkbutton').linkbutton();
		},
		fixed: true
	});
});