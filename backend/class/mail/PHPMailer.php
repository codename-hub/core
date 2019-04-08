<?php
namespace codename\core\mail;

/**
 * Mailing client for SMTP transport of mails
 * @package core
 * @since 2016-04-05
 * @todo check renaming to lowercase class and filename!
 */
class PHPMailer extends \codename\core\mail implements \codename\core\mail\mailInterface {

    /**
     * [protected description]
     * @var \PHPMailer\PHPMailer\PHPMailer
     */
    protected $client;

    /**
     * Creates the instance using the given $config
     * @param array $config
     * @return \codename\core\mail\PHPMailer
     */
    public function __CONSTRUCT(array $config) {

        // NOTE: PHPMailer v6+ is namespaced
        $this->client = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Use SMTP Mode
        $this->client->IsSMTP();
        $this->client->Host = $config['host'];
        $this->client->Port = $config['port'];
        $this->client->SMTPSecure = $config['secure'];

        $this->client->CharSet = 'UTF-8';
        $this->client->XMailer = 'core Mailer';
        $this->client->SMTPAuth = $config['auth'];

        //disable ssl verification
        // http://stackoverflow.com/questions/26827192/phpmailer-ssl3-get-server-certificatecertificate-verify-failed
        $this->client->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        if($this->client->SMTPAuth) {
            $this->client->Username = $config['user'];
            $this->client->Password = $config['pass'];
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setFrom($email, $name)
     */
    public function setFrom(string $email, string $name = '') : \codename\core\mail {
        $this->client->setFrom($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addTo($email, $name)
     */
    public function addTo(string $email, string $name = '') : \codename\core\mail {
        $this->client->addAddress($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addCc($email, $name)
     */
    public function addCc(string $email, string $name = '') : \codename\core\mail {
        $this->client->addCC($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addBcc($email, $name)
     */
    public function addBcc(string $email, string $name = '') : \codename\core\mail {
        $this->client->addBCC($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addAttachment($file, $newname)
     */
    public function addAttachment(string $file, string $newname = '') : \codename\core\mail {
        $this->client->addAttachment($file, $newname);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setHtml($status)
     */
    public function setHtml (bool $status = true) : \codename\core\mail {
        $this->client->isHTML($status);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setSubject($subject)
     */
    public function setSubject (string $subject) : \codename\core\mail {
        $this->client->Subject = $subject;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setBody($body)
     */
    public function setBody (string $body) : \codename\core\mail {
        $this->client->Body = $body;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setAltbody($altbody)
     */
    public function setAltbody (string $altbody) : \codename\core\mail {
        $this->client->AltBody = $altbody;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::send()
     */
    public function send() : bool {
        return $this->client->send();
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::getError()
     */
    public function getError() {
        return $this->client->ErrorInfo;
    }

}
