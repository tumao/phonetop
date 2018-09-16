		{html_options name=_selected_system options=$system_cat selected=$_selected_system class="system" onchange="$.toolsearch.breakswap(this)"}
		<select name="isbreak" class="isbreak" {if $_selected_system neq 'ios'}style="display: none;"{/if}>
			<option value="Y" {if $_selected_isbreak eq 1}selected{/if}>越狱</option>
			<option value="N" {if $_selected_isbreak eq 0}selected{/if}>末越狱</option>
		</select>