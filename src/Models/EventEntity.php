<?php

namespace App\Models;

use \Eluceo\iCal\Component\Event;
use \Eluceo\iCal\Component\Calendar;

class EventEntity
{
    /**
     * 予定タイトル
     *
     * @var String
     */
    protected $title = "";

    /**
     * 予定開始日
     *
     * @var \DateTime
     */
    protected $startAt = "";

    /**
     * 予定終了日
     *
     * @var \DateTime
     */
    protected $endAt = "";

    /**
     * 繰り返し予定のインターバル
     *
     * @var \DateInterval
     */
    protected $interval = "";

    /**
     * 週間予定の場合のパターン
     * 月曜日から日曜日
     * 　例） oxoxoxx
     *
     * @var String
     */
    protected $pattern = "";

    /**
     * パターン外に特定の日時を指定する
     *
     * @var Array
     */
    protected $inclusion = [];

    /**
     * パターン中の特定の日時を除外する
     *
     * @var Array
     */
    protected $exclusion = [];

    public function __construct(array $data = [])
    {
        $this->setTitle(key($data));
        foreach (reset($data) as $k => $v) {
            if (empty($v)) {
                continue;
            }

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
        return $this->startAt->format("Y-m-d");
    }

    public function getStartAt(): \DateTime
    {
        return $this->startAt;
    }

    public function getEndAt(): \DateTime
    {
        return $this->endAt;
    }

    public function getInterval(): \DateInterval
    {
        return $this->interval;
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

        if ($this->startAt->format("Y-m-d") === $this->endAt->format("Y-m-d")) {
            $event->setNoTime(true);
        }

        return $event;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->getTitle(),
            'date' => $this->getDate(),
            'startAt' => $this->getStartAt()->format("Y-m-d H:i:s"),
            'endAt' => $this->getEndAt()->format("Y-m-d H:i:s"),
        ];
    }
}
