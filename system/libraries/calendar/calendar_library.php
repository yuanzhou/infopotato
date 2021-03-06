<?php
/**
 * Calendar Library
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2014 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */

namespace InfoPotato\libraries\calendar;

class Calendar_Library {
    /**
     * Calendar message
     * 
     * @var string 
     */
    private $show = array(
        'cal_su' => 'Su',
        'cal_mo' => 'Mo',
        'cal_tu' => 'Tu',
        'cal_we' => 'We',
        'cal_th' => 'Th',
        'cal_fr' => 'Fr',
        'cal_sa' => 'Sa',
        'cal_sun' => 'Sun',
        'cal_mon' => 'Mon',
        'cal_tue' => 'Tue',
        'cal_wed' => 'Wed',
        'cal_thu' => 'Thu',
        'cal_fri' => 'Fri',
        'cal_sat' => 'Sat',
        'cal_sunday' => 'Sunday',
        'cal_monday' => 'Monday',
        'cal_tuesday' => 'Tuesday',
        'cal_wednesday' => 'Wednesday',
        'cal_thursday' => 'Thursday',
        'cal_friday' => 'Friday',
        'cal_saturday'=> 'Saturday',
        'cal_jan' => 'Jan',
        'cal_feb' => 'Feb',
        'cal_mar' => 'Mar',
        'cal_apr' => 'Apr',
        'cal_may' => 'May',
        'cal_jun' => 'Jun',
        'cal_jul' => 'Jul',
        'cal_aug' => 'Aug',
        'cal_sep' => 'Sep',
        'cal_oct' => 'Oct',
        'cal_nov' => 'Nov',
        'cal_dec' => 'Dec',
        'cal_january' => 'January',
        'cal_february' => 'February',
        'cal_march' => 'March',
        'cal_april' => 'April',
        'cal_mayl' => 'May',
        'cal_june' => 'June',
        'cal_july' => 'July',
        'cal_august' => 'August',
        'cal_september' => 'September',
        'cal_october' => 'October',
        'cal_november'    => 'November',
        'cal_december' => 'December',
    );
    
    /**
     * A Unix timestamp corresponding to the current time.
     * 
     * @var string 
     */
    private $local_time;
    
    /**
     * A string containing your calendar template.
     * 
     * @var string 
     */
    private $template = '';
    
    /**
     * Sets the day of the week the calendar should start on.
     * 
     * @var string 
     */
    private $start_day = 'sunday';
    
    /**
     * Determines what version of the month name to use in the header. 
     * long = January, short = Jan.
     * 
     * @var string 
     */
    private $month_type = 'long';
    
    /**
     * Determines what version of the weekday names to use in the column headers. 
     * long = Sunday, short = Sun, abr = Su.
     * 
     * @var string 
     */
    private$day_type = 'abr';
    
    /**
     * SDetermines whether to display links allowing you to toggle to next/previous months.
     * 
     * @var boolean
     */
    private $show_next_prev = FALSE;
    
    /**
     * Sets the basepath used in the next/previous calendar links.
     * 
     * @var string 
     */
    private $next_prev_url = '';
    
    /**
     * Constructor
     *
     * The constructor can be passed an array of config values
     */
    public function __construct(array $config = NULL) {
        if (count($config) > 0) {
            foreach ($config as $key => $val) {
                // Using isset() requires $this->$key not to be NULL in property definition
                // property_exists() allows empty property
                if (property_exists($this, $key)) {
                    $method = 'initialize_'.$key;
                    
                    if (method_exists($this, $method)) {
                        $this->$method($val);
                    }
                } else {
                    exit("'".$key."' is not an acceptable config argument!");
                }
            }
        }
        
        $this->local_time = time();
    }
    
    /**
     * Validate and set $template
     *
     * @param $val string
     * @return void
     */
    private function initialize_template($val) {
        if ( ! is_string($val)) {
            $this->invalid_argument_value('template');
        }
        $this->template = $val;
    }
    
    /**
     * Validate and set $show_next_prev
     *
     * @param $val bool
     * @return void
     */
    private function initialize_show_next_prev($val) {
        if ( ! is_bool($val)) {
            $this->invalid_argument_value('show_next_prev');
        }
        $this->show_next_prev = $val;
    }
    
