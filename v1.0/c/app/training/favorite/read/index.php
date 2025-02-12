<?php

define('PROCESS', "App/Training/Favorite/Read"); /* Name of this Process */
define('ROOT', "../../../../../../src/"); /* Path to root */      
define('REC', "../../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    if(!$sec->premium) throw new ApiException(401, 'premium_required');

    $data = Core::getBody(['id' => ['number', false]]);

    require_once REC . 'Training.php';
    $Training = new Training($_DBC, $sec);
    if($data->id) {
        $obj = $Training->getFavorite($data->id);
    } else {
        $obj = $Training->getFavorite();
    }
    
    $_REP->addData($obj, "items");
    
} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();