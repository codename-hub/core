<?php

namespace codename\core\mail;

use codename\core\mail;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailing client for SMTP transport of mails
 * @package core
 * @since 2016-04-05
 * @todo check renaming to lowercase class and filename!
 */
class PHPMailer extends mail implements mailInterface
{
    /**
     * Creates the instance using the given $config
     * @param array $config
     * @return PHPMailer
     */
    public function __construct(array $config)
    {
        // NOTE: PHPMailer v6+ is namespaced
        $this->client = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Use SMTP Mode
        $this->client->isSMTP();
        $this->client->Host = $config['host'];
        $this->client->Port = $config['port'];
        $this->client->SMTPSecure = $config['secure'];

        $this->client->CharSet = 'UTF-8';
        $this->client->XMailer = 'core Mailer';
        $this->client->SMTPAuth = $config['auth'];

        //disable ssl verification
        // http://stackoverflow.com/questions/26827192/phpmailer-ssl3-get-server-certificatecertificate-verify-failed
        $this->client->SMTPOptions = [
          'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
          ],
        ];

        if ($this->client->SMTPAuth) {
            $this->client->Username = $config['user'];
            $this->client->Password = $config['pass'];
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $email
     * @param string $name
     * @return mail
     * @throws Exception
     * @see \codename\core\mail_interface::setFrom($email, $name)
     */
    public function setFrom(string $email, string $name = ''): mail
    {
        $this->client->setFrom($email, $name);
        return $this;
    }

    /**
     * {@inheritDoc}
     * @param string $email
     * @param string $name
     * @return mail
     * @throws Exception
     */
    public function addReplyTo(string $email, string $name = ''): mail
    {
        $this->client->addReplyTo($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $email
     * @param string $name
     * @return mail
     * @throws Exception
     * @see \codename\core\mail_interface::addTo($email, $name)
     */
    public function addTo(string $email, string $name = ''): mail
    {
        $this->client->addAddress($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $email
     * @param string $name
     * @return mail
     * @throws Exception
     * @see \codename\core\mail_interface::addCc($email, $name)
     */
    public function addCc(string $email, string $name = ''): mail
    {
        $this->client->addCC($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $email
     * @param string $name
     * @return mail
     * @throws Exception
     * @see \codename\core\mail_interface::addBcc($email, $name)
     */
    public function addBcc(string $email, string $name = ''): mail
    {
        $this->client->addBCC($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param string $file
     * @param string $newname
     * @return mail
     * @throws Exception
     * @see \codename\core\mail_interface::addAttachment($file, $newname)
     */
    public function addAttachment(string $file, string $newname = ''): mail
    {
        $this->client->addAttachment($file, $newname);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setHtml($status)
     */
    public function setHtml(bool $status = true): mail
    {
        $this->client->isHTML($status);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setSubject($subject)
     */
    public function setSubject(string $subject): mail
    {
        $this->client->Subject = $subject;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setBody($body)
     */
    public function setBody(string $body): mail
    {
        $this->client->Body = $body;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setAltbody($altbody)
     */
    public function setAltbody(string $altbody): mail
    {
        $this->client->AltBody = $altbody;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @return bool
     * @throws Exception
     * @see \codename\core\mail_interface::send()
     */
    public function send(): bool
    {
        return $this->client->send();
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::getError()
     */
    public function getError(): mixed
    {
        return $this->client->ErrorInfo;
    }
}
