<div id="main" class="easyui-layout" style="width:100%; height:230px;">
	<div data-options="region:'north'" style="height:68px; overflow:hidden;">
		<div class="easyui-layout" style="width:100%; height:65px; border: 0;">
			<div data-options="region:'west', resizable:false, collapsible:false, title:''" style="width:200px; border: 0; background: url(/images/bg1x50.png) bottom repeat-x; overflow: hidden;">
				<img src="/images/zqb-logo.png" style="max-height: 50px; margin-top: 5px; margin-left: 18px;" />
			</div>
			<div data-options="region:'center', title:'', resizable:false, collapsible:false" style=" border: 0; background: url(/images/bg1x50.png) bottom repeat-x;">
				<div id="headerMenubar" style="padding:5px;border:1px solid #ddd; border: 0;">
					{foreach $menuRoot as $menu}
						<a href="{$menu.path}" class="easyui-linkbutton" data-options="plain:true,iconCls:'{$menu.icon}'{if $menu.selected},disabled:true" style="background: url(/images/bg1x24.png) repeat-x #3A3B3C; color:#FFFFFF;font-weight:bold;{/if}">{$menu.name}</a>
					{/foreach}
				</div>
				<div id="headerUserPanel">
					<span>{$smarty.now|date_format: '%Y-%m-%d'}&nbsp;&nbsp;</span>
					<a href="javascript: void(0);" class="easyui-linkbutton" data-options="iconCls: 'icon-ok'" onclick="window.location.href='/user/logout/'">注销</a>
				</div>
			</div>
		</div>
	</div>
	<div data-options="region:'west', resizable:true, title:'{#MENU_NAVIGATION#}'" style="width:200px; background: url(/images/bg200x1.png) repeat-y;">
		<div style="height: 10px;"></div>
		<ul id="NavMenuTree" _curr_menu_id='{$selected_submenu_id}' class="easyui-tree" data-options='{ldelim}animate: true, onClick: $.commlib.submenu_click, data: {$submenu}{rdelim}'></ul>
	</div>
	<div data-options="region:'center', title:'{#SYSTEM_NAME#}{if $navbar.base.name neq ''} &raquo; <a href=\'{$navbar.base.path}\' class=\'my-navtitle\'>{$navbar.base.name}</a>{/if}{if $navbar.child.name neq ''} &raquo; <a href=\'{$navbar.child.path}\' class=\'my-navtitle\'>{$navbar.child.name}</a>{/if}', iconCls:'icon-tip', onResize: $.commlib.resizedatagrid()">
		<div class="container">
			<div class="contain-body">