    /**
     * Validate and set $next_prev_url
     *
     * @param $val string
     * @return void
     */
    private function initialize_next_prev_url($val) {
        if ( ! is_string($val)) {
            $this->invalid_argument_value('next_prev_url');
        }
        $this->next_prev_url = $val;
    }
    
    /**
     * Validate and set $month_type
     *
     * @param $val string
     * @return void
     */
    private function initialize_month_type($val) {
        if ( ! is_string($val)) {
            $this->invalid_argument_value('month_type');
        }
        $this->month_type = $val;
    }
    
    /**
     * Validate and set $day_type
     *
     * @param $val string
     * @return void
     */
    private function initialize_day_type($val) {
        if ( ! is_string($val)) {
            $this->invalid_argument_value('day_type');
        }
        $this->day_type = $val;
    }
    
    /**
     * Validate and set $start_day
     *
     * @param $val string
     * @return void
     */
    private function initialize_start_day($val) {
        if ( ! is_string($val)) {
            $this->invalid_argument_value('start_day');
        }
        $this->start_day = $val;
    }
    
    /**
     * Output the error message for invalid argument value
     *
     * @return void
     */
    private function invalid_argument_value($arg) {
        exit("In your config array, the provided argument value of "."'".$arg."'"." is invalid.");
    }
    
    
    /**
     * Generate the calendar
     *
     * @param integer the year
     * @param integer the month
     * @param array the data to be shown in the calendar cells
     * @return string
     */
    public function generate($year = '', $month = '', array $data = NULL) {
        // Set and validate the supplied month/year
        if ($year === '') {
            $year  = date('Y', $this->local_time);
        }    
        
        if ($month === '') {
            $month = date('m', $this->local_time);
        }    
        
         if (strlen($year) === 1) {
            $year = '200'.$year;
        }
        
         if (strlen($year) === 2) {
            $year = '20'.$year;
        }
        
         if (strlen($month) === 1) {
            $month = '0'.$month;
        }
        
        $adjusted_date = $this->adjust_date($month, $year);
        
        $month = $adjusted_date['month'];
        $year = $adjusted_date['year'];
        
        // Determine the total days in the month
        $total_days = $this->get_total_days($month, $year);
        
        // Set the starting day of the week
        $start_days    = array('sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6);
        $start_day = ( ! isset($start_days[$this->start_day])) ? 0 : $start_days[$this->start_day];
        
        // Set the starting day number
        $local_date = mktime(12, 0, 0, $month, 1, $year);
        $date = getdate($local_date);
        $day = $start_day + 1 - $date["wday"];
        
        while ($day > 1) {
            $day -= 7;
        }
        
        // Set the current month/year/day
        // We use this to determine the "today" date
        $cur_year = date("Y", $this->local_time);
        $cur_month = date("m", $this->local_time);
        $cur_day = date("j", $this->local_time);
        
        $is_current_month = ($cur_year === $year && $cur_month === $month) ? TRUE : FALSE;
    
        // Generate the template data array
        $this->parse_template();
    
        // Begin building the calendar output 
        $out = $this->temp['table_open'];
        $out .= "\n"; 
        
        $out .= "\n";  
        $out .= $this->temp['heading_row_start'];
        $out .= "\n";
        
        // "previous" month link
        if ($this->show_next_prev === TRUE) {
            // Add a trailing slash to the  URL if needed
            $this->next_prev_url = preg_replace("/(.+?)\/*$/", "\\1/",  $this->next_prev_url);
        
            $adjusted_date = $this->adjust_date($month - 1, $year);
            $out .= str_replace('{previous_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'], $this->temp['heading_previous_cell']);
            $out .= "\n";
        }
        
        // Heading containing the month/year
        $colspan = ($this->show_next_prev === TRUE) ? 5 : 7;
        
        $this->temp['heading_title_cell'] = str_replace('{colspan}', $colspan, $this->temp['heading_title_cell']);
        $this->temp['heading_title_cell'] = str_replace('{heading}', $this->get_month_name($month)."&nbsp;".$year, $this->temp['heading_title_cell']);
        
        $out .= $this->temp['heading_title_cell'];
        $out .= "\n";
        
        // "next" month link
        if ($this->show_next_prev === TRUE) {        
            $adjusted_date = $this->adjust_date($month + 1, $year);
            $out .= str_replace('{next_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'], $this->temp['heading_next_cell']);
        }
        
