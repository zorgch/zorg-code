        </td>
      </tr>

      <tr>
        <td width="100%" align="center" valign="center" class="small" bgcolor="#242424" style="padding: 2px; border-top-style: solid; border-top-width: 1px; border-top-color: #CBBA79;">
	        {if $smarty.post.topic == 'president'}Pr&auml;sidenten{elseif $smarty.post.topic == 'actuary'}Aktuars{elseif $smarty.post.topic == 'treasurer'}Kassier{elseif $smarty.post.topic == 'eventmanager'}Event{/if}sache{$smarty.const.PAGETITLE_SUFFIX}<br>
	        <a href="{$smarty.const.SITE_URL}/page/impressum" target="_blank">Impressum</a> | <a href="https://twitter.com/{$smarty.const.TWITTER_NAME}" target="_blank">Twitter</a> | <a href="https://www.facebook.com/{$smarty.const.FACEBOOK_PAGENAME}" target="_blank">Facebook</a> | <a href="{$smarty.const.TELEGRAM_CHATLINK}" target="_blank">Telegram Chat</a>
	        <img src="{$smarty.const.SITE_URL}/verein_mailer.php?mail={$mail_param}&user={$user_param}&hash={$hash_param}&path=/images/1pxl.gif" alt="pixy">
	    </td>
      </tr>
    </table>
</body>
</html>
