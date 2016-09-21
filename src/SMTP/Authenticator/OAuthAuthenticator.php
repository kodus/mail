<?php

namespace Kodus\Mail\SMTP\Authenticator;

class OAuthAuthenticator
{
    // TODO

    /**
     * SMTP AUTH OAUTHBEARER
     * SUCCESS 235
     *
     * @throws CodeException
     * @throws SMTPException
     */
    protected function authOAuthBearer()
    {
        $auth_str = sprintf("n,a=%s,%shost=%s%sport=%s%sauth=Bearer %s%s%s",
            $this->message->getFromEmail(),
            chr(1),
            $this->host,
            chr(1),
            $this->port,
            chr(1),
            $this->oauth_token,
            chr(1),
            chr(1)
        );

        $auth_str = base64_encode($auth_str);

        $code = $this->writeCommand("AUTH OAUTHBEARER {$auth_str}");

        if ($code !== '235') {
            throw new CodeException('235', $code, array_pop($this->result_stack));
        }
    }
}