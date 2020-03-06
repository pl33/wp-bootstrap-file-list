=== Plugin Name ===
Name: Bootstrap File List
Contributors: (this should be a list of wordpress.org userid's)
Tags: File List, Bootstrap
Requires at least: 5.2
Tested up to: 5.2
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
File browser using Bootstrap layout
 
== Description ==
 
The file list view supports a listing of the files of the current directory.
Files can be filtered by regular expressions. The list can be sorted and it
is possible to navigate into sub-folders.

To use the plugin just add
```
[BSFileList folder="/" filter="" sorting="name_asc" show_size="true" show_mtime="false"]
```
to your Wordpress post or page content.

wp-content/uploads/ or every folder below can be selected as the base folder by
the `folder` option.

`show_size` and `show_mtime` enable or disable displaying the file size and the
modification time, respectively.

`filter` can be set to any regular expression, which filter the files and only
shows the matching ones. Directories are not filtered.

`sorting` defines the initial sorting of the view.
* If `sorting` is `name_asc`: The list is sorted by the file name in ascending order.
* If `sorting` is `name_des`: The list is sorted by the file name in descending order.
* If `sorting` is `mtime_asc`: The list is sorted by the modification date in ascending order.
  If two or more files were modified at the same time, they will be sorted by the file name in
  ascending order.
* If `sorting` is `mtime_des`: The list is sorted by the modification date in descending order.
  If two or more files were modified at the same time, they will be sorted by the file name in
  descending order.
* If `sorting` is `size_asc`: The list is sorted by the file size in ascending order.
  If two or more files are of same size, they will be sorted by the file name in
  ascending order.
* If `sorting` is `size_des`: The list is sorted by the file size in descending order.
  If two or more files are of same size, they will be sorted by the file name in
  descending order.

The above code example shows the default values.
 
== Installation ==
 
1. Upload the contents of this directory to wp-content/plugins/bootstrap-file-list/
2. Active the plugin in the Plugins menu.
 
== Frequently Asked Questions ==
 

 
== Screenshots ==
 

 
== Changelog ==
 
= 1.0 =

Added: File browser using Bootstrap layout.
* Added table view using bootstrap
* Added sorting and filtering
* Added nagivation within sub-folders

