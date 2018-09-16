{include file="_shared/header.tpl"}
<div id="FormTool">
	<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="Pro.showForm(0)">新增</a>
</div>
<table id="TableGrid" title="Phone桌面网站" singleSelect="true" fitColumns="true" data-options="toolbar:$('#FormTool')" remoteSort="false" url="/setting/sites/data?json_tag__=ON" pagination="false">
	<thead>
		<tr>
			<th field="id" width="100" sortable="true">序号</th>
			<th field="name" width="100" sortable="true">网站名称</th>
			<th field="url" width="100" sortable="true">链接地址</th>
			<th field="logo" width="100" sortable="true">LOGO图片</th>
			<th field="is_used" width="100" sortable="true">是否可用</th>
			<th field="islock" width="100" sortable="true">是否锁定</th>
			<th field="candel" width="100" sortable="true">是否可删</th>
		</tr>
	</thead>
</table>
<div class="my-ui-hidden">
	<div id="InfoForm"></div>
</div>
<script type="text/javascript" src="/js/app/setting-sites.js"></script>
<style type="text/css">
.c-label { display: inline-block; width: 80px; font-weight: bold; padding-right: 5px; text-align: right; }
.datagrid-row-selected { color: #000000; background-color: #EFEFEF; }
.cc-row { min-width: 580px; margin: 2px; padding: 0; line-height: 24px; }
.drag { float: left; width: 208px; text-align: center; height: 80px;}
</style>
{include file="_shared/footer.tpl"}