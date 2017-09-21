<?php


use Phinx\Seed\AbstractSeed;

class News extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'calendar_id' => 'news',
                'title' => '002 news',
                'description' => '002に関する最新のニュースをお知らせします。'
            ],
        ];

        $calendar = $this->table('calendar');
        $calendar->insert($data)
                 ->save();

        $data = [
            [
                'calendar_id' => '1',
                'title' => '生誕',
                'description' => '002の開発に着手',
                'startAt' => '2017-09-14 00:00:00',
                'endAt' => '2017-09-14 00:00:00',
            ],
        ];

        $event = $this->table('event');
        $event->insert($data)
                 ->save();
    }
}
