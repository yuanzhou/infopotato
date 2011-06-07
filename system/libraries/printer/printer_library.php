<?php

/**
 *  Class that creates printer friendly version of any referrer page
 *
 *  This script helps you create printer friendly versions of your pages.
 *  All you need to do is to insert some tags in your pages, tags that will tell the script what needs to be printed from
 *  that specific page. An unlimited number of areas can be set for printing allowing you a flexible way of setting up
 *  the content to be printed
 *
 *  The output is template driven, meaning that you can customize the printer friendly versions of your pages by adding
 *  custom headers, footers, copyright information or whatever extra info that you find appropriate
 *
 *  The script can be instructed to transform links to a readable format (<a href="www.somesite.com">click here</a> will
 *  become click here [www.somesite.com]) or to remove or convert <img> tags (<img src="pic.jpg" alt="picture"> will become
 *  [image: picture] or just [image] if no alt attribute is specified)
 *
 *  This script was inspired by PHPrint {@link http://www.tufts.edu/webcentral/phprint}
 *
 *  See the documentation for more info.
 *
 *  Read the LICENSE file, provided with the package, to find out how you can use this PHP script.
 *
 *  If you don't find this file, please write an email to noname at nivelzero dot ro and you will be sent a copy of the license file
 *
 *  For more resources visit {@link http://stefangabos.blogspot.com}
 *
 *  @name       printer
 *  @package    print
 *  @version    1.3 (last revision: April 29, 2008)
 *  @author     Stefan Gabos <ix@nivelzero.ro>
 *  @copyright  (c) 2006 - 2008 Stefan Gabos
 *  @example    example.php
 *
 */

class Printer_Library {

    /**
     *  Tag used to delimit start of the area to print
     *
     *  An unlimited number of areas to be printed can be delimited as long as
     *  they are not contained inside another defined area!
     *
     *  default is "<!-- PRINT: start -->" (without the quotes)
     *
     *  @var    string
     */
    private $start_print_tag = "<!-- PRINT: start -->";

    /**
     *  Tag used to delimit end of the area to print
     *
     *  An unlimited number of areas to be printed can be delimited as long as
     *  they are not contained inside another defined area!
     *
     *  default is "<!-- PRINT: stop -->" (without the quotes)
     *
     *  @var    string
     */
    private $stop_print_tag = "<!-- PRINT: stop -->";
    
    /**
     *  Tag used to delimit start of an area that will print
     *  extra content, content that is not available in the page
     *
     *  <b>Note that this tag starts a HTML comment block -
     *  so the content contained in this block will not be visible in the main page but only for printing!</b>
     *
     *  An unlimited number of areas to be printed can be delimited as long as
     *  they are not contained inside another defined area!
     *
     *  default is "<!-- PRINT: start-extra" (without the quotes)
     *
     *  @var    string
     */
    private $start_extra_print_tag = "<!-- PRINT: start-extra";

    /**
     *  Tag used to delimit end of an area that will print
     *  extra content, content that is not available in the page
     *
     *  <b>Note that this tag ends a HTML comment block -
     *  so the content contained in this block will not be visible in the main page but only for printing!</b>
     *
     *  An unlimited number of areas to be printed can be delimited as long as
     *  they are not contained inside another defined area!
     *
     *  default is "PRINT: stop-extra -->" (without the quotes)
     *
     *  @var    string
     */
    private $stop_extra_print_tag = "PRINT: stop-extra -->";
    
    /**
     *  Weather or not to convert images to a readable format
     *
     *  <i>Note that if {@link $drop_images} property is set to TRUE, this property is ignored!</i>
     *
     *  <b>Until 1.3 this property was called {@link $drop_images}. To read about the new functionality of {@link $drop_images}, click on
     *  the link</b>
     *
     *  When set to TRUE, all <img> tags will be replaced with the "[image:]" word
     *  (without the quotes) or, if image has the "alt" attribute set, with
     *  "[image:alt description]" (without the quotes)
     *
     *  Note that if you choose to convert the images, your page layout may suffer modifications!
     *
     *  default is FALSE
     *
     *  @since  1.3
     *
     *  @var    boolean
     */
    private $convert_images = FALSE;

