<div class="panel-group">
	{foreach from=$product_tabs item=tab}
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a data-toggle="collapse" href="#product_tab_{$tab.id}"{if !$tab.is_open} class="collapsed"{/if}>{$tab.name}</a>
			</h4>
		</div>
		<div id="product_tab_{$tab.id}" class="panel-collapse collapse{if $tab.is_open} in{/if}">
			<div class="panel-body">{$tab.content|unescape:"html"}</div>
		</div>
	</div>
	{/foreach}
</div>