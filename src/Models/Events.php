<?php

namespace App\Models;

use \Cake\Database\StatementInterface;
use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event;

class Events extends Model
{
    /**
     * @param int $id
     * @return StatementInterface
     */
    public function getAllByCalendarId(int $id): StatementInterface
    {
        return $this->db->newQuery()
            ->select('id, title, description, startAt, endAt, interval')
            ->from('event')
            ->where(['calendar_id' => $id])
            ->execute();
    }

    /**
     * @param StatementInterface $sth
     * @return array
     */
    public function convertArray(StatementInterface $sth): array
    {
        $calendar = [];
        while ($event = $sth->fetch('assoc')) {
            // 自身のイベント情報を保存する
            $event['date'] = (new \DateTime($event['startAt']))->format("Y-m-d");
            $calendar[] = $event;

            // インターバルがある場合
            if (!empty($event['interval'])) {
                $loopDate = new \DateTime($event['startAt']);
                $intervalDate = clone $loopDate;

                $interval = new \DateInterval($event['interval']);
                $intervalDate->add(new \DateInterval("P1Y"));
                while ($loopDate < $intervalDate) {
                    $loopDate->add($interval);

                    $event['date'] = $loopDate->format("Y-m-d");
                    $event['startAt'] = $loopDate->format('Y-m-d H:i:s');
                    $event['endAt'] = $loopDate->format('Y-m-d H:i:s');
                    $calendar[] = $event;
                }
            }
        }

        return $calendar;
    }

    /**
     * @param StatementInterface $sth
     * @param Calendar $vCalendar
     * @return Calendar
     */
    public function convertVcalendar(StatementInterface $sth, Calendar $vCalendar): Calendar
    {
        $tmpArray = $this->convertArray($sth);

        foreach ($tmpArray as $item) {
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

        return $vCalendar;
    }
}
