{include file="_shared/header.tpl"}
<input id="curr_tagname" type="hidden" value="{$_curr_tagname}" />
<input id="cat_configure" type="hidden" value='{$_cat_configure}' />
<input id="app_conf_star" type="hidden" value='{$_app_conf_star}' />
<input id="app_conf_system" type="hidden" value='{$_app_conf_system}' />
<input id="catoptions" type="hidden" value='{$_cat_options_for_system}' />
<div id="FormTool">
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="ProApp.showForm(0)">新增</a>
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true">删除</a>
		&raquo;&raquo;
		{include file="_shared/search-system.tpl"}
		从：<input class="easyui-datebox" style="width:90px" />
		到：<input class="easyui-datebox" style="width:90px" />
		分类：
		<select id="cc" class="easyui-combotree" style="width: 120px;" data-options='data: {$_cat_tree}, value: 0'></select>
		标志：
		<select id="ss" class="easyui-combotree" style="width: 58px;" data-options='data: {$_cat_stat}, value: 0, iconCls: 0'></select>
		名称：
		<input id="ws" type="text" style="width: 120px;" value="" />
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="ProApp.dosearch()">搜索</a>
</div>
<table id="FormRow" class="easyui-datagrid" data-options="url:'/rc/{$_curr_tagname}/data?json_tag__=ON', fitColumns:true, singleSelect:true, toolbar: $('#FormTool'), pagination: true, pageSize: 20, onLoadSuccess: ProApp.dataLoader">
	<thead>
		<tr>
			<th data-options="field:'name', width: 50">名称</th>
			<th data-options="field:'cid', width: 50, align: 'center', formatter: RowFormat.category">分类</th>
			<th data-options="field:'system', width: 50, align: 'center', formatter: RowFormat.system">系统</th>
			<th data-options="field:'update_time', width: 50, align: 'center'">更新时间</th>
			<th data-options="field:'dc_total_base', width: 25, align: 'center', sortable:true, order: 'desc', editor:'numberbox'">下载次数</th>
			<th data-options="field:'do_sort', width: 25, align: 'center', sortable:true, order: 'desc', editor:'numberbox'">排序</th>
				<!-- <th data-options="field:'isused', align: 'center', formatter: RowFormat.isused, sortable:true, order: 'desc'">上架</th>
				<th data-options="field:'istoday', align: 'center', formatter: RowFormat.istoday, sortable:true, order: 'desc'">今日头条</th> -->
			<th data-options="field:'recommend', align: 'center', formatter: RowFormat.isrecommend, sortable:true, order: 'desc'">应用精选</th>
			<!-- <th data-options="field:'isnew', align: 'center', formatter: RowFormat.isnew, sortable:true, order: 'desc'">激活付费</th>
			<th data-options="field:'ishot', align: 'center', formatter: RowFormat.ishot, sortable:true, order: 'desc'">应用精选</th>
			<th data-options="field:'ispopular', align: 'center', formatter: RowFormat.ispopular, sortable:true, order: 'desc'">游戏中心</th>
			<th data-options="field:'id', align: 'center', formatter: RowFormat.operat">操作</th> -->

			<th data-options="field:'ischarge', align: 'center', formatter: RowFormat.ischarge, sortable:true, order: 'desc'">激活付费</th>
			<!-- <th data-options="field:'iselite', align: 'center', formatter: RowFormat.iselite, sortable:true, order: 'desc'">应用精选</th> -->
			<th data-options="field:'id', align: 'center', formatter: RowFormat.operat">操作</th>
		</tr>
	</thead>
</table>
<div class="my-ui-hidden">
	<div id="InfoForm"></div>
</div>
{include file="_shared/footer.tpl"}