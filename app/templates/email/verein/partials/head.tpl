<!DOCTYPE html>
<html>
<head>{ldelim}literal{rdelim}
  <title>{$smarty.post.text_mail_subject}</title>
  {*<!--link rel="stylesheet" type="text/css" href="{$smarty.const.SITE_URL}{$smarty.const.CSS_DIR}{$smarty.post.layout}.css"-->*}
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, maximum-scale=1, minimum-scale=1, user-scalable=no"/>
  <style>
  body{ldelim}width:100%{rdelim}
  h2,h3,h4,ul,ol{ldelim}padding-top:10px;{rdelim}
  h2,h3,h4{ldelim}padding-bottom:5px;{rdelim}
  ul,ol{ldelim}padding-bottom:10px;{rdelim}
  p{ldelim}padding-top:5px;padding-bottom:5px;{rdelim}
  div p{ldelim}padding-top:10px;padding-bottom:10px;{rdelim}
  li{ldelim}margin-left:25px !important{rdelim}
  table{ldelim}font-size:12px{rdelim}
  table.header{ldelim}background-color:#000000;margin:0px{rdelim}
  table.email-body-wrap{ldelim}width:100%{rdelim}
  a{ldelim}color:#CBBA79;text-decoration:none{rdelim}
  a:hover{ldelim}text-decoration:underline{rdelim}
  div.menu{ldelim}background-color:#42300A;border-bottom-style:solid;border-bottom-color:#CBBA79;border-bottom-width:1px;border-top-style:solid;border-top-color:#FFF;border-top-width:1px;letter-spacing:1px;padding-bottom:1px;padding-top:1px{rdelim}
  div.menu a{ldelim}background-color:#42300A;border-bottom:1px solid #CBBA79;border-left:1px solid #FFF;border-right:1px solid #CBBA79;color:#CBBA79;padding-bottom:1px;padding-left:15px;padding-right:15px;padding-top:1px;text-decoration:none{rdelim}
  div.menu a:hover{ldelim}background:#62502A;text-decoration:none{rdelim}
  div.menu a.left{ldelim}background-color:#42300A;border-left-style:none;padding-left:0px;padding-right:1px{rdelim}
  div.menu a.right{ldelim}background-color:#42300A;border-right-style:none;padding-left:1px;padding-right:0px{rdelim}
  input:focus{ldelim}border-style:inset;border-color:#141414{rdelim}
  .text{ldelim}border-style:solid;border-width:1px;border-color:#CBBA79;font-size:10px;font-family:verdana;color:#FFFFFF;background-color:#000000{rdelim}
  .text:focus{ldelim}border-style:inset;border-color:#141414{rdelim}
  .button{ldelim}border-style:outset;border-width:1px;border-color:#CBBA79;font-size:11px;font-family:verdana;font-weight:bold;color:#FFFFFF;background-color:#000000{rdelim}
  .button:hover{ldelim}border-style:inset{rdelim}
  .small{ldelim}font-size:6pt !important{rdelim}
  .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td {ldelim}line-height: 100%{rdelim}
  .ExternalClass {ldelim}width: 100%{rdelim}
  </style>
  <!--[if !mso]><!-- --><style>
  body{ldelim}-webkit-text-size-adjust:100%{rdelim}
  @media only screen and (max-width : 750px) {ldelim}
    table.email-body-wrap{ldelim}min-width:300px !important{rdelim}
    div.menu{ldelim}border-top-color:#CBBA79 !important;border-bottom:none !important{rdelim}
    a.navlink{ldelim}display:block;border-left:none !important;border-right:none !important{rdelim}
    a.left,a.right{ldelim}display:none{rdelim}
    input.button{ldelim}display:block{rdelim}
  {rdelim}
  </style><!--<![endif]-->
  <!--[if gte mso 9]><style>
  li {ldelim}
    text-indent: -1em; /* Normalise space between bullets and text */
  {rdelim}
  </style><![endif]-->
{ldelim}/literal{rdelim}</head>

