{* Check & fallback for $error.type *}{if !$error.type || $error.type!='success' || $error.type!='info' || $error.type!='warn'}{assign var=$error.type value='info'}{/if}
<div class="alert {$error.type}" {if $error.dismissable == 'true'}onclick="this.style.display='none';this.style.opacity='0';setTimeout(function(){ldelim}this.style.display='none';{rdelim},600);"><span class="closebtn">&times;</span>{else}>{/if}
	{if $error.title<>'' || $error.message <>''}<strong>{if $error.title<>''}{$error.title}{else}{$error.message}{/if}</strong>{/if}
	{if $error.title<>'' && $error.message <>''}<p>{$error.message}</p>{/if}
</div>
