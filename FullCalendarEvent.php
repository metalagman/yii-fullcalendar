<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com> 
 */

class FullCalendarEvent
{
    public
        $title,
        $start,
        $end,
        $options,
        $timeZone;

    public function __construct()
    {
        $this->timeZone = new DateTimeZone(Yii::app()->timeZone);
    }

    public function toArray()
    {
        return CMap::mergeArray($this->options, [
            'title' => $this->title,
            'start' => $this->start->format(DateTime::ISO8601),
            'end' => $this->end->format(DateTime::ISO8601),
        ]);
    }

    /**
     * Constructor. Creates event object from existing model.
     *
     * @param CActiveRecord $model
     * @param string $title
     * @param string $startAttribute
     * @param string $endAttribute
     * @param string|null $startTimeAttribute
     * @param string|null $endTimeAttribute
     * @return FullCalendarEvent
     */
    public static function createFromModel(
        $model,
        $title,
        $startAttribute,
        $endAttribute,
        $startTimeAttribute = null,
        $endTimeAttribute = null
    )
    {
        $event = new self();
        $event->title = $title;

        $event->start = new DateTime($model->getAttribute($startAttribute), $event->timeZone);
        $event->end = new DateTime($model->getAttribute($endAttribute), $event->timeZone);

        if (isset($startTimeAttribute)) {
            $timeStart = new DateTime($model->getAttribute($startTimeAttribute), $event->timeZone);
            $event->start->setTime($timeStart->format('H'), $timeStart->format('i'), $timeStart->format('s'));
        }

        if (isset($endTimeAttribute)) {
            $timeEnd = new DateTime($model->getAttribute($endTimeAttribute), $event->timeZone);
            $event->end->setTime($timeEnd->format('H'), $timeEnd->format('i'), $timeEnd->format('s'));
        }

        return $event;
    }

    /**
     * Constructor. Creates event object from timestamps.
     *
     * @param string $title
     * @param integer $startTS
     * @param integer $endTS
     * @return FullCalendarEvent
     */
    public static function createFromTimeStamp(
        $title,
        $startTS,
        $endTS
    )
    {
        $event = new self();
        $event->title = $title;

        $event->start = new DateTime("@$startTS", $event->timeZone);
        $event->end = new DateTime("@$endTS", $event->timeZone);

        $event->start->setTimezone($event->timeZone);
        $event->end->setTimezone($event->timeZone);

        return $event;
    }

    /**
     * Constructor. Creates event object from datetimes.
     *
     * @param string $title
     * @param integer $startTS
     * @param integer $endTS
     * @return FullCalendarEvent
     */
    public static function createFromDateTime(
        $title,
        $start,
        $end
    )
    {
        $event = new self();
        $event->title = $title;

        $event->start = $start;
        $event->end = $end;

        $event->start->setTimezone($event->timeZone);
        $event->end->setTimezone($event->timeZone);

        return $event;
    }

    /**
     * Checks if two events are intersecting
     *
     * @param FullCalendarEvent $compare
     * @return bool
     */
    public function intersectsWith(FullCalendarEvent $compare)
    {
        $result = false;

        // comparing datetime objects
        if (
            ($compare->start <= $this->start and $compare->end > $this->start) // if intersecting through event start
            or
            ($compare->start < $this->end and $compare->end >= $this->end) // if intersecting through event end
        ) {
            $result = true;
        }

        return $result;
    }

    /**
 * Returns intersection of $source event array with $target event array.
 *
 * @param FullCalendarEvent[] $source
 * @param FullCalendarEvent[] $target
 * @return FullCalendarEvent[]
 */
    public static function arrayIntersect($source, $target)
    {
        // removing intersections from source
        $source = array_filter($source, function($v) use ($target) {
            foreach ($target as $item) {
                if ($item->intersectsWith($v))
                    return true;
            }
            return false;
        });
        return $source;
    }

    /**
     * Returns subtraction of $source event array with $target event array.
     *
     * @param FullCalendarEvent[] $source
     * @param FullCalendarEvent[] $target
     * @return FullCalendarEvent[]
     */
    public static function arraySubtract($source, $target)
    {
        // removing intersections from source
        $source = array_filter($source, function($v) use ($target) {
            foreach ($target as $item) {
                if ($item->intersectsWith($v))
                    return false;
            }
            return true;
        });
        return $source;
    }

    /**
     * Returns merge of $source event array with $target event array.
     * When events intersect, event from $target will be taken.
     *
     * @param FullCalendarEvent[] $source
     * @param FullCalendarEvent[] $target
     * @return FullCalendarEvent[]
     */
    public static function arrayUnion($source, $target)
    {
        $prepend = self::arraySubtract($source, $target);
        return array_values(array_merge($prepend, $target));
    }

    public function splitDays()
    {
        $result = new FullCalendarEventGroup();

        $generatorCurrent = clone $this->start;;

        while ($generatorCurrent <= $this->end) {
            $dayStart = clone $generatorCurrent;
            $dayStart->setTime($this->start->format('H'), $this->start->format('i'), $this->start->format('s'));
            $dayEnd = clone $generatorCurrent;
            $dayEnd->setTime($this->end->format('H'), $this->end->format('i'), $this->end->format('s'));

            $dayEvent = clone $this;
            $dayEvent->start = $dayStart;
            $dayEvent->end = $dayEnd;

            $result[] = $dayEvent;

            $generatorCurrent->add(new DateInterval('P1D'));
        }

        return $result;
    }
}