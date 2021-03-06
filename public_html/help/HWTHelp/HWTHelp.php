<?php
/**
 * HWTHelp.php
 * 
 * PHP Version 5
 *
 * @package HWTHelp
 * @copyright 2012 Tyler Kemp-Benedict
 * @license GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link http://www.hardworkingtype.com/
 */

/**
 * Label exceptions from the HWTHelp class
 * 
 */
require_once('config.php');

class HWTHelpException extends Exception
{
}

/**
 * Generic help class
 * 
 * Provides a generic help class
 * Example usage:
 * $glossary = new HWTHelp('def_link', 'glossary.php', 'sc_gloss');
 * $glossary->getLink('gloss_path'); // For making links in the page
 * $glossary->getJSON('gloss_path'); // For AJAX calls
 * foreach ($glossary as $id => $entry) {} // Iterator over help entries
 * $glossary->getHelpPage(); // To generate a series of divs with labels in <h2> elements
 *
 * @todo Store help systems in an associative array indexed by path to xml file to avoid reloading or use $_PHPSESSION
 */
class HWTHelp implements Iterator
{
    private $_entries = array();
    private $_markup_flag;
    private $_filter;
    private $_help_page;
    private $_index;
    private $_ids;

    /**
     * Constructor for the HWTHelp system
     * 
     * @param string $markup_flag CSS class id for markup
     * @param path   $help_page   Path to a help page with a part of the page generated by getHelpPage()
     * @param string $filter      Filter (a column in the help database) to apply to the entries (null gets everything)
     * 
     * @return void
     * @todo Make so that a URL can be used: right now only a file
     */
    public function __construct($markup_flag, $help_page, $filter=null)
    {
        //$this->_url = $url;
        $this->_markup_flag = $markup_flag;
        $this->_help_page = $help_page;
        $this->_filter = $filter;
        //$parser = new HWTHelpParser();
        $this->_entries = self::getHelpDB($filter);  // $parser->parse($url);
        $this->_ids = array_keys($this->_entries);
        
        // Initalize the iterator
        $this->_index = 0;
    }
    
     /**
     * Connects to help entries database via remote helper file
     * 
     * @return an array of entries
     */   
    private static function getHelpDB($filter = null)
    {
        global $helpdb_include_path;
        $includefile = $helpdb_include_path . 'db_gdrs_help.inc.php';
        if (is_file($includefile)) { include $includefile; } else { die("Can't read " . $includefile); }
        if ($filter) {
            $filter_text = ' WHERE ' . $filter . '=1';
        } else {
            $filter_text = '';
        }
        try
        {
          $sql = 'SELECT id, code_id, entry_title, entry_text FROM entry' . $filter_text . ' ORDER BY entry_title;';
          $result = $pdo->query($sql);
        }
        catch (PDOException $e)
        {
          $error = 'Error fetching entries: ' . $e->getMessage();
          include $helpdb_include_path . 'error.html.php';
          exit();
        }

        // ... store results of query in an array ...
        $temp_entries = array();
        foreach ($result as $row)
        {
          $temp_entries[$row['code_id']] = array(
                    'db_id' => $row['id'], 
                    'label' => $row['entry_title'], 
                    'text'  => $row['entry_text']);
          
        }
        return $temp_entries;
    }
    
    /**
     * Get all the help entries
     * 
     * @return array of help entries
     */
    public function getEntries() {
        return $this->_entries;
    }
    
    /**
     * Get all the help ids
     * 
     * @return array of help ids 
     */
    public function getIds() {
        return $this->_ids;
    }
    
    /**
     * Sends you to the first help entry
     * 
     * @return void
     */
    public function rewind()
    {
        $this->_index = 0;
    }
    
    /**
     * Return the current entry
     * 
     * @return array Has the structure array('label' => '', 'text' => '')
     */
    public function current()
    {
        return $this->_entries[$this->_ids[$this->_index]];
    }
    
    /**
     * Return the id for the current entry
     * 
     * @return string Short identifier for the entry
     */
    public function key()
    {
        return $this->_ids[$this->_index];
    }
    
    /**
     * Increments the internal entry pointer
     * 
     */
    public function next()
    {
        $this->_index++;
    }
    
    /**
     * Say whether entry at current internal pointer exists
     * 
     * @return boolean Say whether entry is valid or not
     */
    public function valid()
    {
        return isset($this->_ids[$this->_index]);
    }
    
    /**
     * Generate JSON-encoded entry array for the specified ID
     * 
     * @param string $id Short name for the entry
     * @return JSON JSON-encoded entry array
     */
    public function getJSON($id)
    {
        if (isset($this->_entries[$id])) {
            return json_encode($this->_entries[$id]);
        } else {
            throw new HWTHelpException('No entry for identifier "' . $id . '" found');
        }
    }
    
    /**
     * Generate HTML for link into help
     * 
     * @param string  $id       Short name for the entry
     * @param boolean $to_lower Flag whether to convert to lower case
     * @param string $link_text Text to display for link to help entry // TODO default back to $label if no link text provided 
     * 
     * @return HTML Link markup for item you're getting help entry for
     */
    public function getLink($id, $to_lower = false, $link_text = '')
    {
        if (!isset($this->_entries[$id])) {
            //throw new HWTHelpException('No entry for identifier "' . $id . '" found');
        }
        $html = '<a class="' . $this->_markup_flag . '"';
        $html .= ' href="' . $this->_help_page . '#' . $id . '"';
        $html .= ' target="_blank">';
        
        if ($link_text == '') {
            $label = $this->_entries[$id]['label'];
        } else {
            $label = $link_text;
        }
        if ($to_lower) {
            $label = strtolower($label);
        }
        
        $html .= $label;
        $html .= '</a>';
        
        return $html;
    }
    
    /**
     * Output a single help entry text
     * 
     * @param string $id       Short name for the entry
     * 
     * @return HTML text 
     */
    // TODO Fix this broken fn, use it in getHelpPage below and uncomment in how.php (Acknowlegements)
    public function getHelpEntry($id)
    {
        $retval = $this->_entries[$id]['text'];
        return $retval;
    }
    
    /**
     * Construct a list of help entries as part of an HTML page
     * 
     * @param string $label_elem HTML element to wrap labels in
     * 
     * @return HTML A series of divs with id = element id's, labels wrapped in $label_elem, and text 
     */
    public function getHelpPage($label_elem = "h2")
    {
        $retval = '';
        $retval .= '<nav class="group">' . PHP_EOL;
        $retval .= '<ul>' . PHP_EOL;
        foreach ($this->_entries as $id => $entry) {
            $retval .= '<li><a href="#' . $id . '">' . $entry['label'] . '</a></li>';
        }
        $retval .= '</ul>' . PHP_EOL;
        $retval .= '</nav>' . PHP_EOL;

        foreach ($this->_entries as $id => $entry) {
            $retval .= '<div id="' . $id . '">' . PHP_EOL;
            $retval .= '<' . $label_elem . '>' . $entry['label'];
            $retval .= '</' . $label_elem . '>' . PHP_EOL;
            $retval .= $entry['text'];
            $retval .= '</div>' . PHP_EOL;
        }
        return $retval;
    }
}

?>
