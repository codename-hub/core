<?php

namespace codename\core\mail;

use codename\core\mail;

/**
 * Definition for \codename\core\mail
 * @package core
 * @since 2016-04-05
 */
interface mailInterface
{
    /**
     * Set the sender of the mail
     * @param string $email
     * @param string $name
     * @return mail
     */
    public function setFrom(string $email, string $name = ''): mail;

    /**
     * Sets/adds a reply-to mail address
     * @param string $email [description]
     * @param string $name [description]
     * @return mail        [description]
     */
    public function addReplyTo(string $email, string $name = ''): mail;

    /**
     * Add a recipient for the mail
     * @param string $email
     * @param string $name
     * @return mail
     */
    public function addTo(string $email, string $name = ''): mail;

    /**
     * Add a Carbon Copy recipient to the mail
     * @param string $email
     * @param string $name
     * @return mail
     */
    public function addCc(string $email, string $name = ''): mail;

    /**
     * Add a Blind Carbon Copy to the mail
     * @param string $email
     * @param string $name
     * @return mail
     */
    public function addBcc(string $email, string $name = ''): mail;

    /**
     * Attach a file to the app
     * @param string $file
     * @param string $newname
     * @return mail
     */
    public function addAttachment(string $file, string $newname = ''): mail;

    /**
     * Set HTML true or false
     * @param bool $status
     * @return mail
     */
    public function setHtml(bool $status = true): mail;

    /**
     * Set the subject of the mail
     * @param string $subject
     * @return mail
     */
    public function setSubject(string $subject): mail;

    /**
     * Set the (html-) Body of the mail
     * @param string $body
     * @return mail
     */
    public function setBody(string $body): mail;

    /**
     * Content displayed if the mail client does not support HTML mails
     * @param string $altbody
     * @return mail
     */
    public function setAltbody(string $altbody): mail;

    /**
     * Send the mail
     * @return bool
     */
    public function send(): bool;

    /**
     * Return an error message
     * @return mixed
     */
    public function getError(): mixed;
}
