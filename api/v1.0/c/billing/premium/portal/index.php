<?php

define('PROCESS', "Billing/Premium/Portal"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/class/"); /* Path to classes of current version */ /* Path to root */           

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    require_once ROOT . 'Authentication.php';
    $Auth = new Auth($_DBC, ["mail" => $sec->mail]);
    if ($Auth->check()->status !== "verified") throw new ApiException(403, 'not_verified');

    require_once ROOT . 'Billing.php';  
    $Billing = new Billing($_DBC, $sec);  
    $output = $Billing->newPortal();

    $_REP->addData($output, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();