<?php

class FrmPlusEntryMetaHelper{
    function FrmPlusEntryMetaHelper(){
        add_filter('frm_display_value_custom', 'FrmPlusEntryMetaHelper::frmplus_display_value_custom', 10, 3);
        add_filter('frm_email_value', array($this, 'email_value'), 10, 3);
		add_filter('frm_hidden_value',array(&$this,'previous_fields_value'),10,2);
		add_filter('frm_csv_value',array(&$this,'frmplus_csv_value'),10,2);
		
		// Around 1.06.08, Formidable Pro introduced something that really tripped up F+.
		// Within FrmEntry::validate, there is now this line:
		//
		//	 if(is_array($value) and count($value) === 1)
        //  	 $_POST['item_meta'][$posted_field->id] = $value = reset($value); 
		//
		// which means that if a table is entered with just one row, then the table/array
		// nature of the posted field gets clobbered.  These two actions are my workaround for that.
		// The workaround is basically to add a 'spoof' row to every table field so count($value) != 1
		add_action('init',array(&$this,'setup_spoofs')); 
		add_filter('frm_validate_entry',array(&$this,'get_rid_of_spoofs'),10,2); // The last filter in FrmEntry::validate
		
		// See the comments at the supplied method
		add_action('init',array(&$this,'setup_multi_page_shortcodes'));
    }

	public static function frmplus_display_value_custom($value,$field,$atts){
		switch ($field->type){
		case 'table':
			$value = self::sanitize($value,false); // false means we skip stripping slashes (it's already been done)
			$display_only = true;
			$field->options = maybe_unserialize($field->options);
			$field = (array) $field;
			$field['value'] = $value;
			
			$field = FrmPlusFieldsHelper::adjust_for_attributes( $field, $atts ); 
			ob_start();
			require(FRMPLUS_VIEWS_PATH.'/frmplus-fields/table.php');
			$value = ob_get_clean();
			break;
		}		
		return $value;
	}
	
	function email_value($value, $meta, $entry){
		switch($meta->field_type){
		case 'table':
			$value = self::sanitize($value,true); // true means we'll stripslashes
			$display_only = true;
			$field = $meta;
			if (isset($meta->fi_options)){
				$field->options = maybe_unserialize($meta->fi_options);
			}
			else{
				// Oh Formidable, you're making my life very difficult these days.  
				// With 1.06.08, the way the [default-message] got rendered changed.  This seems to fix it.
				global $frm_field;
				$tmp = $frm_field->getOne($field->field_id);
				$field->options = maybe_unserialize($tmp->options);
			}
			$field = (array) $field;
			$field['value'] = $value;
			ob_start();
			require(FRMPLUS_VIEWS_PATH.'/frmplus-fields/table.php');
			$value = ob_get_clean();
			break;
		}
		return $value;
	}

	function previous_fields_value($value,$field){
		// If someone has entered the shortcode [formidable id=123 fields="46,38,29" entry_id="last"] (or similar)
		// where they're specifically NOT showing a table field, then the data is getting messed up.
		// By the time we're here, the $field['type'] is already set to 'hidden'.  So we need another
		// way to see if they're displaying a table field.
		
		static $table_fields;
		if (!isset($table_fields) and $field['type'] == 'hidden'){
			global $frmdb,$wpdb;
			$query = "SELECT id FROM $frmdb->fields WHERE type = 'table'";
			$table_fields = $wpdb->get_col($query);
		}
		
		if ($field['type'] == 'table' or ($field['type'] == 'hidden' and in_array($field['id'],$table_fields))){
			$value = stripslashes_deep($value);
			if (is_array($value)){
				$value = serialize($value);	
			}
			$value = esc_attr($value); // turns " into &quot;
		}
		return $value;
	}
	
	public static function sanitize($value,$strip = false){
		if (is_array($value) and array_key_exists(0,$value) and is_string($value[0])){
			if ($strip){
				$value = stripslashes_deep($value);
			}
			foreach ( $value as $k => $v ){
				if ( is_string( $v ) ){
					$value[$k] = html_entity_decode( $v );
				}
			}
			$value = array_map('maybe_unserialize',$value);
		}
		
		// Formidable Pro 1.06.08 introduced something where if there was only one row saved in a dynamic table
		// then it gets saved as array('column 1 value', 'column 2 value', ...) instead of array(array('column 1 value', 'column 2 value', ...))
		// I've added code to fix it, but I'm going to leave this in there to handle any data that might have been saved
		// while the issue existed
		if (is_array($value) and array_key_exists(0,$value) and is_string($value[0])){
			$value = array($value);
		}
		return $value;
	}
	
