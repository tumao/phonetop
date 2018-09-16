{include file="_shared/header.tpl"}
<div id="ConMenu" class="easyui-menu" style="width:120px;">
	<div onclick="ProCat.editRow(-1, -1)" data-options="iconCls:'icon-edit'">{#EDIT#}</div>
	<div class="menu-sep"></div>
	<div onclick="ProCat.showform(-1)" data-options="iconCls:'icon-add'">{#ADD#}</div>
	<div onclick="ProCat.dodelete()" data-options="iconCls:'icon-remove'">{#DEL#}</div>
</div>
<div id="FormTool">
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="ProCat.showform()">新增</a>
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="ProCat.dodelete()">删除</a>
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="ProCat.saveRow()">保存</a>
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-reload" plain="true" onclick="ProCat.resetRow()">删除</a>
		{if $curr_tagname eq 'software' OR $curr_tagname eq 'game'}
		&raquo;&raquo;
		{html_options name=_selected_system options=$system_cat selected=$_selected_system class="system"}
		<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="ProCat.dosearch('{$curr_tagname}')">搜索</a>
		{/if}
</div>
<table id="CategoryTree" title="手机大师『分类管理』"></table>
<input id="curr_root_id" type="hidden" value="{$curr_root_id}" />
<input id="curr_tagname" type="hidden" value="{$curr_tagname}" />
<div class="my-ui-hidden">
	<div id="ProCatForm">
		<div style="margin-top: 15px; margin-bottom: 15px;">
			<span>&nbsp;{#PRO_CAT_ROOT#}</span>
			<span id="rootname"></span>
			<input id="root" type="hidden" value="" />
		</div>
		<div>
			<span>&nbsp;{#PRO_CAT_NAME#}</span>
			<input id="name" type="text" class="easyui-validatebox" data-options="required:true" />
		</div>
		<div style="height: 18px;"></div>
	</div>
</div>
{include file="_shared/footer.tpl"}