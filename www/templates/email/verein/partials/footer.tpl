        </td>
      </tr>

      <tr>
        <td width="100%" align="center" valign="center" class="small" bgcolor="#242424" style="padding: 2px; border-top-style: solid; border-top-width: 1px; border-top-color: #CBBA79;">
	        {if $smarty.post.topic == 'president'}Pr&auml;sidenten{elseif $smarty.post.topic == 'actuary'}Aktuars{elseif $smarty.post.topic == 'treasurer'}Kassier{/if}sache{$smarty.const.PAGETITLE_SUFFIX}
	        <img src="{$smarty.const.SITE_URL}/verein_mailer.php?mail={$mail_param}&user={$user_param}&hash={$hash_param}&path=/images/1pxl.gif">
	    </td>
      </tr>
    </table>
</body>
</html>