    /**
     *  Weather or not to convert links (anchors) to a readable format.
     *
     *  By default, when printing something like <a href="http://www.somesite.com">click</a>,
     *  the url will not be visible on the paper - just "click" will be shown.
     *
     *  When you set this property to TRUE the anchor from above will produce
     *  "click [http://www.somesite.com]" (without the quotes)
     *
     *  The script will convert all of these cases (single, double and no quotes):
     *
     *  <a href="http://www.somesite.com">click</a>
     *
     *  <a href='http://www.somesite.com'>click</a>
     *
     *  <a href=http://www.somesite.com>click</a>
     *
     *  default is TRUE
     *
     *  @var    boolean
     */
    private $convert_links = TRUE;

    /**
     *  Weather or not to remove images from the print
     *
     *  <i>Note that if this property is set to TRUE, the {@link $convert_images} property is ignored!</i>
     *
     *  When set to TRUE, all <img> tags will be removed from the printer friendly version of the document
     *
     *  Note that if you choose to remove the images, your page layout may suffer modifications!
     *
     *  @var    boolean
     */
    private $drop_images = FALSE;

    /**
     *  In case of an error read this property's value to find out what went wrong
     *
     *  possible error values are:
     *
     *      - 0:  file could not be opened
     *      - 1:  the number of starting tags don't match the number of ending tags
     *      - 2:  areas overlap each other
     *
     *  default is 0
     *
     *  @var integer
     */
    private $errors = array();
    
    /**
     *  Constructor
     */
    public function __construct($config = array()) {
		// possible error values are
		$this->errors = array(
			0 => 'file could not be opened',
			1 => 'the number of starting tags do not match the number of ending tags',
			2 => 'areas overlap each other',
			3 => 'no referer page found'
		);
		
		if (isset($config['convert_images'])) {
			$this->convert_images = $config['convert_images'];
		}
		
		if (isset($config['convert_links'])) {
			$this->convert_links = $config['convert_links'];
		}
    }


