{include file="_shared/header.tpl"}
<form name="x-form">
	<table class="my-x-form-table" style="width: 100%;">
		<tr>
			<th class="br" width="125">
				<span>原密码</span>
			</th>
			<td>
				<input type="password" name="old_password" class="easyui-validatebox" data-options="required:true" style="width: 250px;" maxlength=20 />
			</td>
		</tr>
		<tr>
			<th class="br">
				<span>新密码</span>
			</th>
			<td>
				<input type="password" name="new_password" class="easyui-validatebox" data-options="required:true" style="width: 250px;" maxlength=20 />
			</td>
		</tr>
		<tr>
			<th class="br">
				<span>确认新密码</span>
			</th>
			<td>
				<input type="password" name="re_password" class="easyui-validatebox" data-options="required:true" style="width: 250px;" maxlength=20 />
			</td>
		</tr>
		<tr>
			<th class="br">&nbsp;</th>
			<td>
				<a class="easyui-linkbutton" onclick="Pro.chpassword()">确定</a>
				<a class="easyui-linkbutton" onclick="Pro.ResetForm()">重置</a>
			</td>
		</tr>
	</table>
</form>
{include file="_shared/footer.tpl"}