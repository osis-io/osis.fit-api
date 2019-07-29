<?php

define('PROCESS', "Template/User/Delete"); /* Name of this Process */
define('LOCATION', "../../../../"); /* Location of this endpoint */        

include_once LOCATION . 'src/Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
include_once LOCATION . 'src/Security.php'; /* Load Security-Methods */
include_once LOCATION . 'src/class/Food.php';
$_Food = new Food($_DBC);


// ------------------ SCRIPT -----------------
try {

    $auth = Sec::auth();
    $_LOG->user_id = $auth->id;
    $data = Core::getData(['id']);
    
    $_Food->user_id = $auth->id;
    $_Food->id = Validate::number($data->id, 1);

    $_Food->delete();

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();