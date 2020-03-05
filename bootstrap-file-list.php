<?php
/**
 * Plugin Name:       Bootstrap File List
 * Plugin URI:        https://github.com/pl33/wp-bootstrap-file-list
 * Description:       File browser using Bootstrap layout.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Philipp Le
 * Author URI:        https://www.github.com/pl33
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 *
 * Copyright (c) 2020 Philipp Le
 *
 * This file is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This file is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file . If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

namespace bootstrap_file_list {

/**
 * Information about a file is stored here. The class provides functions
 * to retrieve file information and convert information to human-readable
 * strings.
 */
class file_info
{
    /** @brief Full file path in the file system */
    private $_path;
    /** @brief URI with download or navigation link */
    private $_uri;
    /** @brief Type type: @c dir or @c file */
    private $_type;
    /** @brief File size in bytes */
    private $_size;
    /** @brief UNIX timestamp of last modification */
    private $_mtime;

    /**
     * Please use retrieve() or create_up() to create a file_info object.
     */
    private function __construct()
    {
    }

    /**
     * @brief Read properties which are readable
     *
     * @param name Name of the property
     * @return Value of the property
     */
    public function __get($name)
    {
        switch ($name) {
        case 'path':
            return $this->_path;
        case 'name':
            return basename($this->_path);
        case 'uri':
            return $this->_uri;
        case 'type':
            return $this->_type;
        case 'size':
            return $this->_size;
        case 'mtime':
            return $this->_mtime;
        default:
            throw new \TypeError('Property '. __CLASS__.'::'.$name.' is not readable.');
        }
    }

    /**
     * @brief Write properties if it is writable
     *
     * @param name Name of the property
     * @param value New value of the property
     */
    public function __set($name, $value)
    {
        switch ($name) {
        case 'uri':
            if (is_string($value))
                $this->_uri = $value;
            else
                throw new \TypeError('Property '. __CLASS__.'::'.$name.' is a string.');
            break;
        default:
            throw new \TypeError('Property '. __CLASS__.'::'.$name.' is not writable.');
        }
    }

    /**
     * @brief File information is retrieved
     *
     * Following file information are gathered: File size, last modification time, type
     * (directory or file)
     *
     * Both size and modification time are converted to strings. The size is printed in
     * a human readable format (Bytes, kiB, MiB, ...). The rounding precision can be set
     * in @c rounding_precision and is set to @c 2 by default.
     *
     * The time is printed in "Y-m-d, H:i:s O".
     *
     * @param file_path Path to the file
     * @return File information entry
     */
    public static function retrieve($file_path): file_info
    {
        $info = new file_info();
        $info->_uri = '';

        // Query file stats
        $info->_path = $file_path;
        $stats = stat($file_path);

        // Copy stats
        $info->_mtime = $stats['mtime'];
        if (is_dir($file_path)) {
            $info->_type = 'dir';
            $info->_size = -1;
        } else {
            $info->_type = 'file';
            $info->_size = $stats['size'];
        }

        return $info;
    }

    /**
     * This function creates a pseudo-entry whose name is "..". It can
     * be used to link to the parent directory.
     *
     * @return File information entry
     */
    public static function create_up(): file_info
    {
        $info = new file_info();
        $info->_uri = '';

        $info->_path = '..';
        $info->_type = 'dir';
        $info->_size = -1;
        $info->_mtime = -1;

        return $info;
    }

    /**
     * @brief Check if the file is a directory
     *
     * @return @c true if the file is a directory, @c false if the file is a regular file
     */
    public function is_dir(): bool
    {
        return ($this->_type == 'dir');
    }

    /**
     * This function returns @c true if
     * 1. the file is not a directory,
     * 2. the regular expression is not empty, and
     * 3. the regular expression does not match.
     * On return of @c true, the file is filtered out and shall not
     * be included in the output list.
     *
     * @param filter_regex Regular expression used to filter the file
     * @return @c true if the file should not be included in the output list, @false
     * if the file should be appended to output list
     */
    public function is_masked(string $filter_regex = ''): bool
    {
        $name = basename($this->_path);
        if (!$this->is_dir() and ($filter_regex != '') and (preg_match($filter_regex, $name) != 1))
            return true;
        else
            return false;
    }