        $out .= "\n";        
        $out .= $this->temp['heading_row_end'];
        $out .= "\n";
        
        // Write the cells containing the days of the week
        $out .= "\n";    
        $out .= $this->temp['week_row_start'];
        $out .= "\n";
        
        $day_names = $this->get_day_names();
        
        for ($i = 0; $i < 7; $i ++) {
            $out .= str_replace('{week_day}', $day_names[($start_day + $i) %7], $this->temp['week_day_cell']);
        }
        
        $out .= "\n";
        $out .= $this->temp['week_row_end'];
        $out .= "\n";
        
        // Build the main body of the calendar
        while ($day <= $total_days) {
            $out .= "\n";
            $out .= $this->temp['cal_row_start'];
            $out .= "\n";
            
            for ($i = 0; $i < 7; $i++) {
                $out .= ($is_current_month === TRUE && $day === $cur_day) ? $this->temp['cal_cell_start_today'] : $this->temp['cal_cell_start'];
            
                if ($day > 0 && $day <= $total_days) {                     
                    if (isset($data[$day])) {    
                        // Cells with content
                        $temp = ($is_current_month === TRUE && $day === $cur_day) ? $this->temp['cal_cell_content_today'] : $this->temp['cal_cell_content'];
                        $out .= str_replace('{day}', $day, str_replace('{content}', $data[$day], $temp));
                    } else {
                        // Cells with no content
                        $temp = ($is_current_month === TRUE && $day === $cur_day) ? $this->temp['cal_cell_no_content_today'] : $this->temp['cal_cell_no_content'];
                        $out .= str_replace('{day}', $day, $temp);
                    }
                } else {
                    // Blank cells
                    $out .= $this->temp['cal_cell_blank'];
                }
                
                $out .= ($is_current_month === TRUE && $day === $cur_day) ? $this->temp['cal_cell_end_today'] : $this->temp['cal_cell_end'];                          
                $day++;
            }
            
            $out .= "\n";        
            $out .= $this->temp['cal_row_end'];
            $out .= "\n";        
        }
        
        $out .= "\n";        
        $out .= $this->temp['table_close'];
        
