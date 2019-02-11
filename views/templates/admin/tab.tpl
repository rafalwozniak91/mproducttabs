<div class="panel">

	<div class="panel-heading">
		{l s='Product tabs' mod='mproducttabs'}
	</div>


	<div class="tabs">

		{foreach from=$productTabs item=tab}

		<fieldset class="panel-body tab">

		<div class="row">
			<input type="hidden" name="id_tabs[{$tab.id}]" value="{$tab.id}"/>
			<input type="hidden" name="method[{$tab.id}]" value="{if $tab.content}update{else}add{/if}"/>
			
			{if $tab.content} 
				<input type="hidden" name="id_tab_content[{$tab.id}]" value="{$tab.id_tab_content}"/>
			{/if}

			<div class="col-md-1 col-xs-12">
				<input type="checkbox" name="tab[{$tab.id}]" id="tab_{$tab.id}" value="{$tab.id}"{if $tab.content} checked{/if}/>
				<label for="tab_{$tab.id}">{$tab.name}</label>
			</div>

			<div class="col-md-6 col-xs-12">
				<textarea class= "autoload_rte" name="content[{$tab.id}]" id="content_{$tab.id}" cols="30" rows="10">
					{$tab.content}
				</textarea>
			</div>

		</div>
		</fieldset>

		{/foreach}

	</div>


	<div class="panel-footer">
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and Stay'}</button>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save'}</button>
	</div>

</div>




<script type="text/javascript">
	tinySetup({
		editor_selector :"autoload_rte",
		relative_urls : false,
		plugins : "colorpicker link image paste pagebreak table contextmenu filemanager table code media autoresize textcolor fullpage",
		verify_html : false, 
		extended_valid_elements : "em[class|name|id]",
        valid_children : "+body[style], +style[type]",
	});
</script>