<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

/**
 * Class FullCalendarWidget
 */
class FullCalendarWidget extends CWidget
{
    /** @var string the name of the container element that contains the calendar. Defaults to 'div' */
    public $tagName = 'div';

    /** @var array Html options of the container */
    public $htmlOptions = [];

    /** @var array Plugin options */
    public $options = [];

    /** @var FullCalendar */
    protected $component;

    public function init()
    {
        Yii::import('ext.yii-fullcalendar.FullCalendar');
        $this->component = Yii::app()->getComponent('fullcalendar') ? : new FullCalendar;
        $this->component->init();
    }

    /**
     * Run this widget.
     * This method registers necessary javascript and renders the needed HTML code.
     */
    public function run()
    {
        $id = $this->getId();
        $this->htmlOptions['id'] = $id;

        echo CHtml::openTag($this->tagName, $this->htmlOptions);
        echo CHtml::closeTag($this->tagName);

        $options = CMap::mergeArray($this->component->options, $this->options);
        $encodeOptions = CJavaScript::encode($options);

        Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $id, "$('#$id').fullCalendar($encodeOptions);");
    }
}