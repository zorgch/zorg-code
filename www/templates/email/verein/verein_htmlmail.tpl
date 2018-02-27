{include file='file:email/verein/partials/head.tpl'}
        <div valign="top" style="max-width:580px; display:block; margin:auto; padding:25px;">

            <h1 style="margin:0; mso-line-height-rule:exactly; text-align:center; padding-bottom:10px;">{$smarty.post.text_mail_subject}</h1>

            {eval var=$smarty.post.text_mail_message assign="mail_body"}
            {$mail_body}
            {*$smarty.post.text_mail_message <-- THIS WON'T PARSE SMARTY TAGS IN text_mail_message*}

			{include file='file:email/verein/elements/block_sender.tpl'}
        </div>
{include file='file:email/verein/partials/footer.tpl'}