	public static function fetch($field_id,$entry_id){
		// Because of the way the metas get written to the database
		// I was having big troubles retaining proper " (double quote) ' (apostrophe)
		// and \ (slash).  The regular Formidable way of getting Entry meta
		// performed stripslashes at a place that broke the serialization of 
		// some (admittedly edge case) scenarios where the table contained
		// those characters. This was only a problem when displaying the form
		// for updating.  I call this function from views/frmplus-fields/form-fields.php
		global $frm_entry_meta,$frm_version;
		$value = $frm_entry_meta->get_entry_meta_by_field($entry_id,$field_id,false); // the false skips the stripslashes
		// Backward compatibility pre F+ version 1.1.7 and pre FPro 1.06.03
		if ($frm_version < '1.06.09' and is_array($value)){
			// The old way
			$value = array_map('maybe_unserialize',$value); // let's unserialize the sucker
			$value = stripslashes_deep($value); // here's where to strip slashes
			if( is_array($value)){
				foreach ($value as $num => $row){
					$value[$num] = array_map('maybe_unserialize',$row);  // now, unserialize all of the rows
				}
				$value = array_shift($value); // I don't know why I have to do this, but the thing I want is actually the first element of the array
			}
		}
		else{
			// The new way
			static $entries;
			if (!isset($entries)){
				$entries = array();
			}
			if (!array_key_exists($entry_id,$entries)){
				global $frm_entry;
				$entries[$entry_id] = $frm_entry->getOne($entry_id,true);
			}
			$entry = $entries[$entry_id];
			if ( !$entry || !isset( $entry->metas[$field_id] ) ){
				return null;
			}
			$value = maybe_unserialize($entry->metas[$field_id]);
			$value = stripslashes_deep($value); // here's where to strip slashes
			if( is_array($value)){
				foreach ($value as $num => $row){
					$value[$num] = maybe_unserialize($row);  // now, unserialize all of the rows
				}
			}

			// Formidable Pro 1.06.08 introduced something where if there was only one row saved in a dynamic table
			// then it gets saved as array('column 1 value', 'column 2 value', ...) instead of array(array('column 1 value', 'column 2 value', ...))
			// I've added code to fix it, but I'm going to leave this in there to handle any data that might have been saved
			// while the issue existed
			if (is_array($value) and array_key_exists(0,$value) and is_string($value[0])){
				$value = array($value);
			}
		}
		return $value;
	}
	
