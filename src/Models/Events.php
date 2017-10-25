<?php

namespace App\Models;

use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event;

class Events
{
    /**
     * Googleのカレンダー(ical)も標準では前後3年っぽいので、それにあわせておく
     */
    public function generateEventRange(EventEntity $eventEntity): array
    {
        $events = [clone $eventEntity];

        // インターバルがある場合
        if (!empty($eventEntity->getIntervalSpec())) {
            $events = [];

            $loopDate = $eventEntity->getStartAt();
            $intervalDate = clone $loopDate;

            // 終了日がセットされている場合は一旦セット
            // ただし、現時刻よりも3年以上先の場合は最大で3年後にするために、開始日と同じに設定しておく
            if ($eventEntity->getEndAt() instanceof \DateTime
                && $eventEntity->getEndAt() < (new \DateTime("now"))->modify("+3 year")) {
                $intervalDate = $eventEntity->getEndAt();
            }

            // 開始日と終了日が同じ場合は現時刻を基準に3年後にセットする
            if ($loopDate->format("Y-m-d") === $intervalDate->format("Y-m-d")) {
                $intervalDate = new \DateTime("now");
                $intervalDate->modify("+3 year");
            }

            while ($loopDate < $intervalDate) {
                // 除外に含まれていない場合は追加する
                if (!in_array($loopDate->format('c'), $eventEntity->getExclusion())) {
                    $event = clone $eventEntity;
                    $event->setStart($loopDate->format('Y-m-d H:i:s'));
                    $event->setEnd($loopDate->format('Y-m-d H:i:s'));
                    $events[] = $event;
                }

                $loopDate->add($eventEntity->getInterval());
            }
        }

        // 追加
        if (!empty($eventEntity->getInclusion())) {
            foreach ($eventEntity->getInclusion() as $dateTime) {
                $event = clone $eventEntity;
                $event
                    ->setStart($dateTime)
                    ->setEnd($dateTime);

                $events[] = $event;
            }
        }

        return $events;
    }

    public function generateEventRangeArray(EventEntity $eventEntity): array
    {
        $events = $this->generateEventRange($eventEntity);
        $result = [];
        foreach ($events as $event) {
            $result[] = $event->toArray();
        }

        return $result;
    }

    public function generateEventRangeCalendar(Calendar $vCalendar, EventEntity $eventEntity): Calendar
    {
        $events = $this->generateEventRange($eventEntity);
        foreach ($events as $event) {
            $vCalendar->addComponent($event->toEventObj());
        }

        return $vCalendar;
    }
}
