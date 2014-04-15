=== Formidable Plus ===
Contributors: topquarky
Tags: formidable, forms, table, new field-type
Requires at least: 2.5 ( Formidable Pro 1.07.04 or higher is required )
Tested up to: 3.8.2
Stable tag: 1.2.1

This plugin adds a new field type to the Formidable Pro plugin.  It allows you to add a table to your form.

Please Note: Version 1.2 is a fairly major upgrade.  We **strongly** recommend backing up your database before upgrading. 

== Description ==

*Please note: See the FAQ for information on which most recent version of Formidable Pro is supported.*

Formidable Plus is an add-on to [Strategy 11's](http://strategy11.com) great [Formidable Pro](http://formidablepro.com) plugin.  It adds a *table* field type that allows you to request data in a tabular form.  You can fill a table with many different types of fields, from simple text inputs or checkboxes, to datepickers and calculation fields.  See the FAQ for all available field types.  

You can have any number of rows and any number of columns.  You can even create a dynamic table, which allows users to add in new rows.  

== Installation ==

1. Formidable Plus is available for purchase from [topquark.com](http://topquark.com)
1. Purchase/Download Formidable Plus from [topquark.com/extend/plugins/formidable-plus](http://topquark.com/extend/plugins/formidable-plus)
1. Install the ZIP file to your server and activate the plugin

== Frequently Asked Questions ==

= What version of Formidable Pro do I need? =

Recent releases of Formidable Pro have caused headaches with Formidable Plus - breaking things in unexpected places.  Because of this, I am unable to continually test F+ against all previous versions of FPro.  I am only able to support the current version of Formidable Pro.  Odds are good that it will work in previous versions, but I need to focus my development time on the current version.

*Current version of Formidable Pro supported: 1.07.06.*

= What kinds of fields can I include in a Formidable Plus table? = 
When you add a table field to your form, the edit widget gives you a dropdown box for each row and column.  The dropdown box contains the available field types.  Currently, these are:

* Text - a plain text input field
* Textarea - a multiline text input field
* Select - a dropdown or autocomplete field
* Checkbox - a series of checkboxes (select zero or more)
* Radio - a series of radio buttons (select only one)
* Radioline - one radio button per cell (in the row or column)
* Datepicker - a datepicker field using the jQuery DatePicker plugin
* Calculation - give a sum, average or count of fields in the table
* Data From Entries - create select, checkbox or radio field based on values entered in another form, or from a WordPress taxonomy
* Incrementer - a readonly incrementing number

= I tried to upgrade and received a message The package could not be installed. PCLZIP_ERR_BAD_FORMAT (-10) : Unable to find End of Central Dir Record signature = 
It’s most likely that the Top Quark credentials are not entered properly on the Settings > Formidable Plus page.  Go there, enter the credentials you received when you purchased the plugin, get the "Awesome! You're good to go!" message, and then visit your plugins page. Add `?forceCheck=true` to the end of the plugins.php url.  Then, you should be able to run the update properly.

= I see there's an update, but it says my subscription has run out.  What's up? = 
It’s most likely that the Top Quark credentials are not entered properly on the Settings > Formidable Plus page.  Go there, enter the credentials you received when you purchased the plugin, get the "Awesome! You're good to go!" message, and then visit your plugins page. Add `?forceCheck=true` to the end of the plugins.php url.  Then, you should be able to run the update properly.

= I'm a developer; is there a way for me to add my own field type? =
Yes.  See the controller files for Calculations, DataFromEntries, DatePicker and Incrementer in the `formidable-plus/classes/controllers/` directory for examples on how to do this.  

= Can I create a table where the person filling out the form can add rows dynamically =

Yes.  To do this, simply create a table field and don't add any rows.  (Add and name as many columns as you'd like).  When that table gets rendered in the form, there will be options to add new rows

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

= Can I enable arrow-key navigation through a table? =

It's possible to enable users to use the arrow keys to navigate through a table.  This can be useful for large tables.  To do this, you need to add the class `use-arrow-keys` to your table.  You can add that to the CSS layout classes field under Field Options.

= I have a big table and the user loses the column/row headings when navigating around it =

It's possible to enable a tooltip which pops up the row/column name when the user focusses on a table cell.  This can be useful for large tables.  To do this, you need to add the class `use-tooltips` to your table.  You can add that to the CSS layout classes field under Field Options.

= People have made entries and I want to reorder/rename some of my rows/columns = 
 
No problem.  When you reorder, add or delete rows or columns, Formidable Plus will update all of your data to the appropriate new values.  No data will be lost.

== Screenshots ==

1. The admin view of a a simple table with calculations and a datepicker
2. What the user sees for the calculations table when they are filling out the form
3. Examples with different field types within the table
4. What the field looks like in a display or email context


== Changelog ==

= 1.2.1 =
* Feature: Calculation fields can now place calculations into other fields on the form
* New: Calculation fields can now calculate the product (multiplication) of cells 
* New: The Incrementer field can have different styles (1 2 3 or A B C or I II III)
* New: The Incrementer field can start at any number and can have a suffix

= 1.2 = 
* Major Change: changed the UI for creating different field types within the table.  Now, instead of having to write things like `checkbox:Title:apples|oranges|bananas`, you use a simple dropdown to choose the field type and a settings button to set the different options
* New Field Types: Formidable Plus now ships with 10 built in field types, including Calculation, Datepicker and Data from Entries - common requests amongst Formidable Plus users
* Feature: Select fields can now be marked as multiselect, which allows users to choose more than one value from the list
* Feature: Select fields can now be marked as autocomplete, which changes the field in the table to be one where users can type and be presented with a list of available options
* Fix: removed more lingering non-fatal PHP notices

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

