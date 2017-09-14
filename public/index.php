<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \HolidayJp\HolidayJp;
use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event;

require '../vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();

$app->get('/holiday', function (Request $request, Response $response) {
    $tz = "Asia/Tokyo";
    $vCalendar = new Calendar("default");
    $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);
    $vCalendar->setName("日本の祝日");
    $vCalendar->setDescription("日本の祝日のカレンダーです。");
    $vCalendar->setTimezone($vTimezone);

    $end = new DateTime();
    $end->modify('last day of next month');
    $holidays = HolidayJp::between(new DateTime('1978-01-23'), $end);

    foreach ($holidays as $item) {
        $event = new Event();
        $event->setDtStart($item['date'])
              ->setDtEnd($item['date'])
              ->setSummary($item['name'])
              ->setNoTime(true)
              ->setUseTimezone(true);

        $vCalendar->addComponent($event);
    }

    $body = $response->getBody();
    $body->write($vCalendar->render());

    return $response
        ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->withBody($body);
});

$app->run();
