var RowConfigure = [{
	field: 'id',
	title: 'ID'
}, {
	field: 'name',
	title: '名称'
}, {
	field: 'cid',
	title: '分类'
}];
$(function() {
	ProApp.init();
});
var ProApp = (function($) {
	var self = this;
	var $tagname;
	var tree_cat_ = {android: false, ios: false};
	var editIndex   = -1;
	var editRowHTML = '';
	var editRowdata = {};
	var $saving = false;
	this.configure = {};
	this.starconf = {};
	this.system = {};
	this.init = function() {
		$tagname = $(':hidden#curr_tagname').val();
		eval('self.configure = ' + $(':hidden#cat_configure').val());
		eval('self.starconf = ' + $(':hidden#app_conf_star').val());
		eval('self.system = ' + $(':hidden#app_conf_system').val());
	};
	this.showForm = function(id) {
		var title = '增加新应用';
		var data = {};
		if (id > 0) {
			title = '修改应用';
			data.id = id;
		}
		$.commlib.ajax({
			url: '/rc/'+$tagname+'/info',
			data: data,
			success: function(rp) {
				if (rp.code != 0) {
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				$('div#InfoForm').dialog({
					title: title,
					width: 745,
					height: 520,
					closed: false,
					modal: true,
					buttons: [{
						text: '保存',
						iconCls: 'icon-ok',
						handler: ProApp.saveform
					}, {
						text: '取消',
						iconCls: 'icon-cancel',
						handler: function() {
							$('div#InfoForm').dialog('close');
						}
					}],
					content: rp.data.formdata,
					onOpen: function() {
						$("#update_time").datebox();
						$(':input.dctag').unbind().bind('change', ProApp.download);
						$('#S-TAB').tabs({
							onSelect: function(title) {
								$('tr.x-tab').hide();
								if (title == '属性') {
									$('tr.prop').show();
								} else if (title == '描述') {
									$('tr.desc').show();
								} else if (title == '更新') {
									$('tr.update').show();
								} else if (title == '图片') {
									$('tr.view').show();
								} else if (title == '下载') {
									$('tr.down').show();
								}
							}
						});
						$('#clearAppIMG').tooltip({
							position: 'top',
							content: '<span style="color: #fff; padding: 2px 5px;">清空所有图片!</span>',
							onShow: function(){
								$(this).tooltip('tip').css({
									backgroundColor: '#000',
									borderColor: '#666'
								});
							}
						});
						$('#browseAppIMG').tooltip({
							position: 'bottom',
							content: '<span style="color: #fff; padding: 2px 5px;">选择App图片!</span>',
							onShow: function(){
								$(this).tooltip('tip').css({
									backgroundColor: '#000',
									borderColor: '#666'
								});
							}
						});
						ProApp.download();
					}
				});
			}
		});
	};
	this.saveform = function() {
		if ($saving) return false;
		var xform = document.forms['x-form'];
		var fields = [
			'id',
			'cid',
			'name',
			'logo',
			'app_addr',
			'app_size',
			'app_md5',
			'app_support',
			'system',
			'is_break',
			'app_package',
			'app_classname',
			'memo',
			'version',
			'app_version',
			'lang',
			'star',
			'point',
			'do_sort',
			'update_time',
			'update_content',
			'app_keywords',
			'developer',
			'dc_total_base',
			'dc_day_from',
			'dc_week_from',
			'dc_month_from'
		];
		var formdata = {};
		for (var x in fields) {
			formdata[fields[x]] = $.trim(xform[fields[x]].value);
		}
		if (formdata.name == '') {
			xform.name.focus();
			return $.messager.alert('提示信息', '应用名称不能为空！', 'error');
		}
		if (formdata.cid == 0) {
			xform.cid.focus();
			return $.messager.alert('提示信息', '请选择应用分类！', 'error');
		}
		if (formdata.system == 0) {
			xform.system.focus();
			return $.messager.alert('提示信息', '请选择系统类型！', 'error');
		}
		$saving = true;
		formdata.pic = new Array();
		$('div.picbox:has("img")').each(function(i,x) {
			formdata.pic.push($(x).attr('picsrc'));
		});
		$.commlib.ajax({
			url: '/rc/'+$tagname+'/save/',
			data: formdata,
			success: ProApp.cb_save_handler,
			error: function() { $saving = false; }
		});
	};
	this.cb_save_handler = function(rp) {
		$saving = false;
		if (rp.code != 0) {
			return $.messager.alert('提示信息', rp.info, 'error');
		}
		$('div#InfoForm').dialog('close');
		$('#FormRow').datagrid('reload');
	};
	this.dataLoader = function() {
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
		$("td[field='dc_total_base']:gt(0)").click(ProApp.showEditor);
	};
	this.deleteRow = function(id) {
		$.messager.confirm('提示信息', '你确定要删除所选记录吗？<br /><span style="font-weight: bold; color: #FF0000;">注：删除操作不可恢复！</span>',
			function(r) {
				if (!r) return;
				$.commlib.ajax({
					url: '/rc/'+$tagname+'/del/',
					data: {id: id},
					success: function(rp) {
						if (rp.code != 0) {
							return $.messager.alert('提示信息', rp.info, 'error');
						}
						$('#FormRow').datagrid('reload');
					}
				});
			}
		);
	};
	this.showEditor = function() {
		if (editIndex != -1) {
			ProApp.saveDownload();
			return;
		}
		var $row = $(this).parents('tr');
		editIndex = $row.attr('datagrid-row-index');
		$('#FormRow')
			.datagrid('selectRow', editIndex)
			.datagrid('beginEdit', editIndex);
		editRowdata = $('#FormRow').datagrid('getSelected');
	};
	this.RedrawRowline = function() {
		$('#FormRow').datagrid('cancelEdit', editIndex);
		$('#FormRow').datagrid('updateRow', {
			index: editIndex,
			row: editRowdata
		});
		try {
			var $node = $("tr.datagrid-row[datagrid-row-index='"+editIndex+"']");
			$('a.my-link-edit', $node).linkbutton({iconCls: 'icon-edit', plain: true});
			$('a.my-link-remove', $node).linkbutton({iconCls: 'icon-remove', plain: true});
			$('a.my-icon-stat-ok', $node).linkbutton({iconCls: 'icon-ok', plain: true});
			$('a.my-icon-stat-no', $node).linkbutton({iconCls: 'icon-no', plain: true});
			$("td[field='dc_total_base']", $node).click(ProApp.showEditor);
		} catch(ex) {}
		editIndex = -1;
	};
	this.saveDownload = function() {
		var edi = $('#FormRow').datagrid('getEditor', {index: editIndex, field: 'dc_total_base'});
		var num = $(edi.target).numberbox('getValue');
		if (num == editRowdata.dc_total_base) {
			return ProApp.RedrawRowline();
		}
		$.commlib.ajax({
			url: '/rc/'+$tagname+'/setdownload/',
			data: {
				id: editRowdata.id,
				num: num
			},
			success: function(rp) {
				if (rp.code != 0) {
					$('#FormRow').datagrid('cancelEdit', editIndex);
					return $.messager.alert('提示信息', rp.info, 'error');
				}
				editRowdata.dc_total_base = num;
				ProApp.RedrawRowline();
			},
			error: function() { editIndex = -1; }
		});
	};
	this.staticon = function(t, val) {
		var icon = 'my-icon-stat-no';
		var data = val.split(':');
		if (data[0] == 1) icon = 'my-icon-stat-ok';
		return '<a class="' + icon + '" onclick="ProApp.chg(this, \'' + t + '\', \'' + data[1] + '\')"></a>';
	};
	this.setlogo = function(data) {
		var xform = document.forms['x-form'];
		xform.logo.value = data.url;
		$('#logoPreiew img').attr('src', data.url);
	};
	this.setpic = function(data) {
		var s = '<div class="picbox" picsrc="' + data.url + '" ondblclick="ProApp.delpic(this)"><img src="' + data.url + '" title="'+data.path+'" /></div>';
		$('#PicLoader div.contain').append(s);
	};
	this.setappaddr = function(data) {
		var xform = document.forms['x-form'];
		xform.app_addr.value = data.url;
		$.commlib.ajax({
			url: '/rc/'+$tagname+'/filequery',
			data: {
				file: data.path
			},
			success: function(rp) {
				if (rp.code != 0) return;
				xform.app_size.value = rp.data.size;
				xform.app_md5.value = rp.data.md5;
				if (rp.data.app_name != '') xform.name.value = rp.data.app_name;
				if (rp.data.package != '') xform.app_package.value = rp.data.package;
				if (rp.data.classname != '') xform.app_classname.value = rp.data.classname;
				if (rp.data.version != '') xform.version.value = rp.data.version;
				if (rp.data.app_version != '') xform.app_version.value = rp.data.app_version;
				if (rp.data.icon.url != '') ProApp.setlogo(rp.data.icon);
				// ** 在关键字后加入名称
				var kw = xform.app_keywords.value;
				kw = $.trim(kw);
				if (kw != '') {
					xform.app_keywords.value = kw + ',' + rp.data.app_name
				} else xform.app_keywords.value = rp.data.app_name;
			}
		});
	};
	this.delpic = function(o) {
		$(o).remove();
	};
	this.chg = function(o, t, id) {
		id = $.trim(id);
		$.commlib.ajax({
			url: '/rc/'+$tagname+'/chg',
			data: {
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
	this.download = function() {
		var conf = ['TOTAL', 'DAY', 'WEEK', 'MONTH'];
		var b, r, s;
		for (var x in conf) {
			b = parseInt($.trim($('#BASE_'+conf[x]+'_DOWNLOADS').val()));
			r = parseInt($.trim($('#REAL_'+conf[x]+'_DOWNLOADS').html()));
			if (isNaN(b)) b = 0;
			if (isNaN(r)) r = 0;
			s = b + r;
			$('#SHOW_'+conf[x]+'_DOWNLOADS').html(s)
		}
	};
	this.dosearch = function() {
		var selsystem = $('div#FormTool :input.system').val();
		var selbreak = $('div#FormTool :input.isbreak').val();
		var timefrom = $('div#FormTool :input.combo-text:eq(0)').val();
		var timeto = $('div#FormTool :input.combo-text:eq(1)').val();
		var searchname = $('div#FormTool :input#ws').val();
		var data = {
			page: 1,
			_selected_system: selsystem,
			_selected_ios_break: selbreak
		};
		if (searchname) data.keyword = searchname;
		if (timefrom) data.from = timefrom;
		if (timeto) data.to = timeto;
		var curr_cid = $('div#FormTool #cc').combotree('getValue');
		var curr_stat = $('div#FormTool #ss').combotree('getValue');
		if (curr_cid) data.cid = curr_cid;
		if (curr_stat) data.stat = curr_stat;
		$('#FormRow').datagrid({queryParams: data});
	};
	this.clearPicAll = function() {
		$('#PicLoader .picbox').trigger('dblclick');
	};
	return this;
})(jQuery);
var RowFormat = {
	system: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
	},
	category: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.configure[val];
	},
	system: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.system[val];
	},
	operat: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		var ophtml = '<a class="my-link-edit" onclick="ProApp.showForm('+val+')" title="'+val+'">编辑</a>';
		ophtml += '<a class="my-link-remove" onclick="ProApp.deleteRow('+val+')" title="'+val+'">删除</a>';
		return ophtml;
	},
	istoday: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('today', val);
	},
	isrecommend: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('recommend', val);
	},
	isnew: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('new', val);
	},
	ishot: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('hot', val);
	},
	ispopular: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('popular', val);
	},
	ischarge: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('charge', val);
	},
	iselite: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('elite', val);
	},
	isgamecenter: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('gamecenter', val);
	},
	isused: function(val, row) {
		if (val == undefined || $.trim(val) == '') return;
		return ProApp.staticon('used', val);
	}
};