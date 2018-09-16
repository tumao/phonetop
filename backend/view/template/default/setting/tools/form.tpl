<form name="x-form">
<input type="hidden" name="id" value="{$info.id}" />
<table class="my-x-form-table" style="width: 100%;">
	<tr>
		<th class="br" style="width: 90px;">
			<span>工具名称</span>
		</th>
		<td>
			<input type="text" name="name" value="{$info.name}" style="width: 450px;" />
		</td>
	</tr>
	<tr>
		<td colspan="2" style="height: 80px; text-align: center;">
			<img id="viewbox" src="{$info.logo|default: '/css/easy-ui/theme/images/blank.gif'}" style="width: 205px; height: 68px;" />
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>LOGO图片</span>
		</th>
		<td>
			<input type="text" name="logo" value="{$info.logo}" style="width: 420px;" />
			<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="$.explorer.open({ldelim}handler: Pro.setimglogo{rdelim})"></a>
		</td>
	</tr>
	<tr>
		<th class="br">&nbsp;</th>
		<td>
			<span style="color: #ACACAC;">&nbsp;&raquo;&nbsp;先填写进程名，再选下载包时，会自动生成进程文件的MD5值！</span>
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>进程名称</span>
		</th>
		<td>
			<input type="text" name="proc" value="{$info.proc}" style="width: 450px;" />
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>进程MD5</span>
		</th>
		<td>
			<input type="text" name="md5proc" value="{$info.md5proc}" style="width: 450px;" />
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>下载地址</span>
		</th>
		<td>
			<input type="text" name="url" value="{$info.url}" style="width: 420px;" />
			<a href="javascript: void(0);" class="easyui-linkbutton" iconCls="icon-search" plain="true" onclick="$.explorer.open({ldelim}handler: Pro.setdownloadurl{rdelim})"></a>
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>工具包MD5</span>
		</th>
		<td>
			<input type="text" name="md5" value="{$info.md5}" style="width: 450px;" />
		</td>
	</tr>
</table>
</form>