        return $out;
    }
    
    /**
     * Get Month Name
     *
     * Generates a textual month name based on the numeric
     * month provided.
     *
     * @param integer the month
     * @return string
     */
    private function get_month_name($month) {
        if ($this->month_type === 'short') {
            $month_names = array('01' => 'cal_jan', '02' => 'cal_feb', '03' => 'cal_mar', '04' => 'cal_apr', '05' => 'cal_may', '06' => 'cal_jun', '07' => 'cal_jul', '08' => 'cal_aug', '09' => 'cal_sep', '10' => 'cal_oct', '11' => 'cal_nov', '12' => 'cal_dec');
        } else {
            $month_names = array('01' => 'cal_january', '02' => 'cal_february', '03' => 'cal_march', '04' => 'cal_april', '05' => 'cal_mayl', '06' => 'cal_june', '07' => 'cal_july', '08' => 'cal_august', '09' => 'cal_september', '10' => 'cal_october', '11' => 'cal_november', '12' => 'cal_december');
        }
        
        $month = $month_names[$month];
        
        if ($this->show[$month] === FALSE) {
            return ucfirst(str_replace('cal_', '', $month));
        }
        
        return $this->show[$month];
    }
    
    /**
     * Get Day Names
     *
     * Returns an array of day names (Sunday, Monday, etc.) based
     * on the type.  Options: long, short, abrev
     *
     * @param string
     * @return array
     */
    private function get_day_names($day_type = '') {
        if ($day_type !== '') {
            $this->day_type = $day_type;
        }
        
        if ($this->day_type === 'long') {
            $day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        } elseif ($this->day_type === 'short') {
            $day_names = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
        } else {
            $day_names = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
        }
    
        $days = array();
        foreach ($day_names as $val) {            
            $days[] = ($this->show['cal_'.$val] === FALSE) ? ucfirst($val) : $this->show['cal_'.$val];
        }
    
        return $days;
    }
    
    /**
     * Adjust Date
     *
     * This function makes sure that we have a valid month/year.
     * For example, if you submit 13 as the month, the year will
     * increment and the month will become January.
     *
     * @param integer the month
     * @param integer the year
     * @return array
     */
    private function adjust_date($month, $year) {
        $date = array();
        
        $date['month']    = $month;
        $date['year']    = $year;
        
        while ($date['month'] > 12) {
            $date['month'] -= 12;
            $date['year']++;
        }
        
        while ($date['month'] <= 0) {
            $date['month'] += 12;
            $date['year']--;
        }
        
        if (strlen($date['month']) == 1) {
            $date['month'] = '0'.$date['month'];
        }
        
        return $date;
    }
     
    /**
     * Total days in a given month
     *
     * @param integer the month
     * @param integer the year
     * @return integer
     */
    private function get_total_days($month, $year) {
        $days_in_month    = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        
        if ($month < 1 || $month > 12) {
            return 0;
        }
        
        // Is the year a leap year?
        if ($month == 2) {
            if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0)) {
                return 29;
            }
        }
        
        return $days_in_month[$month - 1];
    }
    
    /**
     * Set Default Template Data
     *
     * This is used in the event that the user has not created their own template
     *
     * @return array
     */
    private function default_template() {
        return  array (
            'table_open'                 => '<table border="0" cellpadding="0" cellspacing="0">',
            'heading_row_start'         => '<tr>',
            'heading_previous_cell'        => '<th><a href="{previous_url}">&lt;&lt;</a></th>',
            'heading_title_cell'         => '<th colspan="{colspan}">{heading}</th>',
            'heading_next_cell'         => '<th><a href="{next_url}">&gt;&gt;</a></th>',
            'heading_row_end'             => '</tr>',
            'week_row_start'             => '<tr>',
            'week_day_cell'             => '<td>{week_day}</td>',
            'week_row_end'                 => '</tr>',
            'cal_row_start'             => '<tr>',
            'cal_cell_start'             => '<td>',
            'cal_cell_start_today'        => '<td>',
            'cal_cell_content'            => '<a href="{content}">{day}</a>',
            'cal_cell_content_today'    => '<a href="{content}"><strong>{day}</strong></a>',
            'cal_cell_no_content'        => '{day}',
            'cal_cell_no_content_today'    => '<strong>{day}</strong>',
            'cal_cell_blank'            => '&nbsp;',
            'cal_cell_end'                => '</td>',
            'cal_cell_end_today'        => '</td>',
            'cal_row_end'                => '</tr>',
            'table_close'                => '</table>'
        );    
    }
    
    /**
     * Parse Template
     *
     * Harvests the data within the template {pseudo-variables}
     * used to display the calendar
     *
     * @return void
     */
     private function parse_template() {
        $this->temp = $this->default_template();
     
         if ($this->template === '') {
             return;
         }
         
        $today = array('cal_cell_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today');
        
        foreach (array('table_open', 'table_close', 'heading_row_start', 'heading_previous_cell', 'heading_title_cell', 'heading_next_cell', 'heading_row_end', 'week_row_start', 'week_day_cell', 'week_row_end', 'cal_row_start', 'cal_cell_start', 'cal_cell_content', 'cal_cell_no_content',  'cal_cell_blank', 'cal_cell_end', 'cal_row_end', 'cal_cell_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today') as $val) {
            if (preg_match("/\{".$val."\}(.*?)\{\/".$val."\}/si", $this->template, $match)) {
                $this->temp[$val] = $match['1'];
            } else {
                if (in_array($val, $today, TRUE)) {
                    $this->temp[$val] = $this->temp[str_replace('_today', '', $val)];
                }
            }
        }     
    }
    
}

/* End of file: ./system/libraries/calendar/calendar_library.php */