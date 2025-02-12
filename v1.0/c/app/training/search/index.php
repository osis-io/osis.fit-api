<?php

define('PROCESS', "App/Training/Search"); /* Name of this Process */
define('ROOT', "../../../../../src/"); /* Path to root */      
define('REC', "../../../../src/"); /* Path to classes of current version */ /* Path to root */        

require_once ROOT . 'Engine.php'; /* Load API-Engine */
Core::startAsync(); /* Start Async-Request */

// --------------- DEPENDENCIES --------------
require_once ROOT . 'Security.php'; /* Load Security-Methods */

// ------------------ SCRIPT -----------------
try {

    $sec = Sec::auth($_LOG);

    if(!$sec->premium) throw new ApiException(401, 'premium_required');

    $data = Core::getBody([
        'public' => ['boolean', false],
        'query' => ['string', false]
    ]);

    require_once REC . 'Training.php';
    $Training = new Training($_DBC, $sec);
    $items = [];    

    if (!$data->public) {

        $items = $Training->find($data->query, $sec->id, false);
        
        foreach ($items as $key => $entry) {
            $items[$key] = $Training->getSearchObject($entry);
        }
        
    } else if ($data->public) {

        $items = $Training->find($data->query, $sec->id, true);
        require_once ROOT . 'Image.php';
        $Image = new Image($_DBC, $sec);
        foreach ($items as $key => $entry) {
            $items[$key] = $Training->getSearchObject($entry, false, $Image);
        }

    }

    $_REP->addData($items, "items");

} catch (\Exception $e) { Core::processException($_REP, $_LOG, $e); }
// -------------------------------------------


// -------------- ASYNC RESPONSE -------------
Core::endAsync($_REP);

// -------------- AFTER RESPONSE -------------
$_LOG->write();