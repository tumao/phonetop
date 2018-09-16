(function($){
	$.commlib = (function() {
		var self = this;
		this.ajax  = function(data) {
			if (typeof data.data == 'string') {
				data.data += '&json_tag__=ON';
			} else data.data.json_tag__ = 'ON';
			data.type = 'post';
			data.dataType = 'json';
			$.ajax(data);
		};
		this.mainframe = function() {
			$('#main').height($(document).height()-5);
			$('#main').layout('resize');
		};
		this.selsubmenu	= function() {
			if ($('ul#NavMenuTree').size() < 1) return true;
			var menuid = $('ul#NavMenuTree').attr('_curr_menu_id');
			if (menuid == '' || menuid == 0) return false;
			var selectedmenu = $('ul#NavMenuTree').tree('find', menuid);
			$('ul#NavMenuTree').tree('select', selectedmenu.target);
		};
		this.main_get_size = function() {
			var p = $('#main').layout('panel', 'center');
			return {
				width: p.panel('panel').width(),
				height: p.panel('panel').height()
			};
		};
		this.contain_get_size = function() {
			return {
				width: $('.contain-body').width(),
				height: $('.contain-body').height()
			};
		};
		this.json_uri_ = function(uri) {
			if (uri.search(/\?/i) == -1) {
				uri += '?json_tag__=ON&';
			} else {
				uri += '&json_tag__=ON&';
			}
			uri += Math.random().toString().substring(2, 7);
			return uri;
		};
		this.submenu_click = function(node) {
			window.location.href=node.attributes.path;
		};
		this.resizedatagrid = function() {
			
		};
		return this;
	})();
	$.explorer = (function() {
		var framehtml = '';
		framehtml += '	<div id="EXPLAYOUT" style="width: 736px; height: 464px; overflow-x: hidden;">';
		framehtml += '		<div data-options="region:\'west\'" style="width:180px; padding: 5px;">';
		framehtml += '			<div id="EXPNEWDIR" class="explorer-new-panel">';
		framehtml += '				<div>根目录：『<span></span>』<input type="hidden" id="newFolderRootID" value="" /></div>';
		framehtml += '				<div>新目录：<input type="text" id="newFolderName" value="" /></div>';
		framehtml += '				<div><button onclick="$.explorer.mkdir()">确定</button><button onclick="$(\'#EXPNEWDIR\').hide()">取消</button></div>';
		framehtml += '			</div>';
		framehtml += '			<div id="EXPDIRTREE"></div>';
		framehtml += '		</div>';
		framehtml += '		<div data-options="region:\'center\'">';
		framehtml += '			<div id="EXPFOLDER" style="width: 554px; height: 462px;">';
		framehtml += '				<div data-options="region:\'center\'" style="padding: 5px; overflow-x: hidden;">';
		framehtml += '					<div id="EXPFILELIST"></div>';
		framehtml += '				</div>';
		framehtml += '				<div data-options="region:\'south\'" style="height:25px">';
		framehtml += '					<input type="file" id="uploadexp" name="uploadexp" style="width: 470px;" />';
		framehtml += '					<input type="button" value="上传" onclick="$.explorer.upload()" />';
		framehtml += '				</div>';
		framehtml += '			</div>';
		framehtml += '		</div>';
		framehtml += '	</div>';
		framehtml += '	<div id="EXPDIRMENU" class="easyui-menu" style="width:120px;">';
		framehtml += '		<div onclick="$.explorer.rmdir()" data-options="iconCls:\'icon-cancel\'">删除</div>';
		framehtml += '		<div class="menu-sep"></div>';
		framehtml += '		<div onclick="$.explorer.newdir()" data-options="iconCls:\'icon-add\'">新建子目录</div>';
		framehtml += '	</div>';
		var menubarhtml = '<div id="EXPNAVBAR">';
		menubarhtml += '<a href="javascript:void(0);" class="_ex_sp" data-options="menu: \'#_m_exp_sel\', iconCls: \'icon-sum\'">全选</a>';
		menubarhtml += '<a href="javascript:void(0);" data-options="iconCls:\'my-icon-ok\'">确定</a>';
		menubarhtml += '</div>';
		menubarhtml += '<div id="_m_exp_sel">';
		menubarhtml += '<div onclick="$.explorer.selectall()" data-options="iconCls:\'my-icon-select-all\'">全选</div>';
		menubarhtml += '<div onclick="$.explorer.selectother()" data-options="iconCls:\'my-icon-select-other\'">反选</div>';
		menubarhtml += '<div class="menu-sep"></div>';
		menubarhtml += '<div onclick="$.explorer.unselectall()" data-options="iconCls:\'my-icon-unselect\'">清空</div>';
		menubarhtml += '</div>';'';
		var __curr_dir = '';
		var dialog = 'div#__EXPLORER_ID';
		var menubar = 'div#EXPNAVBAR a._ex_sp';
		var layout = 'div#EXPLAYOUT';
		var expcallback = function() {};
		var is_multi_selection = false;
		this.open = function(ext) {
			if ($(dialog).size() < 1) {
				$('body').append("<div id='__EXPLORER_ID' />");
			}
			if (ext && ext.handler && typeof ext.handler == 'function') {
				expcallback = ext.handler;
			} else expcallback = function() {};
			__curr_dir = '';
			$(dialog).dialog({
				width: 750, height: 500, modal: true,
				title: '&nbsp;&raquo;&nbsp;文件管理器',
				content: framehtml,
				onOpen: function() {
					$(layout).layout();
					$('div#EXPFOLDER').layout();
					$('div#EXPDIRTREE').tree({
						url: '/explorer/dir?json_tag__=ON',
						onContextMenu: function(e, node) {
							e.preventDefault();
							$(this).tree('select', node.target);
							$('#EXPDIRMENU').menu('show', {
								left: e.pageX,
								top: e.pageY
							});
						},
						onSelect: function(row) {
							if (__curr_dir == row.id) return;
							__curr_dir = row.id;
							$.explorer.showFileGrid();
						}
					});
					if (ext.multi){
						$(menubarhtml).insertBefore($('div#EXPFILELIST'));
						$(menubar).splitbutton().siblings().linkbutton({plain: true});
						$(menubar).unbind().bind('click', function() {
							if ($('#EXPFILELIST div.explorer-file-nocheck').size() > 0) {
								$.explorer.selectall();
							} else $.explorer.unselectall();
						});
						$('div#EXPNAVBAR a:eq(1)').unbind().bind('click', function() {
							$('#EXPFILELIST div.explorer-file-checked').each(function() {
								expcallback({
									path: $(this).attr('rpath'),
									url: $(this).attr('fileurl')
								});
							});
							$.explorer.dialogclose();
						});
						is_multi_selection = true;
					} else is_multi_selection = false;
					$.explorer.showFileGrid();
				}
			});
		};
		this.dialogclose = function() {
			$(dialog).dialog('close');
		};
		this.upload = function() {
			$.ajaxFileUpload({
				url: '/explorer/upload?id=' + __curr_dir + '&json_tag__=ON', 
				secureuri: false,
				fileElementId: 'uploadexp',
				dataType: 'json',
				success: function (data, status) {
					if(data.code != 0) {
						return $.messager.alert('提示信息', data.info, 'error');
					}
					$.explorer.showFileGrid();
				},
				error: function (data, status, e) {
					alert(e);
				}
			});
		};
		this.newdir = function() {
			var row = $('div#EXPDIRTREE').tree('getSelected');
			if (!row) return false;
			$('#newFolderRootID').val(row.id);
			var folder = $('#newFolderName').val('');
			$('#EXPNEWDIR span').html(row.text);
			$('#EXPNEWDIR').show();
		};
		this.mkdir = function() {
			var rootid = $('#newFolderRootID').val();
			var folder = $.trim($('#newFolderName').val());
			if (folder.search(/^[a-z0-9\._-]+$/i) == -1) {
				return $.messager.alert('提示信息', '目录名只能由<br />字母数字、点号(.)、短划线(-)、下划线(_) 组成！', 'error');
			}
			$.commlib.ajax({
				url: '/explorer/mkdir',
				data: {
					id: rootid,
					name: folder
				},
				success: function(rp) {
					if (rp.code != 0) {
						return $.messager.alert('提示信息', rp.info, 'error');
					}
					$('#EXPNEWDIR').hide();
					if (rootid == '6666cd76f96956469e7be39d750cc7d9')
					{
						$('div#EXPDIRTREE').tree('reload');
						return;
					}
					var row = $('div#EXPDIRTREE').tree('find', rootid);
					$('div#EXPDIRTREE').tree('reload', row.target);
					$('div#EXPDIRTREE').tree('expandTo', row.target);
				}
			});
		};
		this.rmdir = function() {
			var row = $('div#EXPDIRTREE').tree('getSelected');
			if (!row) return false;
			if (row.id == '/') {
				return $.messager.alert('提示信息', '根目录不能删除！', 'info');
			}
			$.messager.confirm('提示信息', '你确定要删除所选目录吗？<br /><span style="font-weight: bold; color: #FF0000;">注：删除操作不可恢复！</span>',
				function(r) {
					if (!r) return;
					$.commlib.ajax({
						url: '/explorer/rmdir',
						data: {id: row.id},
						success: function(rp) {
							if (rp.code != 0) {
								return $.messager.alert('提示信息', rp.info, 'error');
							}
							$('div#EXPDIRTREE').tree('remove', row.target);
						}
					});
				}
			);
		};
		this.showFileGrid = function() {
			$.commlib.ajax({
				url: '/explorer/file/',
				data: {id: __curr_dir},
				success: function(rp) {
					if (rp.code != 0) {
						return $.messager.alert('提示信息', rp.info, 'error');
					}
					$('#EXPFILELIST').html('');
					var list = rp.data.files;
					for (var i in list) filegrid(list[i]);
				}
			});
		};
		this.selectall = function() {
			$('#EXPFILELIST div.explorer-file-box')
				.removeClass('explorer-file-nocheck')
				.addClass('explorer-file-checked');
		};
		this.selectother = function() {
			$('#EXPFILELIST div.explorer-file-box').each(function() {
				if ($(this).hasClass('explorer-file-checked')) {
					$(this).removeClass('explorer-file-checked').addClass('explorer-file-nocheck');
				} else $(this).removeClass('explorer-file-nocheck').addClass('explorer-file-checked');
			});
		};
		this.unselectall = function() {
			$('#EXPFILELIST div.explorer-file-box')
				.removeClass('explorer-file-checked')
				.addClass('explorer-file-nocheck');
		};
		function filegrid(fi) {
			if (!fi) return;
			var file_icon = fi.url;
			if (fi.icon != '-') {
				file_icon = '/images/file/' + fi.icon + '.png';
			}
			var s ='<div class="explorer-file-box';
			if (is_multi_selection) s += ' explorer-file-nocheck';
			s += '" rpath="' + fi.path + '" fileurl="' + fi.url + '" >';
			s += '		<div class="imgbox" title="'+fi.name+'"><img src="' + file_icon + '" /></div>';
			s += '		<div class="file"><span>' + fi.name + '</span></div>';
			s += '	<div>';
			$('#EXPFILELIST').append(s);
			$('#EXPFILELIST div.explorer-file-box').unbind().bind('dblclick', function(){
				if (is_multi_selection) return true;
				expcallback({
					path: $(this).attr('rpath'),
					url: $(this).attr('fileurl')
				});
				$.explorer.dialogclose();
			});
			if (is_multi_selection) $('#EXPFILELIST div.explorer-file-box').bind('click', function() {
				if ($(this).hasClass('explorer-file-nocheck')) {
					$(this).removeClass('explorer-file-nocheck').addClass('explorer-file-checked');
				} else $(this).removeClass('explorer-file-checked').addClass('explorer-file-nocheck');
			});
		}
		return this;
	})();
	$.toolsearch = {
		catoption: null,
		breakswap: function (o) {
			if (o.value == 'ios') {
				$(o).next().show();
			} else {
				$(o).next().hide();
			}
			if ($('#FormTool #cc').size() > 0 ) {
				if ($.toolsearch.catoption == null) {
					eval('$.toolsearch.catoption=' + $(':hidden#catoptions').val());
					if ($.toolsearch.catoption.ios) {
						$.toolsearch.catoption.ios.push({id: 0, text: '全部'});
					} else $.toolsearch.catoption.ios = [{id: 0, text: '全部'}];
					if ($.toolsearch.catoption.android) {
						$.toolsearch.catoption.android.push({id: 0, text: '全部'});
					} else $.toolsearch.catoption.android = [{id: 0, text: '全部'}];
				}
				if ($.toolsearch.catoption == null) return;
				$('#FormTool #cc').combotree('setValue', 0);
				$('#FormTool #cc').combotree('loadData', $.toolsearch.catoption[o.value]);
			}
		},
		breakswapcat: function(o) {
			if ($('#FormTool #cc').size() < 1) return;
		}
	};
})(jQuery);
$(function() {
	$.commlib.mainframe();
	$.commlib.selsubmenu();
});