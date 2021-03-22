{if $smarty.post.topic == 'president'}{assign var=sender value=117}{elseif $smarty.post.topic == 'actuary'}{assign var=sender value=11}{elseif $smarty.post.topic == 'treasurer'}{assign var=sender value=52}{elseif $smarty.post.topic == 'eventmanager'}{assign var=sender value=713}{/if}
            {if $sender > 0}<div style="padding:0 15px 0 15px;text-align:center;">
	            <a href="{$smarty.const.SITE_URL}/user/{$sender}"><img border="0" src="{$smarty.const.SITE_URL}/data/userimages/{$sender}_tn.jpg" alt="{$smarty.post.topic}" title="{$sender|name}"></a>
                <h3 style="margin:0; mso-line-height-rule:exactly;">
	                <a href="{$smarty.const.SITE_URL}/user/{$sender}">{$sender|name}</a>
	            </h3>
            </div>{/if}