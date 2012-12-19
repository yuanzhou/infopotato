<?php
/**
 * Pagination Library
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2012 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 * @link http://www.catchmyfame.com/2007/07/28/finally-the-simple-pagination-class/
 */
class Pagination_Library {
	/**
     * The desired number of items to be shown on each page.
	 * 
	 * @var integer 
     */
	protected $items_per_page = 10;
	
	/**
     * The total number of items you'll be paginating.
     *
     * @var integer
     */
	protected $items_total;
	
	/**
     * The page the user is viewing. Will always be an integer >= 1
	 * 
	 * @var integer 
     */
	protected $current_page = 1;
	
	/**
     * The CSS class for current page
	 * 
	 * @var string
     */
	protected $current_page_class = '';
	
	/**
     * The number of pages to show 'around' the current page, is odd and >=3
	 * 
	 * The mid range is the number of pages that the paginator will display, 
	 * centered around and including the selected page. For example, 
	 * if the mid range is set to seven ($this->mid_range = 7;) then 
	 * when browsing page 50 of 100, the mid range generates links to 
	 * pages 47, 48, 49, 50, 51, 52, and 53. The mid range moves in relation to 
	 * the selected page. If the user is at either the low or high end of the list of pages, 
	 * it will slide  the range toward the other side to accommodate the position. 
	 * For example, if  the user visits page 99 of 100, the mid range will generate links for 
	 * pages  94, 95, 96, 97, 98, 99, and 100.
	 * 
	 * @var integer 
     */
	protected $mid_range = 7;
	
	/**
     * The base page URI we are linking to
	 * 
	 * @var string
     */
	protected $base_uri = '';
	
	/**
     * The pagination data in an array for debug
	 * 
	 * @var array()
     */
	private $_pagination_data = array();
	
	/**
	 * Constructor
	 */	
	public function __construct(array $config = NULL) { 
		if (count($config) > 0) {
			foreach ($config as $key => $val) {
				$this->$key = $val;
			}
		}
	}

	/**
     * The build_pagination method is what determines how many page numbers to display,
	 * figures out how they should be linked, and applies CSS for styling.
     *
     * @return the pagination string
     */
	public function build_pagination() {
		// The total number of pages as generated by the pagination class
		$num_pages = ceil($this->items_total/$this->items_per_page);	    

		// Create the pagination link
		$output = '';
			
		if ($num_pages > 1) {
			// The number of pages to show 'around' the current page
			$start_range = $this->current_page - floor($this->mid_range/2);
			$end_range = $this->current_page + floor($this->mid_range/2);

			if ($start_range <= 0) {
				$end_range += abs($start_range) + 1;
				$start_range = 1;
			}
			if ($end_range > $num_pages) {
				$start_range -= $end_range - $num_pages;
				$end_range = $num_pages;
			}
			
			$this->_pagination_data = array(
			    'base_uri' => $this->base_uri,
			    'items_total' => $this->items_total,
			    'items_per_page' => $this->items_per_page,
			    'mid_range' => $this->mid_range,
			    'current_page' => $this->current_page,
			    'current_page_class' => $this->current_page_class,
			    'prev_page' => $this->current_page - 1,
			    'next_page' => $this->current_page + 1,
			    'num_pages' => $num_pages,
			    'offset_low' => ($this->current_page - 1) * $this->items_per_page,
			    'offset_high' => $this->current_page * $this->items_per_page,
			    'range' => range($start_range, $end_range), // Create an array containing a range of elements
		    );
			
			
		    if ($this->_pagination_data['current_page'] > 1) {
			    $output = '<a href="'.$this->_pagination_data['base_uri'].$this->_pagination_data['prev_page'].'">&laquo;</a> ';
		    }
		
		    for ($i = 1; $i <= $this->_pagination_data['num_pages']; $i++) {
			    if ($this->_pagination_data['range'][0] > 2 && $i === $this->_pagination_data['range'][0]) {
				    $output .= '...';
			    }
			
			    if ($i === 1 || $i === $this->_pagination_data['num_pages'] || in_array($i, $this->_pagination_data['range'])) {
				    if ($i === $this->_pagination_data['current_page']) {
					    $output .= '<span class="'.$this->_pagination_data['current_page_class'].'">'.$i.'</span>'; 
				    } else {
					    $output .= '<a href="'.$this->_pagination_data['base_uri'].$i.'">'.$i.'</a>'; 
				    }
			    }
			
			    if ($this->_pagination_data['range'][$this->_pagination_data['mid_range']-1] < $this->_pagination_data['num_pages']-1 && $i === $this->_pagination_data['range'][$this->_pagination_data['mid_range']-1]) {
				    $output .= '...';
			    }
		    }
		
		    if ($this->_pagination_data['current_page'] !== $this->_pagination_data['num_pages']) {
			    $output .= '<a href="'.$this->_pagination_data['base_uri'].$this->_pagination_data['next_page'].'">&raquo;</a>'; 
		    }
		} 
		
		return $output;
	}
	
	/**
     * Get all the pagination metadata
     *
     * @return the pagination data array
     */
	public function get_pagination_data() {
		return $this->_pagination_data;
	}
}

/* End of file: ./system/libraries/pagination/pagination_library.php */