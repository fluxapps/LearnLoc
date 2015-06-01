<?php
use LearnLocApi\AuthMiddleware;
use LearnLocApi\CoursesService;
use LearnLocApi\LocationService;
use Slim\Slim;

require 'vendor/autoload.php';

// 1. CONFIGURE SLIM
// ********************************************************************************************

$modes = array('development', 'production');
$mode = isset($_GET['mode']) && in_array($_GET['mode'], $modes) ? $_GET['mode'] : 'development';
//$mode = 'production';

$app = new Slim(array(
    'mode' => $mode
));

// Settings applied when booting in dev or prod
$settings = array(
    'development' => array(
        'debug' => true
    ),
    'production' => array(
        'debug' => false
    )
);

$app->configureMode('development', function() use ($app, $settings) {
    $app->config($settings['development']);
});

$app->configureMode('production', function() use ($app, $settings) {
    $app->config($settings['production']);
});

// Add Auth middleware which takes care of creating a session and bootstrapping ILIAS
$app->add(new AuthMiddleware());


/**
 * Helper function to return JSON
 *
 * @param array $data
 */
function response(array $data) {
    $app = Slim::getInstance();
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->body(json_encode($data));
}


// 3. ROUTES FOR THE SERVICES
// ********************************************************************************************

$app->get('/courses', function() use ($app) {
    $service = new CoursesService();
    response($service->getResponse());
});

$app->get('/locations/:id', function($id) use ($app) {
    $service = new LocationsService($id);
    response($service->getResponse());
});


$app->run();