<?php

namespace App\Models;

use \Eluceo\iCal\Component\Event;
use \Eluceo\iCal\Component\Calendar;

class EventEntity
{
    protected $title = "";
    protected $startAt = "";
    protected $endAt = "";
    protected $interval = "";
    protected $pattern = "";
    protected $inclusion = [];
    protected $exclusion = [];

    public function __construct(array $data = [])
    {
        $this->setTitle(key($data));
        foreach (reset($data) as $k => $v) {
            $method = 'set' . ucfirst(strtolower($k));
            if (method_exists($this, $method)) {
                $this->{$method}($v);
            }
        }

        if (empty($this->title) || empty($this->startAt)) {
            throw new InvalidArgumentException;
        }
    }

    public function setTitle(string $title): EventEntity
    {
        $this->title = $title;
        return $this;
    }

    public function setStart(string $start): EventEntity
    {
        $this->startAt = new \DateTime($start);
        return $this;
    }

    public function setEnd(string $end): EventEntity
    {
        $this->endAt = new \DateTime($end);
        return $this;
    }

    public function setInterval(string $interval): EventEntity
    {
        $this->interval = new \DateInterval($interval);
        return $this;
    }

    public function setPattern(string $pattern): EventEntity
    {
        if (!preg_match("/[ox]{7}/", $pattern)) {
            throw new \InvalidArgumentException;
        }

        $this->pattern = $pattern;
        return $this;
    }

    public function setInclusion(array $inclusion): EventEntity
    {
        $this->inclusion = $inclusion;
        return $this;
    }

    public function setExclusion(array $exclusion): EventEntity
    {
        $this->exclusion = $exclusion;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDate(): string
    {
        return $this->startAt instanceof \DateTime
            ? $this->startAt->format("Y-m-d")
            : "";
    }

    public function getStartAt(): string
    {
        return $this->startAt instanceof \DateTime
            ? $this->startAt->format("Y-m-d H:i:s")
            : "";
    }

    public function getEndAt(): string
    {
        return $this->endAt instanceof \DateTime
            ? $this->endAt->format("Y-m-d H:i:s")
            : "";
    }

    public function getIntervalSpec(): string
    {
        if (!($this->interval instanceof \DateInterval)) {
            return "";
        }

        $date = null;
        if ($this->interval->y) {
            $date .= $this->interval->y . 'Y';
        }
        if ($this->interval->m) {
            $date .= $this->interval->m . 'M';
        }
        if ($this->interval->d) {
            $date .= $this->interval->d . 'D';
        }
    
        $time = null;
        if ($this->interval->h) {
            $time .= $this->interval->h . 'H';
        }
        if ($this->interval->i) {
            $time .= $this->interval->i . 'M';
        }
        if ($this->interval->s) {
            $time .= $this->interval->s . 'S';
        }
        if ($time) {
            $time = 'T' . $time;
        }
    
        $text ='P' . $date . $time;
        return $text === 'P'
            ? 'PT0S'
            : $text;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getInclusion(): array
    {
        return $this->inclusion;
    }

    public function getExclusion(): array
    {
        return $this->exclusion;
    }

    public function toEventObj(): Event
    {
        $event = new Event();
        $event
            ->setDtStart($this->startAt)
            ->setSummary($this->title)
            ->setUseTimezone(true);

        if ($this->endAt instanceof \DateTime) {
            $event->setDtEnd($this->endAt);
        }

        if ($this->getStartAt() === $this->getEndAt()) {
            $event->setNoTime(true);
        }

        return $event;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->getTitle(),
            'date' => $this->getDate(),
            'startAt' => $this->getStartAt(),
            'endAt' => $this->getEndAt(),
        ];
    }

    public function generateEventRange(): array
    {
        $events = [clone $this];

        // インターバルがある場合
        if (!empty($this->interval)) {
            $loopDate = clone $this->startAt;
            $intervalDate = clone $loopDate;

            if ($this->endAt instanceof \DateTime) {
                $intervalDate = clone $this->endAt;
            }

            if ($loopDate->format("Y-m-d") === $intervalDate->format("Y-m-d")) {
                $intervalDate = \DateTime::createFromFormat("Y-m-d", $loopDate->format("Y-m-t"));
            }

            while ($loopDate < $intervalDate) {
                $loopDate->add($this->interval);

                $event = clone $this;
                $event->setStart($loopDate->format('Y-m-d H:i:s'));
                $event->setEnd($loopDate->format('Y-m-d H:i:s'));
                $events[] = $event;
            }
        }

        return $events;
    }

    public function generateEventRangeArray(): array
    {
        $events = $this->generateEventRange();
        $result = [];
        foreach ($events as $event) {
            $result[] = $event->toArray();
        }

        return $result;
    }

    public function generateEventRangeCalendar(Calendar $vCalendar): Calendar
    {
        $events = $this->generateEventRange();
        foreach ($events as $event) {
            $vCalendar->addComponent($event->toEventObj());
        }

        return $vCalendar;
    }
}
