<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Eluceo\iCal\Component\Calendar;
use \Yosymfony\Toml\Toml;

require '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv("../");
$dotenv->load();

$app = new \Slim\App;

$container = $app->getContainer();

$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$app->get('/view/{calendar_id:[0-9A-Za-z_]+}', function (Request $request, Response $response) {
    return $this->view->render($response, 'view.tpl');
});

$app->get('/{calendar_id:[0-9A-Za-z_]+}.ical', function (Request $request, Response $response) {
    $calendarId = $request->getAttribute('calendar_id');
    $data = Toml::Parse($calendarId . ".toml");

    $tz = "Asia/Tokyo";
    $vCalendar = new Calendar("default");
    $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);
    $vCalendar->setName($calendar['title']);
    $vCalendar->setDescription($calendar['description']);
    $vCalendar->setTimezone($vTimezone);
    foreach ($data as $title => $value) {
        $eventEntity = new \App\Models\EventEntity([$title => $value]);
        $vCalendar = (new \App\Models\Events)->generateEventRangeCalendar($vCalendar, $eventEntity);
    }
    $body = $response->getBody();
    $body->write($vCalendar->render());

    return $response
        ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->withBody($body);
});

$app->get('/{calendar_id:[0-9A-Za-z]+}.json', function (Request $request, Response $response) {
    $calendarId = $request->getAttribute('calendar_id');
    $data = Toml::Parse($calendarId . ".toml");

    $events = [];
    foreach ($data as $title => $value) {
        $eventEntity = new \App\Models\EventEntity([$title => $value]);
        $events = array_merge(
            $events,
            (new \App\Models\Events)->generateEventRangeArray($eventEntity)
        );
    }
    return $response->withJson($events);
});

$app->run();