    /**
     * @brief Convert the file size to a human-readable string
     *
     * If there is no valid size, @c - is returned. Else, the size is
     * converted to a string. The number is displayed as a number in
     * bytes with the closest prefix. The size is rounded to the
     * number digits after the decimal point which is set in
     * @c rounding_precision.
     *
     * @param rounding_precision Number of digits after the decimal point
     * @return File size as a human-readable string
     */
    public function human_readable_size($rounding_precision = 2): string
    {
        if ($this->_size < 0)
            $size_str = '-';
        elseif ($this->_size >= pow(2, 50))
            $size_str = round($this->_size / pow(2, 50), $rounding_precision) . ' PiB';
        elseif ($this->_size >= pow(2, 40))
            $size_str = round($this->_size / pow(2, 40), $rounding_precision) . ' TiB';
        elseif ($this->_size >= pow(2, 30))
            $size_str = round($this->_size / pow(2, 30), $rounding_precision) . ' GiB';
        elseif ($this->_size >= pow(2, 20))
            $size_str = round($this->_size / pow(2, 20), $rounding_precision) . ' MiB';
        elseif ($this->_size >= pow(2, 10))
            $size_str = round($this->_size / pow(2, 10), $rounding_precision) . ' kiB';
        else
            $size_str = $this->_size . ' Bytes';

        return $size_str;
    }

    /**
     * @brief Convert the modification time to a string
     *
     * If there is no modification time, @c - is returned. If there is a valid
     * modification time, the format will be 'Y-m-d, H:i:s O'.
     *
     * @return Modification time as a string
     */
    public function mtime_string(): string
    {
        if ($this->_mtime < 0)
            return '-';
        else
            return date('Y-m-d, H:i:s O', $this->_mtime);
    }

    /**
     * @brief Sort a file list
     *
     * A list of files is sorted. The sorting is controlled by the @c sorting argument
     * as follows:
     * * If @c sorting is @c name_asc: The list is sorted by the file name in ascending order.
     * * If @c sorting is @c name_des: The list is sorted by the file name in descending order.
     * * If @c sorting is @c mtime_asc: The list is sorted by the modification date in ascending order.
     *   If two or more files were modified at the same time, they will be sorted by the file name in
     *   ascending order.
     * * If @c sorting is @c mtime_des: The list is sorted by the modification date in descending order.
     *   If two or more files were modified at the same time, they will be sorted by the file name in
     *   descending order.
     * * If @c sorting is @c size_asc: The list is sorted by the file size in ascending order.
     *   If two or more files are of same size, they will be sorted by the file name in
     *   ascending order.
     * * If @c sorting is @c size_des: The list is sorted by the file size in descending order.
     *   If two or more files are of same size, they will be sorted by the file name in
     *   descending order.
     *
     * @param entries Unsorted list of file entries
     * @param sorting Sorting key and direction
     * @return Sorted list of file entries
     */
    public static function sort(array $entries, string $sorting): array
    {
        $copy = $entries;
        usort($copy, function ($a, $b) use($sorting) {
            switch ($sorting) {
            case 'name_asc':
                return strcmp($a->name, $b->name);
            case 'name_des':
                return strcmp($b->name, $a->name);
            case 'mtime_asc':
                if ($a->mtime < $b->mtime)
                    return -1;
                elseif ($a->mtime > $b->mtime)
                    return 1;
                else
                    return strcmp($a->name, $b->name);
            case 'mtime_des':
                if ($a->mtime < $b->mtime)
                    return 1;
                elseif ($a->mtime > $b->mtime)
                    return -1;
                else
                    return strcmp($b->name, $a->name);
            case 'size_asc':
                if ($a->size < $b->size)
                    return -1;
                elseif ($a->size > $b->size)
                    return 1;
                else
                    return strcmp($a->name, $b->name);
            case 'size_des':
                if ($a->size < $b->size)
                    return 1;
                elseif ($a->size > $b->size)
                    return -1;
                else
                    return strcmp($b->name, $a->name);
            default:
                throw new \LogicException('Invalid sorting');
            }
        });
        return $copy;
    }
}