	function frmplus_csv_value($field_value,$args){
		if ($field_value !== false and $args['field']->type == 'table'){
			// Formidable Pro turns linebreaks into <br/>.  That's no good for us.  We actually
			// want the different rows to appear on different lines.  To accomplish this, we'll 
			// make use of the callback function in ob_start() to replace $line_break_spoof with
			// real linebreaks
			$line_break_spoof = '~:~'; // This is a delimiting string.  We're hoping that ~:~ doesn't appear in any data.  
			$reinstate_line_breaks = create_function('$s','return str_replace("'.$line_break_spoof.'","\r",$s);');
			
			// Get the options and the columns & Rows
			$options = maybe_unserialize($args['field']->options);
			list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($options);
			
			// Header row - this collects all of the column headers into an array to process later
			$header = array();
			if (is_array($rows) and count($rows)){
				$header[] = '';
			}
			foreach ($columns as $opt_key => $opt){
				$header[] = FrmPlusFieldsHelper::parse_option($opt,'name');
			}
			
			// Data rows - this collects all of the rows into an array to process later
			$data = array();			
			$field_value = self::sanitize($field_value);
			for($row = 0, $total = (count($rows) ? count($rows) : count($field_value)); $row < $total; $row++){
				$row_data = isset($field_value[$row]) ? $field_value[$row] : array();
				$tmp = array();
				$tmp_index = 0;
				if (count($rows)){
					$row_opt = current($rows);
					$tmp[$tmp_index++] = FrmPlusFieldsHelper::parse_option($row_opt,'name');
					next($rows);
				}
				else{
					$row_opt = null;
				}
				for ($index = 0; $index < count($columns); $index++){
					$column_keys = array_keys( $columns );
					list($type,$name,$options,$precedence) = FrmPlusFieldsHelper::parse_with_precedence($row_opt,$columns[ $column_keys[$index] ]);
					
					if (!isset($row_data[$index])){
						$tmp[$tmp_index] = '';
					}
					elseif ( has_action( "frmplus_field_value_$type" ) ){
						ob_start();
						do_action( "frmplus_field_value_$type", array( 
							'field' => $args['field'], 
							'value' => $row_data[$index],
							'options' => $options,
						));
						$tmp[$tmp_index] = ob_get_clean();
					}
					elseif(is_array($row_data[$index])){
						$tmp[$tmp_index] = implode(', ',$row_data[$index]);
					}
					else{
						$tmp[$tmp_index] = $row_data[$index];
					}
					$tmp_index++;
				}
				$data[] = $tmp;
			}
			
			// Now that we've got the header and the data, let's create something that we can use.
			static $how_options;
			if (!isset($how_options)){
				$how_options = array(); // We're going to allow different hows for different fields (keyed by the field ID)
			}
			if (!isset($how_options[$args['field']->id])){
				$how_options[$args['field']->id] = apply_filters('frmplus_csv_export_how','fixed', $args['field']); // Other options are 'csv' to get a CSV type string and 'tabbed' to get a string with the values separated by tabs.
				// Please note, if you are using 'csv', then data that gets exported will need to be massaged a little to be usable.
				// You'll essentially want to create a separate CSV file for each and every exported table field.  To do that, you'll
				// need to open your exported CSV file in Excel (or similar), copy the contents of the table field, paste it into a 
				// text editor and then replace all instances of ~:~ ($line_break_spoof above) with \n (a linebreak).  
			}
			$how = $how_options[$args['field']->id];
			
			// Starting fresh to create the $field_value
			$field_value = '';

			// a handy helper function to replace " with ""
			$escape_quotes = create_function('$a','return str_replace(\'"\',\'""\',$a);');
			
			switch($how){
			case 'csv':
				$header = array_map($escape_quotes,$header); // escape existing quotes
				$field_value = '"'.implode('","',$header).'"'; // create a CSV row for the header
				
				// create a CSV row for each row of data 
				foreach ($data as $row){
					$row = array_map($escape_quotes,$row);
					$field_value.= $line_break_spoof.'"'.implode('","',$row).'"';
				}
				break;
			case 'tabbed':
				// Create a tab separated row for the header
				$field_value = implode("\t",$header);
				
				// create a tab separated row for each row of data 
				foreach ($data as $row){
					$field_value.= $line_break_spoof.implode("\t",$row);
				}

				break;
			case 'fixed':
				// The header row
				static $fixed_width_options;
				if (!isset($fixed_width_options)){
					$fixed_width_options = array(); // we'll allow different fixed widths per table (though not yet per column)
				}
				if (!isset($fixed_width_options[$args['field']->id])){
					$fixed_width_options[$args['field']->id] = apply_filters('frmplus_csv_export_fixed_width',15, $args['field']); 
				}
				$fixed_width = $fixed_width_options[$args['field']->id];
				
				$line = str_repeat('-',count($header)*($fixed_width+3));
				$field_value.= self::create_wrapped_line($header,$fixed_width,$line_break_spoof);
				$field_value.= $line_break_spoof.str_replace('-','=',$line);
				
				// The rest of it
				foreach ($data as $row){
					$field_value.= $line_break_spoof;
					$field_value.= self::create_wrapped_line($row,$fixed_width,$line_break_spoof);
					$field_value.= $line_break_spoof.$line;
				}				
				break;
			}

			// For CSV files, putting the linebreaks back in causes problems.
			// If you copy and paste a cell value, then it gets wrapped in double quotes
			// and all existing double quotes are escaped as "".  This does not make it easy 
			// to get back into a table format.  Easier to just have the user replace ~:~ with \n
			// Other formats it's fine to put the line breaks back in.
			if ($how != 'csv'){
				if (ob_get_level()){
					ob_flush();
				}
				else{
					ob_start($reinstate_line_breaks);
				}
			}
			
		}
		
		return $field_value;
	}
	
	function create_wrapped_line($array,$fixed_width,$line_break_spoof){
		$more = true;
		$count = 0;
		$wrapped = '';
		while($more){
			$more = false;
			foreach ($array as $value){
				$chunk = substr($value,$count*$fixed_width,$fixed_width)."";
				$wrapped.= ' '.str_pad($chunk,$fixed_width);
				
				if (substr($value,($count+1)*$fixed_width,$fixed_width)){
					$wrapped.= '-';
					$more = true;
				}
				else{
					$wrapped.= ' ';
				}
				$wrapped.= '|';
			}
			if ($more){
				$wrapped.= $line_break_spoof;
				$count++;
			}
		}
		return $wrapped;
	}
	
