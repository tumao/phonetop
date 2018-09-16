<div class="header">&nbsp;</div>
<div class="easyui-panel" title="{#USER_LOGIN#}" style="width: 520px; height: 250px; padding:10px;">
	<div class="easyui-layout" data-options="fit:true">
		<div data-options="region:'west',split:false" style="width:120px; padding:10px; border:0;">
			<img src="/images/zq-logo.png" />
		</div>
		<div data-options="region:'center'" style="padding:10px; border:0;">
			<div class="rowfield">
				&nbsp;
			</div>
			<div class="rowfield">
				{#USERNAME#} <input id="username" type="text" class="easyui-validatebox" data-options="required:true, validType:'length[4, 50]'" />
			</div>
			<div class="rowfield">
				{#PASSWORD#} <input id="password" type="password" class="easyui-validatebox" data-options="required:true" />
			</div>
			<div class="rowfield" style="margin-left: 27px; vertical-align: middle; font-family:tahoma;font-size:12px;">
				<input id="remember" type="checkbox" value="ON" style="vertical-align:middle;" /> 记住密码
			</div>
			<div class="rowfield">
				<a href="javascript: void(0);" onclick="userlogin()" class="easyui-linkbutton" data-options="iconCls:'icon-ok'">{#OK#}</a>
				<a href="javascript: void(0);" onclick="formclear()" class="easyui-linkbutton" data-options="iconCls:'icon-redo'">{#RESET#}</a>
			</div>
		</div>
	</div>
</div>