/**
 * The main logic of the plugin is implemented here. The shortcode @c BSFileList
 * is processed by this class. It will produce the HTML output of the file list
 * view.
 *
 * The file list view supports a listing of the files of the current directory.
 * Files can be filtered by regular expressions. The list can be sorted and it
 * is possible to navigate into sub-folders.
 *
 * The file list view can be customized by values set in the $_GET array. See
 * bootstrap_file_list::get_sub_key() and bootstrap_file_list::get_sorting_key()
 * for details.
 */
class bootstrap_file_list
{
    /**
     * Called by Wordpress to handle the shortcode @c BSFileList.
     *
     * The actual generation of the HTML code is handled by a instance of this class.
     */
    public static function shortcode_BSFileList($attr, $content='')
    {
        // Fill attributes by default values if not set
        $args = shortcode_atts(
            array(
                'folder' => false,
                'filter' => '',
                'sorting' => 'name_asc',
                'show_size' => true,
                'show_mtime' => false
            ),
            $attr
        );

        try {
            // Check arguments
            if ($args['folder'] === false)
                throw new \RuntimeException('"folder" attribute is not set.');

            // Generate HTML code
            $plugin = new bootstrap_file_list($args['folder'], $args['filter'], $args['sorting'], $args['show_size'], $args['show_mtime']);
            return $plugin->get_html();
        } catch (\RuntimeException $e) {
            return 'BSFileList error: ' . $e->getMessage();
        }
    }

    /**
     * Some parameters are set in the $_GET array to control the view generated by
     * the @c BSFileList shortcode. To distinguish between multiple file lists on
     * the same page, an ID is used to multiplex. The ID is generated from the current
     * ID of the Wordpress post or page and the MD5 hash of the root folder. Therefore,
     * the ID is not unique when two file lists on the same page point to the same
     * root folder.
     */
    private $id;
    /**
     * The root folder is the top-level folder of the file list view. The path given here
     * is relative to the Wordpress upload directory.
     */
    private $root_folder;
    /**
     * The sub-folder is given relative to the root folder. It cannot traverse above the
     * root folder. For example, a value of "../" is invalid.
     */
    private $sub_folder;
    /**
     * The current directory is composed of Wordpress upload directory +
     * root folder + sub folder. The absolute filesystem path to the current
     * folder is stored here.
     */
    private $folder_dir;
    /**
     * The current directory is composed of Wordpress upload directory +
     * root folder + sub folder. The HTTP URI to the current folder is stored here.
     */
    private $folder_uri;
    /**
     * Regular expression used to filter file names, but not directory names.
     */
    private $filter;
    /**
     * Sorting configuration defining the sorting key and direction.
     */
    private $sorting;
    /**
     * @c true when to file size should be shown in the file list.
     */
    private $show_size;
    /**
     * @c true when to last modification time should be shown in the file list.
     */
    private $show_mtime;

    /**
     * @brief Class constructor
     */
    public function __construct(string $folder, string $filter = '', string $sorting = 'name_asc', bool $show_size = true, bool $show_mtime = false)
    {
        $this->id = get_the_ID().'x'.hash("md5", $folder);

        $this->root_folder = $folder;
        if (isset($_GET[$this->get_sub_key()]))
            $this->sub_folder = $this->sanitize_sub_path($_GET[$this->get_sub_key()]);
        else
            $this->sub_folder = '';

        if (isset($_GET[$this->get_sorting_key()]))
            $this->sorting = $this->sanitize_sub_path($_GET[$this->get_sorting_key()]);
        else
            $this->sorting = $sorting;

        $this->filter = $filter;
        $this->show_size = $show_size;
        $this->show_mtime = $show_mtime;
    }

    /**
     * The sub-folder path is set in the $_GET array and will be read on construction of this
     * class. This function generates the parameter key in the GET URI.
     *
     * @return Parameter key of the sub-folder path
     */
    private function get_sub_key(): string
    {
        return 'bsfilelist_'.$this->id.'_sub';
    }

