<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \HolidayJp\HolidayJp;
use \Eluceo\iCal\Component\Calendar;
use \Cake\Database\Connection;
use Abraham\TwitterOAuth\TwitterOAuth;

require '../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv("../");
$dotenv->load();

const CALL_BACK = 'http://localhost:8081/twitter/auth';

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

$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

$container['view'] = function ($c) {
    return new \Slim\Views\PhpRenderer('../src/views');
};

$container["jwt"] = function ($container) {
    return new StdClass;
};

$app->add(new \Slim\Middleware\JwtAuthentication([
    "path" => "/api",
    "passthrough" => ["/twitter/auth"],
    "secret" => "supersecretkeyyoushouldnotcommittogithub",
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container["jwt"] = $arguments["decoded"];
    }
]));

$app->get('/twitter/auth', function (Request $request, Response $response) {
    if($this->session->exists('oauth_token') &&
       $this->session->get('oauth_token') === $request->getQueryParam('oauth_token') &&
       $request->getQueryParam('oauth_verifier')) {

        $twitter = new TwitterOAuth(
            getenv('CONSUMER_KEY'),
            getenv('CONSUMER_SECRET'),
            $this->session->get('oauth_token'),
            $this->session->get('oauth_token_secret')
        );

        $accessToken = $twitter->oauth(
            "oauth/access_token",
            [
                'oauth_verifier' => $request->getQueryParam('oauth_verifier'),
            ]
        );

        $userTwitter = new TwitterOAuth(
            getenv('CONSUMER_KEY'),
            getenv('CONSUMER_SECRET'),
            $accessToken['oauth_token'],
            $accessToken['oauth_token_secret']
        );
        $userInfo = $userTwitter->get("account/verify_credentials");

        $now = new \DateTime();
        $future = new \DateTime("now +2 hours");
        $server = $request->getServerParams();
        $jti = (new \Tuupola\Base62)->encode(random_bytes(16));

        /** @var Cake\Database\Statement\PDOStatement $sth */
        $sth = $this->db->newQuery()
            ->select('id')
            ->from('users')
            ->where(['twitter_id' => $userInfo->id])
            ->execute();

        if ($sth->rowCount() == 0) {
            /** @var Cake\Database\Statement\PDOStatement $sth */
            $sth = $this->db->insert('users', [
                'user_name' => $userInfo->screen_name,
                'twitter_id' => $userInfo->id
            ]);
            $id = $sth->lastInsertId();
        } else {
            $id = $sth->fetch('assoc')['id'];
        }

        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => $jti,
            "user_id" => $id,
        ];

        $secret = "supersecretkeyyoushouldnotcommittogithub";
        //$secret = getenv("JWT_SECRET");
        $token = \Firebase\JWT\JWT::encode($payload, $secret, "HS256");
        $data["token"] = $token;
        $data["expires"] = $future->getTimeStamp();

        return $this->view->render($response, 'loggedin.tpl', $data);
    }

    $twitter = new TwitterOAuth(getenv('CONSUMER_KEY'), getenv('CONSUMER_SECRET'));
    $tokens = $twitter->oauth("oauth/request_token", ["oauth_callback" => CALL_BACK]);

    $this->session->set('oauth_token', $tokens['oauth_token']);
    $this->session->set('oauth_token_secret', $tokens['oauth_token_secret']);

    $url = $twitter->url("oauth/authorize", ['oauth_token' => $tokens['oauth_token']]);
    return $response->withRedirect((string)$url, 302);

})->add(new \Slim\Middleware\Session([
    'name' => '002_tw_session',
    'autorefresh' => true,
    'lifetime' => '1 hour',
]));

$app->post('/api/token_refresh', function (Request $request, Response $response) {
    $now = new \DateTime();
    $future = new \DateTime("now +2 hours");
    $jti = (new \Tuupola\Base62)->encode(random_bytes(16));

    $payload = [
        "iat" => $now->getTimeStamp(),
        "exp" => $future->getTimeStamp(),
        "jti" => $jti,
        "user_id" => $this->jwt->user_id,
    ];

    $secret = "supersecretkeyyoushouldnotcommittogithub";
    //$secret = getenv("JWT_SECRET");
    $token = \Firebase\JWT\JWT::encode($payload, $secret, "HS256");
    $data["token"] = $token;
    $data["expires"] = $future->getTimeStamp();
    return $response->withStatus(201)
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});


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

$app->get('/{calendar_id:[0-9A-Za-z]+}.json', function (Request $request, Response $response) {
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
