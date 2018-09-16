{include file="_shared/header.tpl"}
<table id="MenuTree" title="{#SYS_ADMIN_MENU_DESC#}&nbsp;&raquo;&nbsp;<a class='easyui-linkbutton' href='javascript: Menu.append(0);'>{#ADD_NEW_TOP_MENU#}</a>"></table>
<div id="ConMenu" class="easyui-menu" style="width:120px;">
	<div onclick="Menu.edit()" data-options="iconCls:'icon-edit'">{#EDIT#}</div>
	<div class="menu-sep"></div>
	<div onclick="Menu.append(-1)" data-options="iconCls:'icon-add'">{#ADD#}</div>
	<div onclick="Menu.removeIt()" data-options="iconCls:'icon-remove'">{#DEL#}</div>
	<div class="menu-sep"></div>
	<div onclick="Menu.moveup()" data-options="iconCls:'my-icon-moveup'">{#MOVEUP#}</div>
	<div onclick="Menu.movedown()" data-options="iconCls:'my-icon-movedown'">{#MOVEDOWN#}</div>
	<div class="menu-sep"></div>
	<div onclick="Menu.collapse()">{#COLLAPSE#}</div>
	<div onclick="Menu.expand()">{#EXPAND#}</div>
	<div class="menu-sep"></div>
	<div onclick="Menu.reload()" data-options="iconCls:'icon-reload'">{#RELOAD#}</div>
</div>
<div class="my-ui-hidden">
	<div id="FormAdd">
		<div>
			<span>{#MENU_NAME#}</span>
			<input id="name" type="text" class="easyui-validatebox" data-options="required:true" />
		</div>
		<div>
			<span>{#MENU_PATH#}</span>
			<input id="path" type="text" class="easyui-validatebox" data-options="required:true" />
		</div>
		<div>
			<span>{#MENU_ICON#}</span>
			<input id="icon" type="text" />
		</div>
		<div>
			<span>{#MENU_MEMO#}</span>
			<input id="memo" type="text" />
		</div>
	</div>
</div>
{include file="_shared/footer.tpl"}