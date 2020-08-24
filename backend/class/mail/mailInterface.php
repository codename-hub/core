<?php
namespace codename\core\mail;

/**
 * Definition for \codename\core\mail
 * @package core
 * @since 2016-04-05
 */
interface mailInterface {

    /**
     * Set the sender of the mail
     * @param string $email
     * @param string $name
     * @return \codename\core\mail
     */
    public function setFrom(string $email, string $name = '') : \codename\core\mail;

    /**
     * Sets/adds a reply-to mail address
     * @param  string              $email [description]
     * @param  string              $name  [description]
     * @return \codename\core\mail        [description]
     */
    public function addReplyTo(string $email, string $name = '') : \codename\core\mail;

    /**
     * Add a recipient for the mail
     * @param string $email
     * @param string $name
     * @return \codename\core\mail
     */
    public function addTo(string $email, string $name = '') : \codename\core\mail;

    /**
     * Add a Carbon Copy recipient to the mail
     * @param string $email
     * @param string $name
     * @return \codename\core\mail
     */
    public function addCc(string $email, string $name = '') : \codename\core\mail;

    /**
     * Add a Blind Carbon Copy to the mail
     * @param string $email
     * @param string $name
     * @return \codename\core\mail
     */
    public function addBcc(string $email, string $name = '') : \codename\core\mail;

    /**
     * Attach a file to the app
     * @param string $file
     * @param string $newname
     * @return \codename\core\mail
     */
    public function addAttachment(string $file, string $newname = '') : \codename\core\mail;

    /**
     * Set HTML true or false
     * @param bool $status
     * @return \codename\core\mail
     */
    public function setHtml (bool $status = true) : \codename\core\mail;

    /**
     * Set the subject of the mail
     * @param string $subject
     * @return \codename\core\mail
     */
    public function setSubject (string $subject) : \codename\core\mail;

    /**
     * Set the (html-) Body of the mail
     * @param string $body
     * @return \codename\core\mail
     */
    public function setBody (string $body) : \codename\core\mail;

    /**
     * Content displayed if the mail client does not support HTML mails
     * @param string $altbody
     * @return \codename\core\mail
     */
    public function setAltbody (string $altbody) : \codename\core\mail;

    /**
     * Send the mail
     * @return \codename\core\mail
     */
    public function send() : bool;

    /**
     * Return an error message
     * @return \codename\core\mail
     */
    public function getError();
}
