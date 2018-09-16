<form name="x-form">
<input type="hidden" name="id" value="{$info.id}" />
<table class="my-x-form-table" style="width: 100%;">
	<tr>
		<th class="br" style="width: 65px;">
			<span>名称</span>
		</th>
		<td colspan="3">
			{html_options name=cid options=$_cat_options selected=$info.cid}
			&nbsp;
			<input type="text" name="name" value="{$info.name}" style="width: 455px;" />
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>APP地址</span>
		</th>
		<td colspan="3">
			<input type="text" name="app_addr" value="{$info.app_addr}" style="width: 560px;" />
			<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="$.explorer.open({ldelim}handler: ProApp.setappaddr{rdelim})"></a>
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>大小</span>
		</th>
		<td style="width: 200px;">
			<input type="text" name="app_size" value="{$info.app_size}" style="width: 180px;" />
		</td>
		<th class="bc">
			<span>MD5</span>
		</th>
		<td>
			<input type="text" name="app_md5" value="{$info.app_md5}" style="width: 270px;" />
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>系统类型</span>
		</th>
		<td>
			{html_options name=system options=$_app_system selected=$_app_selected_system disabled=true}
			{if $_app_selected_system eq 'ios'}
				{html_options name=is_break options=$_ios_breaking selected=$_is_breaked disabled=true}
			{else}
				<input type="hidden" name="is_break" value="0" />
			{/if}
		</td>
		<th class="bc">
			<span>包名</span>
		</th>
		<td>
			<input type="text" name="app_package" value="{$info.app_package}" style="width: 270px;" />
		</td>
	</tr>
	<tr>
		<th>
			<span>支持系统</span>
		</th>
		<td>
			<input type="text" name="app_support" value="{$info.app_support}" style="width: 180px;" />
		</td>
		<th>
			<span>类名</span>
		</th>
		<td>
			<input type="text" name="app_classname" value="{$info.app_classname}" style="width: 270px;" />
		</td>
	</tr>
	<tr>
		<td colspan="4">
			<div id="S-TAB" style="width: 680px;">
				<div title="属性" iconCls="my-icon-tab-app-prop"></div>
				<div title="描述" iconCls="my-icon-tab-app-desc"></div>
				<div title="更新" iconCls="my-icon-tab-app-update"></div>
				<div title="图片" iconCls="my-icon-tab-app-picture"></div>
				<div title="下载" iconCls="my-icon-tab-app-download"></div>
			</div>
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>版本</span>
		</th>
		<td>
			<input type="text" name="version" value="{$info.version}" style="width: 108px;" />
			（页面显示）
		</td>
		<td class="bl" colspan="2" rowspan="5">
			<div id="logoPreiew"><img src="{if $info.logo}{$info.logo}{else}/css/easy-ui/theme/images/blank.gif{/if}" border="0" /></div>
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>版本号</span>
		</th>
		<td>
			<input type="text" name="app_version" value="{$info.app_version}" style="width: 108px;" />
			（升级使用）
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>语言</span>
		</th>
		<td>
			{html_options name=lang options=$_app_lang selected=$info.lang}
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>星级</span>
		</th>
		<td>
			{html_options name=star options=$_app_stars selected=$info.star}
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>评分</span>
		</th>
		<td>
			<input type="text" name="point" value="{$info.point}" />
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>排序</span>
		</th>
		<td>
			<input type="text" name="do_sort" value="{$info.do_sort}" />
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>更新时间</span>
		</th>
		<td>
			<input type="text" id="update_time" name="update_time" value="{$info.update_time|default:$smarty.now|date_format: '%Y-%m-%d'}" />
		</td>
		<th class="bc" style="width: 65px;">
			<span>LOGO</span>
		</th>
		<td>
			<input type="text" name="logo" value="{$info.logo}" style="width: 270px;" />
			<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="$.explorer.open({ldelim}handler: ProApp.setlogo{rdelim})"></a>
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>关键字</span>
		</th>
		<td colspan="3">
			<input type="text" name="app_keywords" value="{$info.app_keywords}" style="width: 570px;" />
		</td>
	</tr>
	<tr class="x-tab prop">
		<th class="br">
			<span>开发商</span>
		</th>
		<td colspan="3">
			<input type="text" name="developer" value="{$info.developer}" style="width: 570px;" />
		</td>
	</tr>
	<tr class="x-tab desc">
		<th class="br">
			<span>APP描述</span>
		</th>
		<td colspan="3">
			<textarea name="memo" style="width: 100%; height: 225px; border: 1px solid #D4D4D4;">{$info.memo}</textarea>
		</td>
	</tr>
	<tr class="x-tab update">
		<th class="br">
			<span>更新内容</span>
		</th>
		<td colspan="3">
			<textarea name="update_content" style="width: 100%; height: 225px; border: 1px solid #D4D4D4;">{$info.update_content}</textarea>
		</td>
	</tr>
	<tr class="x-tab view">
		<th class="br" style="height: 25px;">&nbsp;</th>
		<td colspan="3" rowspan="5" id="PicLoader" style="padding: 0;">
			<div class="contain" style="width: 610px; height: 230px; overflow-x: hidden; overflow-y: scroll; margin: 0; padding: 0;">
				{foreach $info.list_pic as $pic}
					<div class="picbox" picsrc="{$pic}" ondblclick="ProApp.delpic(this)"><img src="{$pic}" /></div>
				{/foreach}
			</div>
		</td>
	</tr>
	<tr class="x-tab view" style="height: 25px;">
		<th class="br">
			<a id="clearAppIMG" href="javascript: void(0);" class="easyui-linkbutton" iconCls="my-icon-unselect" plain="true" onclick="ProApp.clearPicAll()"></a>
		</th>
	</tr>
	<tr class="x-tab view">
		<th class="br" style="height: 25px;">
			<span>上传多图</span>
		</th>
	</tr>
	<tr class="x-tab view">
		<th class="br" style="height: 25px;">
			<a id="browseAppIMG" href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="$.explorer.open({ldelim}handler: ProApp.setpic, multi: true{rdelim})"></a>
		</th>
	</tr>
	<tr class="x-tab view" style="height: 120px; overflow: hidden;">
		<th class="br">&nbsp;</th>
	</tr>
	<tr class="x-tab down">
		<th class="br">
			<span>下载次数</span>
		</th>
		<td colspan="3">
			<table style="font-size: 12px; width: 88%; text-align: center;">
				<tr>
					<td>&nbsp;</td>
					<td>总</td>
					<td>日</td>
					<td>周</td>
					<td>月</td>
				</tr>
				<tr>
					<td>基数</td>
					<td><input type="text" class="dctag" id="BASE_TOTAL_DOWNLOADS" name="dc_total_base" value="{$info.dc_total_base}" style="width: 80px;" /></td>
					<td><input type="text" class="dctag" id="BASE_DAY_DOWNLOADS"   name="dc_day_from"   value="{$info.dc_day_from}"   style="width: 80px;" /></td>
					<td><input type="text" class="dctag" id="BASE_WEEK_DOWNLOADS"  name="dc_week_from"  value="{$info.dc_week_from}"  style="width: 80px;" /></td>
					<td><input type="text" class="dctag" id="BASE_MONTH_DOWNLOADS" name="dc_month_from" value="{$info.dc_month_from}" style="width: 80px;" /></td>
				<tr>
				<tr>
					<td>实际</td>
					<td><span id="REAL_TOTAL_DOWNLOADS">{$info.downloads}</span></td>
					<td><span id="REAL_DAY_DOWNLOADS">{$info.day_real_downloads}</span></td>
					<td><span id="REAL_WEEK_DOWNLOADS">{$info.week_real_downloads}</span></td>
					<td><span id="REAL_MONTH_DOWNLOADS">{$info.month_real_downloads}</span></td>
				</tr>
				</tr>
					<td>显示</td>
					<td><span id="SHOW_TOTAL_DOWNLOADS"></span></td>
					<td><span id="SHOW_DAY_DOWNLOADS"></span></td>
					<td><span id="SHOW_WEEK_DOWNLOADS"></span></td>
					<td><span id="SHOW_MONTH_DOWNLOADS"></span></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>