<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \HolidayJp\HolidayJp;
use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event;
use \Cake\Database\Connection;

require '../vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();

$container['db'] = function ($c) {
    return new Connection([
        'driver' => '\Cake\Database\Driver\Sqlite',
        'database' => '../sql/calendar.db'
    ]);
};

$container['view'] = function ($c) {
    return new \Slim\Views\PhpRenderer('../src/views');
};

$app->get('/', function (Request $request, Response $response) {
    return $this->view->render($response, 'index.tpl');
});

$app->get('/{calendar_id:[0-9A-Za-z_]+}.ical', function (Request $request, Response $response) {
    $calendarId = $request->getAttribute('calendar_id');

    $calendar = $this->db->newQuery()
    ->select('id, calendar_id, title, description')
    ->from('calendar')
    ->where(['calendar_id' => $calendarId])
    ->execute()
    ->fetch('assoc');

    $sth = $this->db->newQuery()
    ->select('title, description, startAt, endAt')
    ->from('event')
    ->where('calendar_id', $calendar['id'])
    ->execute();

    $tz = "Asia/Tokyo";
    $vCalendar = new Calendar("default");
    $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);
    $vCalendar->setName($calendar['title']);
    $vCalendar->setDescription($calendar['description']);
    $vCalendar->setTimezone($vTimezone);

    while ($item = $sth->fetch('assoc')) {
        $event = new Event();
        $event->setDtStart(new \DateTime($item['startAt']))
              ->setDtEnd(new \DateTime($item['endAt']))
              ->setSummary($item['title'])
              ->setUseTimezone(true);

        if ($item['startAt'] === $item['endAt']) {
            $event->setNoTime(true);
        }

        $vCalendar->addComponent($event);
    }

    $body = $response->getBody();
    $body->write($vCalendar->render());

    return $response
        //->withHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->withBody($body);
});

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

$app->get('/api/calendar', function (Request $request, Response $response) {
    $calendarId = 1;

    $sth = $this->db->newQuery()
    ->select('title, description, startAt, endAt')
    ->from('event')
    ->where('calendar_id', $calendarId)
    ->execute();

    $calendar = [];
    while ($event = $sth->fetch('assoc')) {
        $date = (new \DateTime($event['startAt']))->format("Y-m-d");
        if (!isset($calendar[$date])) {
            $calendar[$date] = [];
        }

        $calendar[$date][] = $event;
    }

    return $response->withJson($calendar);
});

$app->run();