<body style="width: 100%; height: 100%; margin: 0px; padding: 0px; background-color:#000000;color:#FFFFFF;font-family:Verdana, Sans-Serif;font-size:12px;">
	{if $smarty.post.text_mail_description != ''}<span style="visibility:hidden;">{$smarty.post.text_mail_description}&hellip;</span>{/if}
    <table id="email-body" class="email-body-wrap" height="97%" bgcolor="#000000" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td valign="top" bgcolor="#141414" height="100%">
          <div align="center">
	        <span class="small" style="font-size:6pt">Darstellungsprobleme? Bitte Bilder laden oder <a href="{$smarty.const.SITE_URL}/verein_mailer.php?mail={$mail_param}&user={$user_param}&hash={$hash_param}">hier klicken</a> für Webansicht</span>
            <table class="header" style="height: 55px; margin: 0px; width: 100%; text-align:center;">
              <tr>
                <td align="left">
                  <h1 style="margin:0; mso-line-height-rule:exactly; padding-left:10px;"><a href="{$smarty.const.SITE_URL}" style="color: #ffffff; white-space:nowrap;">{$smarty.const.SITE_HOSTNAME}</a></h1>
                </td>

                <td align="center" style="font-size: x-small;" width="40%">
	                {foreach from=$nextevents item=nextevent}
					<a href="{$smarty.const.SITE_URL}/event/{$nextevent.startdate|date_format:"%Y/%m/%d"}/{$nextevent.id}">{$nextevent.name}
						{if $nextevent.startdate|date_format:"%d%e%Y" != $nextevent.enddate|date_format:"%d%e%Y"}
						 {$nextevent.startdate|date_format:"%d %b"}-{$nextevent.enddate|date_format:"%d %b"}
						{else}
						 {$nextevent.startdate|date_format:"%d. %b %H Uhr"}
						{/if}
					</a> <a href="{$smarty.const.SITE_URL}/actions/events.php?join={$nextevent.id}" style="color:#8274ff;">join</a><br>
					{/foreach}
                </td>

                <td align="right">
                  <table cellpadding="0" cellspacing="2">
                    <tbody>
                      <tr>
                        <td align="right">
                          <table>
                            <tbody>
                              <tr>
                                <td align="left" class="small">
                                  <form action="{$smarty.const.SITE_URL}/index.php" method="post" name="loginform">
                                    user <input tabindex="1" size="15" type="text" name="username" value="" class="text"><br>
                                    pass <input tabindex="2" size="15" type="password" name="password" class="text">&nbsp; <input tabindex="4" type="submit" value="→login" class="button">
                                  </form>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>

              <tr>
                <td align="left" colspan="3">
                  <table cellspacing="0" cellpadding="0" width="100%">
                    <tr>
                      <td align="left" class="small"></td>

                      <td class="small" align="right" valign="middle">
                        <table cellpadding="0" cellspacing="0" border="0">
                          <tr>
                            <td class="small" align="right"><a href="{$smarty.const.SITE_URL}/profil.php?do=anmeldung&amp;menu_id=13">? Login vergessen</a></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </div>

          <div align="center" valign="top" style="margin: 0px 40px;"></div>

          <div class="menu" align="center" width="100%">
            <a class="left">&nbsp;</a><a class="navlink" href="{$smarty.const.SITE_URL}/page/verein"><b>zorg Verein</b></a><a class="navlink" href="{$smarty.const.SITE_URL}/page/vereinsvorstand">Vorstand</a><a class="navlink" href="{$smarty.const.SITE_URL}/page/gv">GV</a><a class="navlink" href="{$smarty.const.SITE_URL}/page/verein-statuten">Statuten</a><a class="navlink" href="{$smarty.const.SITE_URL}/page/verein-overview">Protokolle</a><a class="navlink" href="{$smarty.const.SITE_URL}/page/vereinskonto">Konto</a><a class="right">&nbsp;</a>
          </div>