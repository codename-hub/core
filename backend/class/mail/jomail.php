<?php
namespace codename\core\mail;

/**
 * Client for sending mails via jomail service
 * @package core
 * @since 2016-04-05
 */
class jomail extends \codename\core\mail implements \codename\core\mail\mailInterface {

    /**
     * Start an instance and save the data
     * @param array $config
     */
    public function __CONSTRUCT(array $config) {
        $this->client = new \codename\core\api\codename\jomail($config);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setFrom($email, $name)
     */
    public function setFrom(string $email, string $name = '') : \codename\core\mail {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addReplyTo(string $email, string $name = '') : \codename\core\mail {
      // Not implemented in this client
      return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addTo($email, $name)
     */
    public function addTo(string $email, string $name = '') : \codename\core\mail {
        $this->client->addTo($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addCc($email, $name)
     */
    public function addCc(string $email, string $name = '') : \codename\core\mail {
        $this->client->addCc($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addBcc($email, $name)
     */
    public function addBcc(string $email, string $name = '') : \codename\core\mail {
        $this->client->addBcc($email, $name);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::addAttachment($file, $newname)
     */
    public function addAttachment(string $file, string $newname = '') : \codename\core\mail {
        $this->client->addAttachment($file, ($newname == '') ? $file : $newname);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setHtml($status)
     */
    public function setHtml (bool $status = true) : \codename\core\mail {
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setSubject($subject)
     */
    public function setSubject (string $subject) : \codename\core\mail {
        $this->client->setSubject($subject);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setBody($body)
     */
    public function setBody (string $body) : \codename\core\mail {
        $this->client->setBody($body);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::setAltbody($altbody)
     */
    public function setAltbody (string $altbody) : \codename\core\mail {
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\mail_interface::send()
     * @todo Error handling (messages from the API)
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

    /**
     * Resets all the properties of this class
     * @return void
    **/
    protected function reset() {
        $this->to = array();
        $this->cc = array();
        $this->bcc = array();
        $this->subject = '';
        $this->body = '';
        $this->host = '';
        $this->port = '';
        $this->app = '';
        return;
    }

}