	/**
	 * Render the printer friendly version of the referrer page
	 *
	 * @return	string
	 */
    public function render() {
        if ( ! isset($_SERVER["HTTP_REFERER"])) {
			return $this->errors[3];
		}
		
		// print the page who called this page
		$page = $_SERVER["HTTP_REFERER"];
		
        // tries to open the page
        // note that the page is opened exactly the same way as any browser would open it!
        if ($handle = fopen($page, "rb")) {

            // if file opened successfully
            $page_content = '';

            // read all its content in a variable
            while ( ! feof($handle)) {
                $page_content .= fread($handle, 8192);
            }

            // close file
            fclose($handle);

            // read all starting tags positions into an array
            preg_match_all("/".quotemeta($this->start_print_tag)."/", $page_content, $start_tags, PREG_OFFSET_CAPTURE);

            // read all ending tags positions into an array
            preg_match_all("/".quotemeta($this->stop_print_tag)."/", $page_content, $stop_tags, PREG_OFFSET_CAPTURE);

            // read all extra starting tags positions into an array
            preg_match_all("/".quotemeta($this->start_extra_print_tag)."/", $page_content, $start_extra_tags, PREG_OFFSET_CAPTURE);

            // read all extra ending tags positions into an array
            preg_match_all("/".quotemeta($this->stop_extra_print_tag)."/", $page_content, $stop_extra_tags, PREG_OFFSET_CAPTURE);

            // if there are as many starting tags as ending tags
            if (count($start_tags) == count($stop_tags) && count($start_extra_tags) == count($stop_extra_tags)) {
                // this is an array that groups start-end pairs
                $tags_array = array();

                // populate the array with default start/end pairs
                for ($i = 0; $i < count($start_tags[0]); $i++) {
                    $tags_array[] = array($start_tags[0][$i][1], $stop_tags[0][$i][1], strlen($this->start_print_tag));
                }
                
                // populate the array with extra start/end pairs
                for ($i = 0; $i < count($start_extra_tags[0]); $i++) {
                    $tags_array[] = array($start_extra_tags[0][$i][1], $stop_extra_tags[0][$i][1], strlen($this->start_extra_print_tag));
                }

                // sorts the array so that the extra start/end pairs get in correct position (as default, they get to the end)
                sort($tags_array);
                
                // at this stage the $tags_array[] array holds all the pairs of
                // starting-ending positions of printable areas

                // checks if there are areas that are crossing each other
                // by comparing the values of the array
                foreach ($tags_array as $subjectKey=>$subject_values) {
                    // with all the values of the array
                    foreach ($tags_array as $searchKey=>$search_values) {
                        // except the one that is checked
                        if ($subjectKey != $searchKey) {
                            // checks if the area crosses other areas
                            if (($subject_values[0] >= $search_values[0] && $subject_values[0] <= $search_values[1]) ||
                                ($subject_values[1] >= $search_values[0] && $subject_values[1] <= $search_values[1])) {
                                // save the error level and stop the execution of the script
                                return $this->errors[2];
                            }
                        }
                    }
                }

                // If everything is ok
                // retrieve from the page only the content that needs to be printed
                $content_to_print = '';

                foreach ($tags_array as $offset) {
                    $content_to_print .= substr($page_content, $offset[0] + $offset[2], $offset[1] - $offset[0] - $offset[2]);
                }

                // If links are to be converted to a readable format
                if ($this->convert_links) {
                    // until there are links left to convert
                    /*
					while (preg_match("/<a\s*?href=([\"|\'])(.*?)\\1>(.*?)<\/a>/i", $content_to_print, $matches) > 0) {
                        // convert links
                        $content_to_print = preg_replace("/<a\s*?href=([\"|\'])(.*?)\\1>(.*?)<\/a>/i", "\$3 " . (trim(strip_tags($matches[3])) != "" ? "[\$1]" : ""), $content_to_print, 1);
                    }
					*/
					
					while (preg_match("/\<a.*href\s*=\s*\'([^\']*)\'[^\>]*\>(.*)\<\/a\>|\<a.*href\s*=\s*\"([^\"]*)\"[^\>]*\>(.*)\<\/a\>|\<a.*href\s*=\s*([^\s]*)\s*[^\>]*\>(.*)\<\/a\>/i", $content_to_print, $matches) > 0) {
                        // convert links
                        $content_to_print = preg_replace("/\<a.*href\s*=\s*\'([^\']*)\'[^\>]*\>(.*)\<\/a\>|\<a.*href\s*=\s*\"([^\"]*)\"[^\>]*\>(.*)\<\/a\>|\<a.*href\s*=\s*([^\s]*)\s*[^\>]*\>(.*)\<\/a\>/i", "\$2\$4\$6 [\$1\$3\$5]", $content_to_print, 1);
                    }
                }
                
                // if <img> tags are to be dropped
                if ($this->drop_images) {
                    // drop all <img> tags
                    $content_to_print = preg_replace("/\<img[^\>]*?\>/", "&nbsp;", $content_to_print);

                // if <img> tags are to be converted to a readable format
                } elseif ($this->convert_images) {
                    // until there are images left to convert
                    while (preg_match("/\<img[^\>]*\>/", $content_to_print, $matches) > 0) {
                        // if image has the alt attribute set
                        if (preg_match("/alt\s*?=\s*?\"([^\"]*)\"|alt\s*?=\s*?\'([^\']*)\'|alt\s*?=\s*?([^\s]*)\s/i", $matches[0], $altText) > 0) {
                            // replace the img tag with [image: alt content]
                            $content_to_print = preg_replace("/\<img[^\>]*\>/", "[image:".$altText[1]."]", $content_to_print, 1);
                        // if no alt attribute is set for the image
                        } else {
                            // replace the img rag with [image]
                            $content_to_print = preg_replace("/\<img[^\>]*\>/", "[image]", $content_to_print, 1);
                        }
                    }
                }
            // if different number of starting and ending tags
            } else {
                // save the error level and stop the execution of the script
                return $this->errors[1];
            }
        // if page could not be opened (i.e. no access rights)
        } else {
            // save the error level and stop the execution of the script
            return $this->errors[0];
        }
        
		// returns content if everything went ok
        return $content_to_print;
    }
    
}


/* End of file: ./system/libraries/printer/printer_library.php */
