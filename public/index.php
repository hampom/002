<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \HolidayJp\HolidayJp;
use \Eluceo\iCal\Component\Calendar;
use \Cake\Database\Connection;

require '../vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();

$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    return new Connection([
        'driver' => '\Cake\Database\Driver\Sqlite',
        'database' => '../sql/calendar.db'
    ]);
};

$container['view'] = function ($c) {
    return new \Slim\Views\PhpRenderer('../src/views');
};

$app->get('/view/{calendar_id:[0-9A-Za-z_]+}', function (Request $request, Response $response) {
    return $this->view->render($response, 'view.tpl');
});

$app->get('/{calendar_id:[0-9A-Za-z_]+}.ical', function (Request $request, Response $response) {
    $calendarId = $request->getAttribute('calendar_id');

    $Calendar = new \App\Models\Calendar($this->db);
    $sth = $Calendar->getById($calendarId);

    if ($sth->rowCount() == 0) {
        return $response->withStatus(404);
    }

    $calendar = $sth->fetch('assoc');

    $tz = "Asia/Tokyo";
    $vCalendar = new Calendar("default");
    $vTimezone = new \Eluceo\iCal\Component\Timezone($tz);
    $vCalendar->setName($calendar['title']);
    $vCalendar->setDescription($calendar['description']);
    $vCalendar->setTimezone($vTimezone);

    $Events = new \App\Models\Events($this->db);
    $sth = $Events->getAllByCalendarId($calendar['id']);
    $vCalendar = $Events->convertVcalendar($sth, $vCalendar);

    $body = $response->getBody();
    $body->write($vCalendar->render());

    return $response
        ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->withBody($body);
});

$app->get('/api/calendar/{calendar_id:[0-9A-Za-z]+}', function (Request $request, Response $response) {
    $calendarId = $request->getAttribute('calendar_id');

    $Calendar = new \App\Models\Calendar($this->db);
    $sth = $Calendar->getById($calendarId);

    if ($sth->rowCount() == 0) {
        return $response->withStatus(404);
    }

    $id = $sth->fetch('assoc')['id'];
    $Events = new \App\Models\Events($this->db);
    $sth = $Events->getAllByCalendarId($id);
    $tmp = $Events->convertArray($sth);

    $calendar = [];
    foreach ($tmp as $v) {
        if (! isset($calendar[$v['date']])) {
            $calendar[$v['date']] = [];
        }

        $calendar[$v['date']][] = $v;
    }

    return $response->withJson($calendar);
});

$app->post('/api/calendar/{calendar_id:[0-9A-Za-z]+}', function (Request $request, Response $response) {
    $calendarId = $request->getAttribute('calendar_id');

    $Calendar = new \App\Models\Calendar($this->db);
    $sth = $Calendar->getById($calendarId);

    if ($sth->rowCount() === 0) {
        return $response->withStatus(404);
    }

    $id = $sth->fetch('assoc')['id'];

    $date = $request->getParsedBodyParam("date");
    $title = $request->getParsedBodyParam("title");
    $intervalSetting = $request->getParsedBodyParam("interval_setting");
    $intervalNum = $request->getParsedBodyParam("interval_num");
    $this->logger->addInfo(var_export($request->getParsedBody(), true));

    $data = [
        'calendar_id' => $id,
        'startAt' => $date . " 00:00:00",
        'endAt' => $date . " 00:00:00",
        'title' => $title,
    ];

    if (!empty($intervalSetting) && !empty($intervalNum)) {
        $data['interval'] = sprintf("P%d%s", $intervalNum, $intervalSetting);
    }

    $this->db->insert('event', $data);

    return $response->withJson([]);
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

$app->run();
