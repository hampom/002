<?php

namespace App\Models;

use \Eluceo\iCal\Component\Calendar;
use \Eluceo\iCal\Component\Event;

class Events
{
    public function generateEventRange(EventEntity $eventEntity): array
    {
        $events = [clone $eventEntity];

        // インターバルがある場合
        if (!empty($eventEntity->getIntervalSpec())) {
            $events = [];

            $loopDate = $eventEntity->getStartAt();
            $intervalDate = clone $loopDate;

            if ($eventEntity->getEndAt() instanceof \DateTime) {
                $intervalDate = $eventEntity->getEndAt();
            }

            if ($loopDate->format("Y-m-d") === $intervalDate->format("Y-m-d")) {
                $intervalDate = \DateTime::createFromFormat("Y-m-d", $loopDate->format("Y-m-t"));
            }

            while ($loopDate < $intervalDate) {
                $event = clone $eventEntity;
                $event->setStart($loopDate->format('Y-m-d H:i:s'));
                $event->setEnd($loopDate->format('Y-m-d H:i:s'));
                $events[] = $event;

                $loopDate->add($eventEntity->getInterval());
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