	function setup_spoofs($get = false){
		if (!isset($_POST['form_id']) or !isset($_POST['item_meta']) or !is_numeric($_POST['form_id'])){
			return;
		}
		
		// We're going to get all table fields in the currently posted form. 
		// We can then spoof a row into those table fields so that the piece in 
		// FrmEntry::validate that clobbers single row arrays doesn't trigger
		global $frm_field;
		$table_fields = $frm_field->getAll('fi.form_id='. (int)$_POST['form_id'].' AND fi.type = "table"');
		
		static $spoofed_field_ids;
		if (!isset($spoofed_field_ids)){
			$spoofed_field_ids = array();
		}
		if ($get){
			// Using this function as a getter
			return $spoofed_field_ids;
		}
		
		if (is_array($table_fields)){
			foreach($table_fields as $table_field){
				$posted = & $_POST['item_meta'][$table_field->id];
				if (isset($posted) and is_array($posted)){
					$posted['spoof'] = "I'm fixing a hole where the rain gets in.";
					$spoofed_field_ids[] = $table_field->id;
				}
			}
		}
	}
	
	function get_rid_of_spoofs($errors,$values){
		$spoofed_field_ids = $this->setup_spoofs(true); // using this function as a getter
		
		foreach ($spoofed_field_ids as $field_id){
			if(is_array($_POST['item_meta'][$field_id]) and isset($_POST['item_meta'][$field_id]['spoof'])) {
				unset($_POST['item_meta'][$field_id]['spoof']);
			}
		}
		
		return $errors;
	}
	
	function setup_multi_page_shortcodes(){
		// If working on a multi page form with an HTML field at the end that contains a shortcode for a table
		// field (i.e. [2093]), the value that gets shown gets screwed up because the item_meta[2093] that comes
		// in the $_POST contains serialized strings.  The method FrmProFieldsHelper::get_default_value() calls
		// $new_value = FrmAppHelper::get_param('item_meta['. $shortcode .']', false, 'post');
		// which if $_POST is set will return the item from the $_POST (in this case a serialized string).
		// There are no filters within this stretch of code to allow me to fix it.  I think I have to manipulate
		// the $_POST object itself, which I don't like doing at all.  I'm not sure if it has the potential 
		// to screw anything else up down the way.  So, I'm going to add two filters here.  The low order one
		// saves the original $_POST and then massages the global $_POST.  The high order one restores
		// the original $_POST
		if(isset($_POST) and isset($_POST['item_meta'])){
			add_filter('frm_replace_shortcodes',array(&$this,'massage_request'),1,3);
			add_filter('frm_replace_shortcodes',array(&$this,'unmassage_request'),100,3);
		}
	}
	
	function massage_request($html, $field, $meta, $unmassage = false){
		static $saved_item_metas, $table_field_ids;
		if (!isset($saved_item_metas)){
			$saved_item_metas = array();
			
		}
		if ($unmassage){
			foreach ($saved_item_metas as $key => $value){
				$_POST['item_meta'][$key] = $value;
			}
			unset($saved_item_metas); // Just in case it gets run again.
			return;
		}
				
        preg_match_all( "/\[(\d*)\b(.*?)(?:(\/))?\]/s", $html, $matches, PREG_PATTERN_ORDER); // copied from FrmProFieldsHelper::get_default_value() method
        if (isset($matches[0]) and !empty($matches[0])){
			if (!isset($table_field_ids)){
				global $frm_field;
				$result = $frm_field->getIds('fi.type = "table"');
				$table_field_ids = array();
				foreach ($result as $table_field){
					$table_field_ids[] = $table_field->id;
				}
			}
	
            foreach ($matches[0] as $match_key => $val){
                $shortcode = $matches[1][$match_key];
                if(is_numeric($shortcode) and isset($_POST['item_meta'][$shortcode]) and in_array($shortcode,$table_field_ids) and !in_array($shortcode,$saved_item_metas)){ 
					$saved_item_metas[$shortcode] = $_POST['item_meta'][$shortcode];
					// A [field_id] shortcode fully expects the value in $_POST (if set)
					// to be a displayable chunk of html. We need to massage the _POST
					// variable to display properly any table fields.  
					
					// The email_value method is conveniently exactly what I need
					// in includes a stripslashes on the sanitize mehtod call, and works if I just
					// pass it in a field_id
					$_POST['item_meta'][$shortcode] = self::email_value($_POST['item_meta'][$shortcode],(object)array('field_id' => $shortcode, 'field_type' => 'table'),array());
				}
			}
		}
		
		return $html;
	}
	
	function unmassage_request($html, $field, $meta){
		$this->massage_request($html, $field, $meta, true); // This will unmassage the $_POST variable
		return $html;
	}
	
	public static function catch_all( $value ){
		// Formidable is super killing me with almost every update they do.  I've had to create this catch_all function
		// to try and catch and fix various formatting oddities.  Grrrr.
		if ( is_array( $value ) ){
			$value = array_map( 'maybe_unserialize', $value );
			foreach ( $value as $index => $row ){
				if ( is_array( $row ) ){
					$value[$index] = array_map( 'maybe_unserialize', $row );
				}
			}
		}
		return $value;
	}
}
    
?>