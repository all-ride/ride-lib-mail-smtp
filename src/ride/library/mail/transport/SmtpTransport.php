<?php

namespace ride\library\mail\transport;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use ride\library\log\Log;
use ride\library\mail\exception\MailException;
use ride\library\mail\MailAddress;
use ride\library\mail\MailMessage;
use ride\library\mail\SmtpMailMessage;
use ride\library\system\file\browser\FileBrowser;

/**
 * SMTP message transport
 */
class SmtpTransport extends AbstractTransport
{
    /**
     * Instance of the PHPMailer library
     *
     * @var PHPMailer
     */
    protected $mailer;

    /**
     * @var mixed|string
     */
    protected $host;

    /**
     * @var mixed|string
     */
    protected $port;

    protected $password;

    protected $username;
    /**
     * @var boolean
     */
    protected $security;

    /**
     * Constructs a new message transport
     *
     * @return null
     */
    public function __construct(Log $log = null, $lineBreak = null)
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();

        parent::__construct($log, $lineBreak);
    }

    /**
     * Creates a mail message
     *
     * @return \ride\library\mail\MailMessage
     */
    public function createMessage(): SmtpMailMessage|MailMessage
    {
        return new SmtpMailMessage();
    }

    /**
     * Deliver a mail message via SMTP
     *
     * @param \ride\library\mail\MailMessage $message Message to send
     * @return null
     * @throws \ride\library\mail\exception\MailException when the message could
     * not be delivered
     */
    public function send(MailMessage $message)
    {
        try {
            $this->mailer->Host = $this->host;
            $this->mailer->Port = $this->port;
            $this->mailer->Username = $this->username;
            $this->mailer->Password = $this->password;
            if ($this->security) {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            }

            $this->mailer->Subject = $message->getSubject();

            // set sender
            $from = $message->getFrom();
            if (!$from && $this->defaultFrom) {
                $from = new MailAddress($this->defaultFrom);
            }

            if ($from) {
                $this->mailer->setFrom($from->getEmailAddress(), $from->getDisplayName());
            }

            // set recipient
            if (!$this->debugTo) {
                $to = $message->getTo();
                if ($to) {
                    $addresses = $this->getAddresses($to);
                    foreach ($addresses as $address) {
                        $this->mailer->addAddress($address);
                    }
                }
            } else {
                $this->mailer->addAddress($this->debugTo);
            }

            $cc = $message->getCc();
            foreach ($cc as $address) {
                $cc = $this->getAddress($address, 'cc');
                $this->mailer->addCC($cc['email'], $cc['name']);
            }

            $bcc = $message->getBcc();
            foreach ($bcc as $address) {
                $bcc = $this->getAddress($address, 'bcc');
                $this->mailer->addCC($bcc['email'], $bcc['name']);
            }

            if ($this->defaultBcc) {
                $this->mailer->addBcc($this->defaultBcc);
            }

            $replyTo = $message->getReplyTo();
            if (!$replyTo && $this->defaultReplyTo) {
                $replyTo = $this->defaultReplyTo;
            }

            if ($replyTo) {
                $this->mailer->addReplyTo($replyTo->getEmailAddress(), $replyTo->getDisplayName());
            }

            if ($message->isHtmlMessage()) {
                $this->mailer->isHTML(true);
                if ($this->debugTo) {
                    $html = '<div style="padding: 15px; margin: 25px 50px; border: 1px solid red; color: red; background-color: #FFC">';
                    $html .= 'This mail is sent in debug mode. <ul>';

                    $html .= '</div>';

                    $this->mailer->Body = $html;
                }

                $this->mailer->Body .= $message->getMessage();
            } else {
                $this->mailer->AltBody = $message->getMessage();
            }

            // add attachments
            $parts = $message->getParts();
            foreach ($parts as $name => $part) {
                if ($name === MailMessage::PART_BODY || $name === MailMessage::PART_ALTERNATIVE) {
                    continue;
                }

                $this->mailer->addStringAttachment($part->getBody(), $name);
            }

            // send the mail
            $this->mailer->send();
        } catch (Exception $exception) {
            throw new MailException('Could not send the mail : ' . $this->mailer->ErrorInfo, 0, $exception);
        }
    }


    /**
     * Gets the addresses
     *
     * @param MailAddress|array $addresses Array with MailAddress instances
     * @return array Array of address structs
     */
    protected function getAddresses($addresses): array
    {
        if (!is_array($addresses)) {
            $addresses = [$addresses];
        }

        $result = [];

        foreach ($addresses as $address) {
            $result[] = $this->getAddress($address);
        }

        return $result;
    }

    /**
     * Gets an address struct
     *
     * @param \ride\library\mail\MailAddress $address
     * @param string $type
     * @return array Address struct
     */
    protected function getAddress($address, $type = null): array
    {
        $result = [
            'email' => $address->getEmailAddress(),
        ];

        if ($address->getDisplayName()) {
            $result['name'] = $address->getDisplayName();
        }

        if ($type) {
            $result['type'] = $type;
        }

        return $result;
    }

    public function setHost($host = '127.0.0.1'): void
    {
        $this->host = $host;
    }

    public function setPort($port = '1025'): void
    {
        $this->port = $port;
    }

    public function setUsername($username = ''): void
    {
        $this->username = $username;
    }

    public function setPassword($password = ''): void
    {
        $this->password = $password;
    }

    public function setSecurity($security = false): void
    {
        $this->security = $security;
    }
}
