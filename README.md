# Ride: Mail (SMTP)

Integration of the SMTP implementation of the mail library with a Ride application.

__This package is mainly used for local development in coop with DDEV.__

This lets you send mails to Mailpit during local development and makes it easier to debug

## Parameters

* __mail.smtp.host__: "127.0.0.1", SMTP server to send through
* __mail.smtp.port__: "1025", Port to connect to, use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
* __mail.smtp.security__ : false, Enable implicit TLS encryption
* __mail.smtp.username__ : "", SMTP username,
* __mail.smtp.password__ : "", SMTP password

## Related Modules

- [ride/app](https://github.com/all-ride/ride-app)
- [ride/app-mail](https://github.com/all-ride/ride-app-mail)
- [ride/lib-mail](https://github.com/all-ride/ride-lib-mail)
- [phpmailer/phpmailer](https://github.com/PHPMailer/PHPMailer)

## Installation

You can use [Composer](http://getcomposer.org) to install this application.

```
composer require ride/lib-mail-smtp
```

Don't forget to add the parameters for `Host`, `Port` &  `Security`
These 3 parameters are required
