# Formidable Plus #
Contributors: Trevor Mills
Tags: formidable, forms, table, new field-type
Requires at least: 3.0 ( Formidable Pro 2.0 or higher is required )
Tested up to: 4.9
Stable tag: 2.0.5

*This plugin is no longer in active development or support.  You are free to [download the ZIP file](https://github.com/TrevorMills/formidable-plus/archive/2.0.5.zip) and use it on your WordPress site.  If you have questions, you can use the [Github Issues](https://github.com/TrevorMills/formidable-plus/issues) page and I will try to help.  No automatic updates will be provided.*

This plugin adds a new field type to the Formidable Pro plugin.  It allows you to add a table to your form.

Please Note: Versions 2.x are released to work with Formidable Pro version 2.  If you have any issues at all, please contact me.

## Description ##

*Please note: See the FAQ for information on which most recent version of Formidable Pro is supported.*

Formidable Plus is an add-on to [Strategy 11's](http://strategy11.com) great [Formidable Pro](http://formidablepro.com) plugin.  It adds a *table* field type that allows you to request data in a tabular form.  You can fill a table with many different types of fields, from simple text inputs or checkboxes, to datepickers and calculation fields.  See the FAQ for all available field types.  

You can have any number of rows and any number of columns.  You can even create a dynamic table, which allows users to add in new rows.  

## Installation ##

1. [Download the ZIP file](https://github.com/TrevorMills/formidable-plus/archive/2.0.5.zip) from Github
1. Install the ZIP file to your server and activate the plugin

## Frequently Asked Questions ##

### What version of Formidable Pro do I need? ###

Recent releases of Formidable Pro have caused headaches with Formidable Plus - breaking things in unexpected places.  Because of this, I am unable to continually test F+ against all previous versions of FPro.  I am only able to support the current version of Formidable Pro.  Odds are good that it will work in previous versions, but I need to focus my development time on the current version.

*Current version of Formidable Pro supported: 2.05.06.*

### What kinds of fields can I include in a Formidable Plus table? ###
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
* Static - a readonly block of text, single or multiline

### I tried to upgrade and received a message The package could not be installed. PCLZIP_ERR_BAD_FORMAT (-10) : Unable to find End of Central Dir Record signature ### 
It’s most likely that the Top Quark credentials are not entered properly on the Settings > Formidable Plus page.  Go there, enter the credentials you received when you purchased the plugin, get the "Awesome! You're good to go!" message, and then visit your plugins page. Add `?forceCheck###true` to the end of the plugins.php url.  Then, you should be able to run the update properly.

### I see there's an update, but it says my subscription has run out.  What's up? ### 
It’s most likely that the Top Quark credentials are not entered properly on the Settings > Formidable Plus page.  Go there, enter the credentials you received when you purchased the plugin, get the "Awesome! You're good to go!" message, and then visit your plugins page. Add `?forceCheck###true` to the end of the plugins.php url.  Then, you should be able to run the update properly.

### In a custom view, can I control which rows/columns appear? ### 
Yes.  Within the shortcode you're adding to the view, add in any of the following attributes:
* include_rows - display only these rows
* include_columns - display only these columns
* exclude_rows - do not display these rows
* exclude_columns - do not display these columns
* hide_row_headers - set to 'true' to not display the row headers
* hide_column_headers - set to 'true' to not display the column headers

For the include/exclude attributes, use the row/column name, or the index (starting with 0 for the first row). 

Here's an example:

`[1228 include_rows###"Fruit,Store,Price" exclude_columns###"Potential Business" hide_row_headers###true]`

### I'm a developer; is there a way for me to add my own field type? ###
Yes.  See the controller files for Calculations, DataFromEntries, DatePicker and Incrementer in the `formidable-plus/classes/controllers/` directory for examples on how to do this.  

### Can I create a table where the person filling out the form can add rows dynamically ###

Yes.  To do this, simply create a table field and don't add any rows.  (Add and name as many columns as you'd like).  When that table gets rendered in the form, there will be options to add new rows

### When I export data, the table fields look weird ###

Version 1.1.10 of Formidable Plus finally addressed the issue of exporting data.  Previously, all that showed up was "Array,Array,Array".  Now, the actual table data gets exported.  But, there are a couple of things to consider.

Presenting a table's worth of data in a single spreadsheet cell is actually quite an interesting problem.  Formidable Plus solves it by outputting a *plain text* formatted version of the table. However, when you open up the exported CSV into Excel (or similar), it will only look good if you use a **fixed-width** font for that column.  You'll have to figure out how to change the font in your program.  Once you have a fixed width font, you can widen the column until the table data appears, looking something like:

```
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
```

By default, the width of each column is 15 characters.  You can change this by using the filter `frmplus_csv_export_fixed_width` in the FrmPlusEntryMetaHelper class (see that file for reference).  

This is how Formidable Plus exports the data by default.  However, it is also possible to export it such that the data ends up in CSV format itself, but this will require a little massaging.  To enable CSV export for the table fields, you'll need to add something like the following to your functions.php file:

```
add_filter('frmplus_csv_export_how','my_frmplus_csv_export_how',10,2);
function my_frmplus_csv_export_how($how,$field){
	if ($field->id ## 123){ // change 123 to the field ID of the table field you want to export as CSV.  
		$how ### 'csv';
	}
	return $how;
}
```

There's a big catch with exporting the table data as CSV.  The data that gets exported will need to be massaged a little to be usable.  You'll essentially want to create a separate CSV file for each and every exported table field.  To do that, you'll need to open your exported CSV file in Excel (or similar), copy the contents of the table field, paste it into a text editor and then replace all instances of the special token "~:~" with \n (a linebreak).  Then save that as a CSV and open in Excel.  Why all this nonesense?  Well, for CSV files, putting the real line-breaks in causes problems.  For some strange Microsoft reason if you copy and paste a cell value, then it gets wrapped in double quotes and all existing double quotes are escaped as "".  This does not make it easy to get back into a table format.  Easier to just replace ~:~ with \n.

This is a new feature, and I welcome your feedback on it.  

### Can I dynamically fill the options for field types that allow them (select, radio, checkbox) ### 
This is an advanced question and requires you to write an additional plugin and call a filter, but the short answer is yes, you can.  

If you're comfortable writing plugins, you'll want to `add_filter('frmplus_field_options','my_frmplus_field_options',10,5)` and then write something like this:

```
function my_frmplus_field_options($options,$field,$name,$row_num,$col_num){
	// My table field is field 462 and the name of the row I want to affect is 'Fruits You Like'
	// I could also check the row_num or col_num to figure out if I want to update the options
	if ($field['id'] ## '462' and $name ## 'Fruits You Like'){
		static $fruit_options; // use a static because this function gets called ones for every cell in the row
		if (!isset($fruit_options)){
			$fruit_options ### array('Bananas','Oranges','Apples','Peaches','Pears','Plums'); // or fill dynamically somehow
		}
		$options['options'] ### $fruit_options;
	}
	return $options;
}
```

### Can I enable arrow-key navigation through a table? ###

It's possible to enable users to use the arrow keys to navigate through a table.  This can be useful for large tables.  To do this, you need to add the class `use-arrow-keys` to your table.  You can add that to the CSS layout classes field under Field Options.

### I have a big table and the user loses the column/row headings when navigating around it ###

It's possible to enable a tooltip which pops up the row/column name when the user focusses on a table cell.  This can be useful for large tables.  To do this, you need to add the class `use-tooltips` to your table.  You can add that to the CSS layout classes field under Field Options.

### People have made entries and I want to reorder/rename some of my rows/columns ### 
 
No problem.  When you reorder, add or delete rows or columns, Formidable Plus will update all of your data to the appropriate new values.  No data will be lost.

## Screenshots ##

1. The admin view of a a simple table with calculations and a datepicker
2. What the user sees for the calculations table when they are filling out the form
3. Examples with different field types within the table
4. What the field looks like in a display or email context


## Changelog ##

### 2.0.5 ###
* Plugin now works with Formidable Pro version 2.05.06

### 2.0.3 ###
* Changed author from topquarky to Trevor Mills. I always hated that name.

### 2.0.0beta ### 
* Changes to Formidable Plus to make it work with Formidable Pro 2.0

### 1.2.6.1 ###
* Critical fix: Fatal error.  

### 1.2.6 ###
* Fix: Issue with exponential growth in calculation fields
* Fix: Datepicker after sorting bug ( thanks Scott Gallup )
* Fix: Radioline rows/columns now behave properly in calculations
* New: Radioline rows/columns can now take the value of the header, or can be an incrementer value.  

### 1.2.5 ### 
* Fix: fixing issues where table values that got stored as custom post meta were not showing up, and sometimes causing ksort error
* Change: allow calculation fields to be used in other calculations

### 1.2.4 ### 
* Fix: Trigger change on "other" fields that we fill in with FrmPlus calculations
* Fix: A couple of stray notices for non-initialized variables
* Fix: Empty table when doing frm_entries_edit_entry_ajax

### 1.2.3 ### 
* New: For Data from Entries fields, can place multiple values from same entry into other cells in the table
* Fix: Issue with displaying entries with empty columns
* Fix: Bug with Statics that prevented saved value from prepopulating form when editting entry

### 1.2.2 ### 
* New: calculation fields can have a prefix (like $) or suffix
* New: added ability to localize calculation fields (decimal character, thousands separator)
* New: added ability to hide row or column headings for table field in custom display
* New: added ability to include or exclude rows or columns when adding table field to custom display
* Fix: Bug that could have caused data corruption under some circumstances when reordering or deleting rows/columns
* New: For Data from Entries fields, can place another field value from the same entry into a different cell in the table
* New: Added options to Dynamic Tables - initial number of rows; user sortable; add/delete row labels

### 1.2.1 ###
* Feature: Calculation fields can now place calculations into other fields on the form
* New: Calculation fields can now calculate the product (multiplication) of cells 
* New: The Incrementer field can have different styles (1 2 3 or A B C or I II III)
* New: The Incrementer field can start at any number and can have a suffix

### 1.2 ### 
* Major Change: changed the UI for creating different field types within the table.  Now, instead of having to write things like `checkbox:Title:apples|oranges|bananas`, you use a simple dropdown to choose the field type and a settings button to set the different options
* New Field Types: Formidable Plus now ships with 10 built in field types, including Calculation, Datepicker and Data from Entries - common requests amongst Formidable Plus users
* Feature: Select fields can now be marked as multiselect, which allows users to choose more than one value from the list
* Feature: Select fields can now be marked as autocomplete, which changes the field in the table to be one where users can type and be presented with a list of available options
* Fix: removed more lingering non-fatal PHP notices
