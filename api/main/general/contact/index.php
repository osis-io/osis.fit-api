<?php

define('PROCESS', "General/Contact"); /* Name of this Process */
define('LOCATION', "../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_config/Mail.php'; 
$_ContactMail = new Mail();
$_InfoMail = new Mail();


// ------------------ SCRIPT -----------------
try {

    $data = Core::getData(['subject', 'message', 'mail']);

    $subject = Validate::string($data->subject);
    $message = Validate::string($data->message);
    $mail = Validate::mail($data->mail);
    $_LOG->identity = $mail;

    $firstname = (isset($data->firstname) ? (Validate::string($data->firstname, 0)) : '');
    $lastname = (isset($data->lastname) ? (Validate::string($data->lastname, 0)) : '');
    $lang = (isset($data->language) ? (Validate::string($data->language, 0, 5)) : 'en');

    $authUser = Sec::auth(false);
    $_LOG->user_id = ($authUser ? $authUser->id : 'none');

    include_once 'ContactMail.php';
    $_ContactT = new ContactMail();
    $_ContactMail->from_adress = $mail;
    $_ContactMail->from_name = $firstname." ".$lastname;
    $_ContactMail->addReceiver("mail@eliareutlinger.ch", "Elia", "Reutlinger");
    $_ContactMail->setTemplate($_ContactT);
    $_ContactMail->setContent("subject", [
        "subject" => $subject,
    ]);
    $_ContactMail->setContent("body", [
        "firstname" => $firstname, 
        "lastname" => $lastname, 
        "mail" => $mail, 
        "subject" => $subject,
        "message" => $message,
        "language" => $lang,
        "userid" => $_LOG->user_id,
    ]);
    $_ContactMail->send();

    $_LOG->addInfo('Contact Mail has been sent.');

    include_once 'InfoMail.php';
    $_InfoT = new InfoMail();
    $_InfoMail->addReceiver($mail, $firstname, $lastname);
    $_InfoMail->setTemplate($_InfoT);
    $_InfoMail->setContent("body", [
        "firstname" => $firstname, 
        "lastname" => $lastname, 
        "mail" => $mail, 
        "subject" => $subject,
        "message" => $message
    ]);
    $_InfoMail->send();

    $_LOG->addInfo('Info-Mail has been sent.');
    
} catch (\Exception $e) {
    $_REP->setStatus((($e->getCode()) ? $e->getCode() : 500), $e->getMessage());
    $_LOG->setStatus('fatal', "(".(($e->getCode()) ? $e->getCode() : 500).") Catched: | ".$e->getMessage()." | ");
}
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
$_REP->send();
Core::endAsync(); /* End Async-Request */

// -------------- AFTER RESPONSE -------------
$_LOG->write();