=== Formidable Plus ===
Contributors: topquarky
Tags: formidable, forms, table, new field-type
Requires at least: 2.5 ( Formidable Pro 1.07.04 or higher is required )
Tested up to: 3.8
Stable tag: 1.1.16

This plugin adds a new field type to the Formidable Pro plugin.  It allows you to add a table to your form.

== Description ==

*Please note: Formidable Pro version 1.07.04 or higher is required for this version of Formidable Plus to work.  Please see the FAQ.*

I'm a big fan of the [Stephanie Wells'](http://strategy11.com) [Formidable Pro](http://formidablepro.com) plugin.  On a recent project, the client needed to have a *table* field type for people entering financials vs. timeline.  This add-on to Formidable Pro is the result of that work.  

It integrates into Formidable Pro via the latter's wealth of filters and actions.  You can have any number of rows and any number of columns.  If you create a table without any rows (only columns), then the person filling out the form has the opportunity to add more rows as needed.  

Administrators can re-order, add & delete rows or columns and any existing data gets updated to retain integrity.  

Using special column/row naming nomenclature, administrators can turn the input field from the default `text` to either `textarea`, `select`, `radio` or `checkbox`.  See the FAQ for more information 

== Installation ==

1. Formidable Plus is available for purchase from [topquark.com](http://topquark.com)
1. Purchase/Download Formidable Plus from [topquark.com/extend/plugins/formidable-plus](http://topquark.com/extend/plugins/formidable-plus)
1. Install the ZIP file to your server and activate the plugin

== Frequently Asked Questions ==

= What version of Formidable Pro do I need? =

Recent releases of Formidable Pro have caused headaches with Formidable Plus - breaking things in unexpected places.  Because of this, I am unable to continually test F+ against all previous versions of FPro.  I am only able to support the current version of Formidable Pro.  Odds are good that it will work in previous versions, but I need to focus my development time on the current version.

*Current version of Formidable Pro supported: 1.07.04.*

= When I export data, the table fields look weird =

Version 1.1.10 of Formidable Plus finally addressed the issue of exporting data.  Previously, all that showed up was "Array,Array,Array".  Now, the actual table data gets exported.  But, there are a couple of things to consider.

Presenting a table's worth of data in a single spreadsheet cell is actually quite an interesting problem.  Formidable Plus solves it by outputting a *plain text* formatted version of the table. However, when you open up the exported CSV into Excel (or similar), it will only look good if you use a **fixed-width** font for that column.  You'll have to figure out how to change the font in your program.  Once you have a fixed width font, you can widen the column until the table data appears, looking something like:

`
                 | Column 2        | Column 1        |
------------------------------------------------------
 Row 4           | Yes             | No              |
------------------------------------------------------
 Radio Row       | on              |                 |
------------------------------------------------------
 Row 1           | I selected colu-| This is column -|
                 | mn 2            | 1               |
------------------------------------------------------
 Row 3           | Bananas         | Peaches         |
------------------------------------------------------
 Row 5           | Two, Three      | One, Four       |
------------------------------------------------------
 One Option      | Yes             |                 |
------------------------------------------------------
 Row 2           |                 | Checked Value   |
------------------------------------------------------
`

By default, the width of each column is 15 characters.  You can change this by using the filter `frmplus_csv_export_fixed_width` in the FrmPlusEntryMetaHelper class (see that file for reference).  

This is how Formidable Plus exports the data by default.  However, it is also possible to export it such that the data ends up in CSV format itself, but this will require a little massaging.  To enable CSV export for the table fields, you'll need to add something like the following to your functions.php file:

`add_filter('frmplus_csv_export_how','my_frmplus_csv_export_how',10,2);
function my_frmplus_csv_export_how($how,$field){
	if ($field->id == 123){ // change 123 to the field ID of the table field you want to export as CSV.  
		$how = 'csv';
	}
	return $how;
}`

There's a big catch with exporting the table data as CSV.  The data that gets exported will need to be massaged a little to be usable.  You'll essentially want to create a separate CSV file for each and every exported table field.  To do that, you'll need to open your exported CSV file in Excel (or similar), copy the contents of the table field, paste it into a text editor and then replace all instances of the special token "~:~" with \n (a linebreak).  Then save that as a CSV and open in Excel.  Why all this nonesense?  Well, for CSV files, putting the real line-breaks in causes problems.  For some strange Microsoft reason if you copy and paste a cell value, then it gets wrapped in double quotes and all existing double quotes are escaped as "".  This does not make it easy to get back into a table format.  Easier to just replace ~:~ with \n.

This is a new feature, and I welcome your feedback on it.  

= Can I create a table where the person filling out the form can add rows dynamically =

Yes.  To do this, simply create a table field and don't add any rows.  (Add and name as many columns as you'd like).  When that table gets rendered in the form, there will be options to add new rows

= How do I make the input field into a textarea for multiline input? =

If you give your column (or row) name a prefix of `textarea:` (i.e. `textarea:My Column Name`), then all cells in that column (or row) will be rendered as `<textarea>`, allowing multiiline input.

= How about Radio Buttons, Checkboxes and Dropdowns? =

As of Formidable Plus 1.1.0, you now have several options for input type within your table.  To use this feature, you will follow the example from the textarea above and prefix your column or row name with a field type.  Here are the available types:

* `textarea:{name}` - for multiline input (e.g. `textarea:My Row Name`)
* `checkbox:{name}` - for a checkbox with a checked value of `on` (e.g. `checkbox:My Row Name`)
* `checkbox:{name}:{value}` - for a checkbox with a checked value of `value` (e.g. `checkbox:My Row Name:Yes`)
* `checkbox:{name}:{value1|value2|value3|...}` - for a set of multiple checkboxes, each with a the corresponding checked value.  You can put as many options as you'd like, separating each by the `|` character (e.g. `checkbox:Fruits You Like:Apples|Oranges|Bananas`)
* `select:{name}:{value1|value2|value3|...}` - for a dropdown box. You can add as many options as you'd like to a dropdown box, separating each by the `|` character (e.g. `select:Choose a Fruit:Apples|Oranges|Bananas`)
* `radio:{name}:{value1|value2|value3|...}` - for a group of radio buttons within the table cell (e.g. `radio:My Favourite Car:Honda|Ford|Lincoln|Mazda`)
* `radioline:{name} - this will create a group of radio buttons across the entire row (or column), one button per cell that allows the user to choose an entire column (or row) (e.g. `radioline:Choose A Column`) 

= Can I dynamically fill the options for field types that allow them (select, radio, checkbox) = 
This is an advanced question and requires you to write an additional plugin and call a filter, but the short answer is yes, you can.  

If you're comfortable writing plugins, you'll want to `add_filter('frmplus_field_options','my_frmplus_field_options',10,5)` and then write something like this:

`function my_frmplus_field_options($options,$field,$name,$row_num,$col_num){
	// My table field is field 462 and the name of the row I want to affect is 'Fruits You Like'
	// I could also check the row_num or col_num to figure out if I want to update the options
	if ($field['id'] == '462' and $name == 'Fruits You Like'){
		static $fruit_options; // use a static because this function gets called ones for every cell in the row
		if (!isset($fruit_options)){
			$fruit_options = array('Bananas','Oranges','Apples','Peaches','Pears','Plums'); // or fill dynamically somehow
		}
		$options = $fruit_options;
	}
	return $options;
}`

= I inserted a table field into a custom display and all that shows up is "Array,Array" = 

This issue is resolved with Formidable Pro 1.05.04 and Formidable Plus 1.0.4.  Please upgrade.  

= Can I enable arrow-key navigation through a table? =

It's possible to enable users to use the arrow keys to navigate through a table.  This can be useful for large tables.  To do this, you need to add the class `use-arrow-keys` to your table.  To do that, edit your form in Formidable.  Find the Table field you want to affect and add `use-arrow-keys` to the CSS Layout Classes for the field.  Alternatively, you can do it in PHP (though this is no longer the recommended way):

`add_filter('frm_table_classes','my_table_classes',10,2);
function my_table_classes($classes,$field_id){
	$my_field_id = 12; // the field_id of your table field
	if ($field_id == $my_field_id){
		$classes[] = 'use-arrow-keys';
	}
	return $classes;
}`

= I have a big table and the user loses the column/row headings when navigating around it =

It's possible to enable a tooltip which pops up the row/column name when the user focusses on a table cell.  This can be useful for large tables.  To do this, you need to add the class `use-tooltips` to your table.  To do that, edit your form in Formidable.  Find the Table field you want to affect and add `use-tooltips` to the CSS Layout Classes for the field.  Alternatively, you can do it in PHP (though this is no longer the recommended way):

`add_filter('frm_table_classes','my_table_classes',10,2);
function my_table_classes($classes,$field_id){
	$my_field_id = 12; // the field_id of your table field
	if ($field_id == $my_field_id){
		$classes[] = 'use-tooltips';
	}
	return $classes;
}`

= People have made entries and I want to reorder/rename some of my rows/columns = 
 
No problem.  When you reorder, add or delete rows or columns, Formidable Plus will update all of your data to the appropriate new values.  No data will be lost.

== Screenshots ==

1. The admin view of a simple menu planning table
2. What the menu planner looks like to the end-user
3. The same form, but with all rows removed, and a new column to allow the end user to specify which meal.  They can add/delete rows
4. Examples with different field types within the table


== Changelog ==

= 1.1.16 = 
* Fix: dealt with lingering non-fatal PHP notices
* Fix: certain conditions that led to rendering of a large form taking forever (unnecessary parsing through replace_shortcodes)

= 1.1.15 = 
* Fix: update to Formidable Pro caused multipage forms to show serialized data in table fields.  

= 1.1.14 = 
* Fix: admin area can now add/delete columns & rows again. 

= 1.1.13 = 
* Fix: If excluding a table field in the formidable shortcode by using the fields= attribute, the values for table fields are now propagated properly
* Change: how use-tooltips and use-arrow-keys works.  This work removed the deprecated/removed function jQuery.live from the Formidable Plus scripts

= 1.1.12 = 
* Fix: Table columns & rows in admin page were not sortable anymore with Formidable 1.07.0.  Now they are.

= 1.1.11 = 
* Fix: Issue if multi-page form had HTML field containing a shortcode for a table field from earlier in the form

= 1.1.10 = 
* Fix: Issues with Formidable Pro 1.06.09 and 1.06.10. 
* New: Exporting table data now works, with options.  See the FAQ.

= 1.1.8.4 = 
* Fix: PHP error if a table field is conditionally hidden on submit.

= 1.1.8.3 = 
* Fix: [default-message] in the email notifications was not rendering the table.  

= 1.1.8.2 = 
* Fix: a bug which affected data submitted on a dynamic table containing selects or checkboxes when the user has deleted a row before submitting.

= 1.1.8.1 = 
* Fix: a problem that could occur if a table field is required.  The bug caused a warning message to be output to the browser.

= 1.1.8 = 
* Fix: a critical bug where a table field with only one row was getting saved improperly to the database, thanks to something new in FrmEntry::validate.  

= 1.1.7.2 = 
* Fix: a new issue editing entries with table fields. (thanks Rob Slowen)
* Fix: slashes being added when adding a new row via javascript (thanks @lenmason)

= 1.1.7.1 = 
* Fix: further addressed the compatibility problem fixed in 1.1.7 for a case that popped up (thanks bebetsy.com)

= 1.1.7 = 
* *Critical Update* - fixed a compatibility problem with the latest version of Formidable Pro that caused data loss when editting entries with table fields

= 1.1.6.3rfc = 
* Fix: simple checkboxes now save the correct value (thanks Jason Hill for bringing the problem to my attention)

= 1.1.6.2rfc = 
* Fix: mutlibyte characters in textareas now properly encoded
* Added: on a dynamic table, when a row is added or deleted, the event 'add_row' or 'delete_row' is triggered on the table.  See `js/frm_plus.js` for details
* Deprecated: the post_add_row and post_delete_row methods, in favour of the event mechanism just added
* Added: a filter on the "Add Row" (frmplus-text-add-row) and "Delete Row" (frmplus-text-delete-row) text, to allow you to change those labels.  
* Added: an ajax indicator when you click Add Row

= 1.1.6.1rfc = 
* Fix: bug where td and tr elements weren't getting col-n and row-n classes.  (thanks JoeErsinghaus)
* Fix: unnecessary session() code removed from Entries Controller. (thanks glyphicwebdesign.com)

= 1.1.6 = 
* Critical fix: a bug that could have resulted in lost data when reordering columns on a table field a multipage form.  

= 1.1.5 = 
* Fix: a bug that prevented adding columns to a table field. A PHP error was being thrown trying to use a string as an array.
* Change: the main javascript file, frm_plus.js, is now plain javascript, as opposed to js.php.  This is because some installations of WordPress do not allow loading of .php files as scripts. They return a 404.  

= 1.1.4 = 
* Fixed: a table field on an earlier page in a multi-page form now gets saved properly (requires Formidable Pro 1.6.x - yet to be released).  If you're needing to use this on a multi-page form on Formidable 1.5.x, please contact support@topquark.com to obtain a formidable patch.  
* Added: a column-{n} class to each <td> element in the table.  Should allow better CSS control over individual columns

= 1.1.3 = 
* Emailed form results now properly display table field types.  (Note: looks like crap if you choose to send Plain Text)

= 1.1.2 = 
* Fixed a warning thrown on non-administrator profile page

= 1.1.1 = 
* Updated Top Quark credentials to work on multisite

= 1.1.0 = 
* Added new field types, allowing table to have checkboxes, radio buttons & dropdowns

= 1.0.4 =
* Changed the settings page to access the proper 'formidable-plus' settings page, instead of the generic 'plugin' settings page
* Used new hook in Formidable Pro 1.05.04 for Custom Displays.  The `[frm_table_display]` shortcode from Plus version 1.0.3 is now deprecated

= 1.0.3 =
* Added a shortcode for custom displays - `[frm_table_display id=N]` (where N is the field ID).  Works around the "Array,Array" problem when inserting a table field into a custom display

= 1.0.2 =
* Added TopQuark.com authentication

= 1.0.1 =
* Initial check-in

== Upgrade Notice ==

= 1.0.4 =
You will have to re-enter your TopQuark credentials on the Settings > Formidable Plus page.

= 1.0.2 =
You will only be able to upgrade to this version after purchasing the plugin from [TopQuark.com](http://topquark.com/extend/plugins/formidable-plus)

= 1.0.1 =
No upgrade notice