    /**
     * The sorting key is set in the $_GET array and will be read on construction of this
     * class. This function generates the parameter key in the GET URI.
     *
     * @return Parameter key of the sorting key
     */
    private function get_sorting_key(): string
    {
        return 'bsfilelist_'.$this->id.'_sorting';
    }

    /**
     * Path sanitation comprises:
     * 1. Removing unnecessary elements from the path. @c . and empty elements do not
     *    change the folder and are removed.
     * 2. @c .. changes to the parent folder. Both the @c .. and the parent item are
     *    removed. If there is no parent item, the user tries to jailbreak above the
     *    root folder. This is impeded by throwing a runtime exception.
     *
     * @warning All user inputs must be sanitized. Malicious attackers may harm the system.
     *
     * @param value Path which should be checked
     * @return Sanitized path
     */
    private function sanitize_sub_path(string $path): string
    {
        // Split path by /
        $old_list = explode('/', $path);
        // Process elements
        $new_list = array();
        foreach ($old_list as $name) {
            if (($name == '.') or ($name == '')) {
                // Remove entries pointing to the current directory
            } elseif ($name == '..') {
                // Remove at entry on .., excepting when we are at top-level
                if (count($new_list) > 0)
                    array_pop($new_list);
                else
                    throw new \RuntimeException('Sanitizing error: Cannot traverse above root folder.');
            } else {
                // Copy regular entries
                array_push($new_list, $name);
            }
        }
        // Join path with /
        return implode('/', $new_list);
    }

    /**
     * Only some values are valid as a sorting key. This function ensures that only a valid
     * value is returned. If the inputted value is invalid, the default value is returned.
     *
     * @warning All user inputs must be sanitized. Malicious attackers may harm the system.
     *
     * @param value Value which should be checked
     * @return Same value if valid or default value if invalid
     */
    private function sanitize_sorting(string $value): string
    {
        if (in_array($value, array(
            'name_asc',
            'name_des',
            'mtime_asc',
            'mtime_des',
            'size_asc',
            'size_des'
        )))
            return $value;
        else
            return 'name_asc';
    }

    /**
     * @brief Generate filesystem path and URI of the current directory
     *
     * The current directory is composed of Wordpress upload directory +
     * root folder + sub folder. The filesystem path and the base URI are
     * composed here.
     *
     * The current folder must be a subfolder of the root folder. Otherwise,
     * a runtime exception will be thrown.
     */
    private function make_folder_dir_info(): void
    {
        // Get path and URI of Wordpress upload directory
        $upload_dir = wp_upload_dir();

        // Remove heading and tailing /
        $root_folder = rtrim($this->root_folder, '/');
        $root_folder = ltrim($root_folder, '/');
        $sub_folder = rtrim($this->sub_folder, '/');
        $sub_folder = ltrim($sub_folder, '/');
        $folder = rtrim($root_folder.'/'.$sub_folder, '/');
        $folder = ltrim($folder, '/');

        // Save path and URI of the current folder
        $this->folder_dir = realpath(rtrim(
            $upload_dir['basedir'].'/'.$folder,
             '/'
        ));
        $this->folder_uri = rtrim(
            $upload_dir['baseurl'].'/'.$folder,
            '/'
        );

        // The current folder must exist
        if (!is_dir($this->folder_dir))
            throw new \RuntimeException('The path is not a directory.');

        // Check that requested folder is a subfolder of the root folder
        $root_dir = rtrim($upload_dir['basedir'].'/'.$root_folder, '/');
        if (strpos($this->folder_dir, realpath($root_dir)) !== 0)
            throw new \RuntimeException('Cannot traverse above root folder.');
    }

