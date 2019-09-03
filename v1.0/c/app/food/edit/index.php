<?php

define('PROCESS', "App/Food/Edit"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);
    $data = Core::getBody([
        'id' => ['number', true],
        'title' => ['string', true, ['max' => 150]],
        'amount' => ['number', true],
        'caloriesPer100' => ['number', true],
        'imageID' => ['number', false]
    ]);

    require_once REC . 'Food.php';
    $Food = new Food($_DBC, $sec);
    
    if($data->imageID && $sec->premium) {
        require_once ROOT . 'Image.php';
        $Image = new Image($_DBC, $sec);
        $data->image = $Image->set(['id'=>$data->imageID])->read()->getObject();
    }
    
    $data->calories_per_100 = $data->caloriesPer100;
    $obj = $Food->set($data)->edit()->getObject();
    $obj = (object) Core::formResponse($obj);

    $_REP->addData((int) $obj->id, "id");
    $_REP->addData($obj, "item");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();