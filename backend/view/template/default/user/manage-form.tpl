<form name="x-form">
<input type="hidden" name="id" value="{$info.id}" />
<input type="hidden" name="command" value="save" />
<table class="my-x-form-table" style="width: 100%;">
	<tr>
		<th class="br" style="width: 75px;">
			<span>登录账号</span>
		</th>
		<td>
			<input type="text" name="username" value="{$info.username}" style="width: 215px;" />
		</td>
	</tr>
	<tr>
		<th class="br">
			<span>显示名称</span>
		</th>
		<td>
			<input type="text" name="realname" value="{$info.realname}" style="width: 215px;" />
		</td>
	</tr>
	{if $info.id eq 0}
	<tr>
		<th class="br">
			<span>初始密码</span>
		</th>
		<td>
			<input type="text" name="password" value="" style="width: 215px;" />
		</td>
	</tr>
	{/if}
	<tr>
		<th class="br">
			<span>超级管理员</span>
		</th>
		<td>
			<input type="checkbox" name="issuper" value="ON" {if $info.stat & 0x80}checked{/if} />
			<span style="font-size: 10px; color: #ACACAC;">（勾选后，账号为超级管理员！）</span>
		</td>
	</tr>
</table>
</form>