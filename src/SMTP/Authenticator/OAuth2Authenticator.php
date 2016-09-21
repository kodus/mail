<?php

namespace Kodus\Mail\SMTP\Authenticator;

class OAuth2Authenticator
{
    // TODO

    /**
     * SMTP AUTH XOAUTH2
     * SUCCESS 235
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authXOAuth2()
    {
        // TODO this method is unused - add support for OAUTH 2.0 authentication?

        $auth_str = sprintf("user=%s%sauth=Bearer %s%s%s",
            $this->message->getFromEmail(),
            chr(1),
            $this->oauth_token,
            chr(1),
            chr(1)
        );

        $auth_str = base64_encode($auth_str);

        $code = $this->writeCommand("AUTH XOAUTH2 {$auth_str}");

        if ($code !== '235') {
            throw new CodeException('235', $code, array_pop($this->result_stack));
        }
    }
}