    /**
     * @brief Generate a list of files in the current directory
     *
     * All files in the current directory (Wordpress upload directory + root folder +
     * sub folder) are listed. Hidden files starting with @c . are ignored.
     *
     * The generated file list will consist of two groups. First group comprises all
     * directories. The second group are the files. Both groups will be separately sorted
     * each for itself. Directories will have a fixed size of size. Sorting them by size
     * will not have any effect. Sorting does not interleave directories and files. Both
     * groups are kept separate. The sorting key is defined in the @c sorting class member.
     *
     * If the current directory is not the root directory, a link to the parent directory
     * is added in front of the directory group.
     *
     * A filter defined by a regular expression is applied to the files group, if set. No
     * filter is applied to the directory group.
     *
     * @return List of files
     */
    private function get_file_list(): array
    {
        // Read the content of the directory
        $files = scandir($this->folder_dir);

        // Scan the directory contents
        $dir_list = array();
        $file_list = array();
        foreach ($files as $file) {
            // Filter out hidden files
            if ($file[0] == '.')
                continue;

            // Retrieve file information
            $file_path = $this->folder_dir . '/' . $file;
            $entry = file_info::retrieve($file_path);

            // Apply filter, if entry is not a directory
            if ($entry->is_masked($this->filter))
                continue;

            // Create URI
            if (is_dir($file_path)) {
                $entry->uri = $this->get_chdir_uri($file);
            } else {
                $entry->uri = $this->folder_uri . '/' . $file;
            }

            // Put entry on correct list
            if ($entry->is_dir())
                array_push($dir_list, $entry);
            else
                array_push($file_list, $entry);
        }

        // Add .. entry, except when we are at top-level
        $pre_entries = array();
        if ($this->sanitize_sub_path($this->sub_folder) != '') {
            $entry = file_info::create_up();
            $entry->uri = $this->get_chdir_uri('..');
            array_push($pre_entries, $entry);
        }

        $sorting = $this->sanitize_sorting($this->sorting);
        return array_merge(
            $pre_entries,
            file_info::sort($dir_list, $sorting),
            file_info::sort($file_list, $sorting)
        );
    }

