    <?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */

/**
 * Class FullCalendar
 */
class FullCalendar extends CApplicationComponent
{
    /** @var bool Force using files from cdn instead of local ones */
    public $useCDN = false;
    /** @var bool Use minified version of scripts */
    public $useMinified = true;
    /** @var string Version to use */
    public $version = "1.6.4";
    /** @var array Plugin options */
    public $options = [];

    public function init()
    {
        if (!$this->isInitialized) {
            /** @var CClientScript $cs */
            $cs = Yii::app()->clientScript;
            /** @var CAssetManager $am */
            $am = Yii::app()->assetManager;

            $cs->registerCoreScript('jquery');

            $jsFile = $this->useMinified ? 'fullcalendar.min.js' : 'fullcalendar.js';
            $cssFile = 'fullcalendar.css';

            if ($this->useCDN) {
                $baseUrl = '//cdnjs.cloudflare.com/ajax/libs/fullcalendar/' . $this->version;
            } else {
                $baseUrl = $am->publish(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $this->version);
            }

            $cs->registerScriptFile($baseUrl . '/' . $jsFile);
            $cs->registerCssFile($baseUrl . '/' . $cssFile);

            $this->initFormat();
        }
    }

    protected function initFormat()
    {
        $locale = Yii::app()->getLocale();

        $this->options['monthNames'] = array_values($locale->getMonthNames('wide', true));
        $this->options['monthNamesShort'] = array_values($locale->getMonthNames('wide', true));

        $this->options['dayNames'] = array_values($locale->getWeekDayNames());
        $this->options['dayNamesShort'] = array_values($locale->getWeekDayNames('abbreviated'));

        $this->options['columnFormat'] = [
            'month' => 'ddd',
            'week' => 'ddd '.$locale->getDateFormat('short'),
            'day' => '',
        ];

        $this->options['timeFormat'] = [
            '' => $locale->getTimeFormat(),
            'agenda' => "{$locale->getTimeFormat()}{ - {$locale->getTimeFormat()}}",
        ];

        $this->options['axisFormat'] = $locale->getTimeFormat('short');
        $this->options['firstDay'] = date('w', time() - (date('w') - 1) * 60*60*24);
    }

    /**
     * Merges two event arrays
     * When events intersect, event from arr2 will be taken.
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public function mergeEvents($arr1, $arr2)
    {
        // removing intersections from $arr2
        $arr1 = array_filter($arr1, function($v) use ($arr2) {
            foreach ($arr2 as $item) {
                if ($this->eventsIntersect($item, $v)) {
                    return false;
                }
            }
            return true;
        });

        return array_values(array_merge($arr1, $arr2));
    }

    /**
     * Checks if two events are intersecting
     *
     * @param array $e1
     * @param array $e2
     * @return bool
     */
    public function eventsIntersect($e1, $e2)
    {
        $result = false;

        // creating datetime objects
        $e1Start = $this->createDateTime($e1['start']);
        $e1End = $this->createDateTime($e1['end']);
        $e2Start = $this->createDateTime($e2['start']);
        $e2End = $this->createDateTime($e2['end']);

        // comparing datetime objects
        if (
            ($e2Start <= $e1Start and $e2End > $e1Start) // if intersecting through e1 start
            or
            ($e2Start < $e1End and $e2End >= $e1End) // if intersection through e1 end
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * Creates DateTime from string using timezone
     *
     * @param $dateStr
     * @param $timeZone
     * @return DateTime
     */
    protected function createDateTime($dateStr)
    {
        if (is_numeric($dateStr)) {
            $timeZone = new DateTimeZone(Yii::app()->timeZone);
            return new DateTime('@' . $dateStr, $timeZone);
        }

        return DateTime::createFromFormat(DateTime::ISO8601, $dateStr);
    }

    /**
     * Return intersection of $source with $target
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public function intersectSets($source, $target)
    {
        // removing intersections from $arr2
        $source = array_filter($source, function($v) use ($target) {
            foreach ($target as $item) {
                if ($this->eventsIntersect($item, $v)) {
                    return true;
                }
            }
            return false;
        });

        return $source;
    }

}
