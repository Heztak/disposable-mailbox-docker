<?php

// check for common errors
if (version_compare(phpversion(), '7.2', '<')) {
    die("ERROR! The php version isn't high enough, you need at least 7.2 to run this application! But you have: " . phpversion());
}
extension_loaded("imap") || die('ERROR: IMAP extension not loaded. Please see the installation instructions in the README.md');


# load php dependencies:
require_once './vendor/autoload.php';
require_once './config_helper.php';
require_once './User.php';
require_once './imap_client.php';
require_once './controller.php';

load_config();

$imapClient = new ImapClient($config['imap']['url'], $config['imap']['username'], $config['imap']['password']);

if (DisplayEmailsController::matches()) {
    DisplayEmailsController::invoke($imapClient, $config);
} elseif (RedirectToAddressController::matches()) {
    RedirectToAddressController::invoke($imapClient, $config);
} elseif (RedirectToRandomAddressController::matches()) {
    RedirectToRandomAddressController::invoke($imapClient, $config);
} elseif (DownloadEmailController::matches()) {
    DownloadEmailController::invoke($imapClient, $config);
} elseif (DeleteEmailController::matches()) {
    DeleteEmailController::invoke($imapClient, $config);
} elseif (HasNewMessagesControllerJson::matches()) {
    HasNewMessagesControllerJson::invoke($imapClient, $config);
} else {
    // If requesting the main site, just redirect to a new random mailbox.
    RedirectToRandomAddressController::invoke($imapClient, $config);
}

$valid_passwords = array ("Heztak" => "elMasInsano911");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  die ("No estas autorizado Prro");
}

// delete after each request
$imapClient->delete_old_messages($config['delete_messages_older_than']);
