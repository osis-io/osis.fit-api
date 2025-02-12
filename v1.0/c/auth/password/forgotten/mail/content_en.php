<?php

$Mailer->subject = "Your new Osis.fit password";

$Mailer->body = [
    "content.inner2" => '
        <br/>
        <table bgcolor="{{button.bgcolor}}" border="0" cellspacing="0" cellpadding="0">
            <tr><td align="center" height="45" class="button">
                <a href="{{button.href}}" style="color: {{button.inner.color}}">
                    {{button.inner}}
                </a>
            </td></tr>
        </table><br/><br/>
        Or use this link: <br/>
        <a href="'.Env_mail::app_url.'/auth/forgotten?mail='.$Auth->account->mail . '&code=' . $code . '">
            '.Env_mail::app_url.'/auth/forgotten?mail='.$Auth->account->mail . '&code=' . $code . '
        </a>
    ',
    "header.heading" => "Forgot something?",
    "header.subheading" => "Reset your password here",
    "content.heading" => "Hello " . $User->firstname . "!",
    "content.inner" => "
        We just received a request to reset your password.
        Have you remembered your password in the meantime? Then you can ignore this message 
        and your password stays the same. If you have not requested a password reset, 
        <a href='".Env_mail::app_url."/help/contact'>let us know</a>. <br/><br/>
    ",
    "button.inner" => "Change password &rarr;",
    "button.href" => Env_mail::app_url."/auth/forgotten?mail=" . $Auth->account->mail . "&code=" . $code
];