    /**
     * @brief Generate URI with applies a new sub-folder path (relative path to current location)
     *
     * The sub-folder path is set in the $_GET array and will be read on construction of this
     * class. This function generates an URI which sets a new sub-folder path relatively path to current location.
     *
     * @param rel_path Desired sub-folder path, relative path to current location
     * @return URI with sorting key applied
     */
    private function get_chdir_uri(string $rel_path)
    {
        // Generate URI with modified path
        return add_query_arg(
            array(
                $this->get_sub_key() => $this->sanitize_sub_path($this->sub_folder.'/'.$rel_path)
            ),
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * @brief An paragraph containing the current path is printed
     *
     * The location can be printed in front of the file table. It contains the
     * current path from the folder defined in @c root_folder. All elements are
     * links that switch the sub-folder to that location.
     *
     * @return HTML code
     */
    private function make_location(): string
    {
        // Print current path
        $location = explode('/', $this->sanitize_sub_path($this->sub_folder));
        $html = '<p><strong>Location:';
        $html .= ' &nbsp; <span class="oi oi-folder" aria-hidden="true"></span><a href="'.$this->get_setdir_uri('').'"> /</a>';
        $current_path = array();
        foreach ($location as $dir) {
            if ($dir == '')
                continue;
            array_push($current_path, $dir);
            $html .= ' &nbsp; <span class="oi oi-folder" aria-hidden="true"></span> ';
            $html .= '<a href="'.$this->get_setdir_uri(implode('/', $current_path)).'">'.$dir.' /</a>';
        }
        $html .='</strong></p>';

        return $html;
    }

    /**
     * @brief Generate the main table containing the file list
     *
     * HTML code of the file table is generated. The files are printed in the
     * order of the argument @c file_list.
     *
     * @param file_list List of files
     * @return HTML code
     */
    private function make_table(array $file_list): string
    {
        $html = '<table class="table">';

        // Print table head with sorting options
        $html .= '<thead><tr>';
        $html .= '<th width="30"></th>';
        $html .= '<th>'.$this->make_table_head_entry('Name', 'name').'</th>';
        if ($this->show_size)
            $html .= '<th width="20%">'.$this->make_table_head_entry('Size', 'size').'</th>';
        if ($this->show_mtime)
            $html .= '<th width="30%">'.$this->make_table_head_entry('Modified', 'mtime').'</th>';
        $html .= '</tr></thead>';

        // Print entries
        $html .= '<tbody>';
        foreach ($file_list as $entry) {
            $html .= '<tr>';

            // Type
            if ($entry->type == 'dir')
                $html .= '<td><span class="oi oi-folder" aria-hidden="true"></span></td>';
            elseif ($entry->type == 'file')
                $html .= '<td><span class="oi oi-document" aria-hidden="true"></span></td>';
            else
                $html .= '<td></td>';

            // Name with download link
            $html .= '<td><a href="'.$entry->uri.'">'.$entry->name.'</a></td>';

            // Information
            if ($this->show_size)
                $html .= '<td>'.$entry->human_readable_size().'</td>';
            if ($this->show_mtime)
                $html .= '<td>'.$entry->mtime_string().'</td>';

            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }

    /**
     * @brief Generate URI with applies a new sub-folder path (full relative path from top-level folder)
     *
     * The sub-folder path is set in the $_GET array and will be read on construction of this
     * class. This function generates an URI which sets a new sub-folder path to the full relative
     * path from top-level folder.
     *
     * @param path Desired sub-folder path, full relative path from top-level folder
     * @return URI with sorting key applied
     */
    private function get_setdir_uri(string $path)
    {
        // Generate URI with modified path
        return add_query_arg(
            array(
                $this->get_sub_key() => $this->sanitize_sub_path($path)
            ),
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * @brief Generate HTML code of table head
     *
     * The table head contains the field name. Clicking it should reverse the sorting.
     * A link is inserting which points to an URI that reverses the sorting direction,
     * or makes the field the sorting key. In the case that the field is the current
     * sorting key, an icon indication the sorting direction is added.
     *
     * @param text Text of the field
     * @param sort_key Sorting key of the field, without @c _asc or @c _des suffix
     * @return HTML code
     */
    private function make_table_head_entry(string $text, string $sort_key): string
    {
        $sorting = $this->sanitize_sorting($this->sorting);

        // Generate table head entry and print the sorting icon, if applicable
        if ($sorting == $sort_key.'_asc')
            return '<a href="'.$this->get_sort_uri($sort_key.'_des').'"><span class="oi oi-caret-top" aria-hidden="true"></span> '.$text.'</a>';
        elseif ($sorting == $sort_key.'_des')
            return '<a href="'.$this->get_sort_uri($sort_key.'_asc').'"><span class="oi oi-caret-bottom" aria-hidden="true"></span> '.$text.'</a>';
        else
            return '<a href="'.$this->get_sort_uri($sort_key.'_asc').'">'.$text.'</a>';
    }

    /**
     * @brief Generate URI with applies a new sorting key
     *
     * The sorting key is set in the $_GET array and will be read on construction of this
     * class. This function generates an URI which sets a new sorting key.
     *
     * @param sorting Desired sorting key
     * @return URI with sorting key applied
     */
    private function get_sort_uri(string $sorting)
    {
        // Generate URI with modified sorting key
        return add_query_arg(
            array(
                $this->get_sorting_key() => $this->sanitize_sorting($sorting)
            ),
            $_SERVER['REQUEST_URI']
        );
    }

    /**
     * @brief Generate the HTML output of the shortcode
     *
     * @return HTML code
     */
    public function get_html(): string
    {
        $this->make_folder_dir_info();
        $file_list = $this->get_file_list();

        $html = $this->make_location();
        $html .= $this->make_table($file_list);
        return $html;
    }
}


/**
 * @brief Init hook of the plugin
 *
 * The init hook registers the shortcode @c BSFileList
 */
function init_hook(): void
{
    add_shortcode(
        'BSFileList',
        '\bootstrap_file_list\bootstrap_file_list::shortcode_BSFileList'
    );
}


} /* namespace bootstrap_file_list */

namespace {
// Register plugin at init hook
add_action('init', '\bootstrap_file_list\init_hook', 5);
}
?>