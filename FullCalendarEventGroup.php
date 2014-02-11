<?php

/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 *
 * @property FullCalendarEvent[] $events
 */
class FullCalendarEventGroup implements Iterator, ArrayAccess
{
    public
        $options = [];

    private
        $position = 0,
        $events = [];

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->events[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->events[$this->position]);
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->events[] = $value;
        } else {
            $this->events[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->events[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->events[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->events[$offset]) ? $this->events[$offset] : null;
    }

    /**
     * Returns intersection of events array with $target event group.
     *
     * @param FullCalendarEventGroup $target
     */
    public function intersect($target)
    {
        $this->events = FullCalendarEvent::arrayIntersect($this->events, $target);
    }

    /**
     * Returns subtraction of events array with $target event group.
     *
     * @param FullCalendarEventGroup $target
     */
    public function subtract($target)
    {
        $this->events = FullCalendarEvent::arraySubtract($this->events, $target);
    }


    /**
     * Returns merge of $source event array with $target event array.
     * When events intersect, event from $target will be taken.
     *
     * @param FullCalendarEventGroup $target
     */
    public function union($target)
    {
        $this->events = FullCalendarEvent::arrayUnion($this->events, $target);
    }

    public function toArray()
    {
        $result = [];
        foreach ($this->events as $event) {
            $result[] = CMap::mergeArray($this->options, $event->toArray());
        }
        return $result;
    }

    public function toJSON()
    {
        return CJSON::encode($this->toArray());
    }
}