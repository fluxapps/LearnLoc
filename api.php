<?php
use LearnLocApi\AuthMiddleware;
use LearnLocApi\CampusTourService;
use LearnLocApi\CommentsService;
use LearnLocApi\CoursesService;
use LearnLocApi\CreateCommentService;
use LearnLocApi\CreateLocationService;
use LearnLocApi\DependencyService;
use LearnLocApi\LocationImageService;
use LearnLocApi\LocationsService;
use LearnLocApi\CommentImageService;
use LearnLocApi\PingService;
use Slim\Slim;

require 'vendor/autoload.php';

// 1. CONFIGURE SLIM
// ********************************************************************************************

$modes = array(
	'development',
	'production'
);
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

$app->configureMode('development', function () use ($app, $settings) {
	$app->config($settings['development']);
});

$app->configureMode('production', function () use ($app, $settings) {
	$app->config($settings['production']);
});

// Add Auth middleware which takes care of creating a session and bootstrapping ILIAS
$app->add(new AuthMiddleware());

$env = $app->environment();
if ($_REQUEST['get_request'] === 'true') {
	$env['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'GET';
}


// Disable CORS
$app->response->headers->set('Access-Control-Allow-Origin', '*');

// 2. HELPERS
// ********************************************************************************************

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

/**
 * @param string $image_path
 */
function imageResponse($image_path) {
	$app = Slim::getInstance();
	if (! is_file($image_path)) {
		$app->response->setStatus(404);
	} else {
		$app->response->headers()->set('Content-Type', 'image/jpeg');
		$app->response->headers()->set('Content-Length', filesize($image_path));
		$app->response->body(file_get_contents($image_path));
	}
}

// 3. ROUTES FOR THE SERVICES
// ********************************************************************************************

$app->get('/', function () use ($app) {
	response(array(
		'GET /ping' => 'is Alive?',
		'GET /courses' => 'List of courses',
		'GET /course/:id/locations' => 'Returns all locations (and folders) of the given course ref-ID',
		'GET /location/:id/image' => 'Returns the location image',
		'GET /location/:id/thumb' => 'Returns a thumbnail version of the location image',
		'GET /location/:id/header' => 'Returns a header version of the location image',
		'GET /campusTour' => 'Returns all locations (and folders) from the campus tour',
		'GET /location/:id/comments' => 'Returns all comments for the location. Pass GET-Parameters "start" and "count" for paging',
		'GET /comment/:id/image' => 'Returns image of comment',
		'GET /comment/:id/thumb' => 'Returns thumbnail image of comment',
		'POST /location/:id/comment' => 'Create a new Comment. Post parameters (url-encoded): title, body, image, parent_id',
		'POST /course/:id' => 'Create a new location under the given course (Ref-ID). Post parameters (url-encoded): title, description, latitude, longitude, image, address'
	));
});

$app->get('/ping', function () use ($app) {
	$service = new PingService();
	response($service->getResponse());
});

$app->get('/courses', function () use ($app) {
	$service = new CoursesService();
	response($service->getResponse());
});

$app->get('/allCourses', function () use ($app) {
	$service = new CoursesService(0, true);
	response($service->getResponse());
});

$app->get('/course/:id/locations', function ($id) use ($app) {
	$service = new LocationsService($id);
	response($service->getResponse());
});

$app->get('/location/:id/dependencies', function ($id) use ($app) {
	$service = new DependencyService($id);
	response($service->getResponse());
});

$app->get('/location/:id/image', function ($id) use ($app) {
	$service = new LocationImageService($id, array(
		'w' => 960,
		'h' => 960,
		'crop' => false
	));
	imageResponse($service->getResponse());
});

$app->get('/location/:id/header', function ($id) use ($app) {
	$service = new LocationImageService($id, array(
		'w' => 960,
		'h' => 260,
		'crop' => false
	));
	imageResponse($service->getResponse());
});

$app->get('/location/:id/thumb', function ($id) use ($app) {
	$service = new LocationImageService($id, array(
		'w' => 64,
		'h' => 64,
		'crop' => true
	));
	imageResponse($service->getResponse());
});

$app->get('/campusTour', function () use ($app) {
	$service = new CampusTourService();
	response($service->getResponse());
});

$app->get('/location/:id/comments', function ($id) use ($app) {
	$start = $app->request()->get('start') ? (int)$app->request()->get('start') : 0;
	$count = $app->request()->get('count') ? (int)$app->request()->get('count') : 100;
	$service = new CommentsService($id, $start, $count);
	response($service->getResponse());
});

$app->get('/comment/:id/thumb', function ($id) use ($app) {
	$service = new CommentImageService($id, array(
		'w' => 64,
		'h' => 64,
		'crop' => true
	));
	imageResponse($service->getResponse());
});

$app->get('/comment/:id/image', function ($id) use ($app) {
	$service = new CommentImageService($id, array(
		'w' => 960,
		'h' => 960,
		'crop' => false
	));
	imageResponse($service->getResponse());
});

$app->post('/location/:id/comment', function ($id) use ($app) {
	$parent_id = $app->request()->post('parent_id');
	$data = array(
		'title' => $app->request()->post('title'),
		'body' => $app->request()->post('body'),
		'image' => $app->request()->post('image'),
	);
	$service = new CreateCommentService($id, $parent_id, $data);
	response($service->getResponse());
});

$app->post('/course/:id', function ($id) use ($app) {
	$data = array(
		'title' => $app->request()->post('title'),
		'description' => $app->request()->post('description'),
		'image' => $app->request()->post('image'),
		'latitude' => $app->request()->post('latitude'),
		'longitude' => $app->request()->post('longitude'),
		'address' => $app->request()->post('address'),
	);
	$service = new CreateLocationService($id, $data);
	response($service->getResponse());
});

$app->run();