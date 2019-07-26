<?php

define('PROCESS', "Log/Weight/Delete"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION.'_config/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION.'_config/Security.php'; /* Load Security-Methods */
include_once LOCATION.'_objects/logs/WeightLog.php';
$_wLog = new WeightLog($_DBC);


// ------------------ SCRIPT -----------------
try {

    $authUser = Sec::auth();
    $_LOG->user_id = $authUser->id;        
    $data = Core::getData(['id']);

    $_wLog->user_id = $authUser->id;
    $_wLog->id = Validate::number($data->id, 1);

    $_wLog->delete();

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