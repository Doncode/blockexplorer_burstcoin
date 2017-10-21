<?php
// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Berlin');

// Paths
define('DS', DIRECTORY_SEPARATOR);
define('DIR_SYSTEM', __DIR__.'/../');
define('DIR_PUBLIC', __DIR__.'/../public_html/');

// Routing
define('ROUTING_MAIN', 'blocks.fastpool.info');
define('ROUTING_API', 'blocks.fastpool.info');

// Smarty
define('SMARTY_SPL_AUTOLOAD', 1);

// Kompression
define('COMPRESSION', true);
define('COMPRESSION_LEVEL', 5);
define('COMPRESSION_HTML', false);

// Datenbank
define('DB_SERVER', '127.0.0.1');
define('DB_PORT', PORT);
define('DB_NAME', 'DB_NAME');
define('DB_USER', 'ROOT');
define('DB_PASSWORD', 'PASSPHRASE');
define('DB_PRE', 'b_');
define('DB_CHARSET', 'utf8');

// Internationalisierung
define('LANGUAGE', true);
define('LANGUAGES', 'en,de,ru');
define('LANGUAGE_DEFAULT', 'en');

// Mailer
define('SMTP_HOST', 'domain.com');
define('SMTP_PORT', 25);
define('SMTP_USER', 'info@domain.com');
define('SMTP_PASSWORD', 'mailbox_pass');
define('MAIL_FROM', 'info@domain.com');
define('MAIL_FROM_NAME', 'domain.com Support');
define('MAIL_REPLY', 'info@domain.com');
define('MAIL_REPLY_NAME', 'Domain.com Support');

// Kontakt
define('CONTACT_FORM', 'info@fastpool.info');

// Adresse der Burst API
define('BURST_API', '127.0.0.1');

// TS Burst Start
define('BURST_EXIST', 1407722400);

// Faucet
define('FAUCET_PW', 'faucet_wallet_pass');
define('FAUCET_ID', '13031614675276222682');
define('FAUCET_RC', 'BURST-QN8U-J4ST-RVTC-DSEQB');

// Surfbar
define('SURFBAR_API', 'ebesucher_username:ebesucher_api_key');
define('SURFBAR_VALUE', 0.0000190); // Wert in Euro pro SP
define('SURFBAR_PW', 'surfbar_wallet_pass');

//define('RECAPTCHA_PUBLIC', '-------');
//define('RECAPTCHA_SECRET', '-------');

define('COINHIVE_COM_PUBLIC', '-------');
define('COINHIVE_COM_SECRET', '-------');
define('COINHIVE_COM_HASHES', 4096);
