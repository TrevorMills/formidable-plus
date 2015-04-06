<?php

class FrmPlusFieldsController{
    function FrmPlusFieldsController(){
        add_action('frm_display_added_fields', array(&$this,'show'));
        add_action('frm_form_fields', array(&$this, 'form_fields'), 10,2);
        add_action('wp_ajax_frm_add_field_option',array(&$this, 'add_option'),4); // run before the add_option in the FrmProFieldsController class
        add_action('wp_ajax_frm_delete_field_option',array(&$this, 'delete_option'),4); // run before the delete_option in the FrmProFieldsController class
        add_action('wp_ajax_frm_field_option_ipe', array(&$this, 'edit_option'), 4); // run before the edit_option in the FrmProFieldsController class
		add_action('wp_ajax_frm_plus_edit_option', array(&$this, 'edit_option'));
		add_action('wp_ajax_frm_plus_get_options_form', array( &$this, 'get_options_form'));
        add_action('wp_ajax_frm_add_table_row',array(&$this, 'add_table_row')); 
        add_action('wp_ajax_nopriv_frm_add_table_row',array(&$this, 'add_table_row')); 
		add_action('wp_ajax_frm_table_option_order',array(&$this,'reorder_table_options'));
		add_filter('frm_before_field_created',array(&$this,'setup_new_vars'));
    }
    
	/**
	 * The Show method for showing a table field within the Admin form builder
	 */
    function show($field){
        $field_name = "item_meta[". $field['id'] ."]";
        require(FRMPLUS_VIEWS_PATH.'/frmplus-fields/show.php');    
    }

	/**
	 * Displays a table field
	 */
    function form_fields($field, $field_name){
        require(FRMPLUS_VIEWS_PATH.'/frmplus-fields/form-fields.php');
    }
    
	/** 
	 * Called when a row or column is added to the table field within the admin area
	 */
	function add_option(){
        global $frm_field;

        $id = $_POST['field_id'];
		// If the field_id begins with 'row' or 'col'
		if (in_array($t = substr($id,0,3),array('col','row'))){
			$id = substr($id,4);
	        $field = $frm_field->getOne($id);
			if (!$field){
				return;
			}
	        $options = maybe_unserialize($field->options);
		    list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($options);
			if ($t == 'col'){
				if (!count($columns)){
					$last = 'col_0';
				}
				else{
					$tmp = array_keys($columns);
					natsort($tmp);
					$last = array_pop($tmp);
				}
				preg_match('/[0-9]+$/',$last,$matches);
		        $opt_key = 'col_' . ($matches[0] + 1);
		        $opt = 'Column '.(count($columns)+1);
		        $columns[$opt_key] = $opt;
			}
			else{
				if (!count($rows)){
					$last = 'row_0';
				}
				else{
					$tmp = array_keys($rows);
					natsort($tmp);
					$last = array_pop($tmp);
				}
				preg_match('/[0-9]+$/',$last,$matches);
		        $opt_key = 'row_' . ($matches[0] + 1);
		        $opt = 'Row '.(count($rows)+1);
		        $rows[$opt_key] = $opt;
			}
			$options = FrmPlusFieldsHelper::set_table_options($options,$columns,$rows);
	        $frm_field->update($id, array('options' => serialize($options)));
	
			// Now that we've added it, we need to update all item metas to have matching arrays
			// This is so that when we delete a row/column and then remove data from the item metas
			// the integrity of the data is maintained
			global $wpdb;
			$frmdb = & FrmPlusAppController::get_frmdb();
			$metas = $wpdb->get_results("SELECT id, meta_value FROM {$frmdb->entry_metas} WHERE field_id={$id}");
			if (is_array($metas)){
				foreach ($metas as $meta){
					$data = FrmPlusEntryMetaHelper::sanitize(maybe_unserialize($meta->meta_value),true); // true = stripslashes
					if ($t == 'row'){
						$data[] = array_fill(0,count($columns),'');
					}
					else{
						foreach ($data as $r => $row){
							$data[$r][] = '';
						}
					}
			      	$query_results = $wpdb->update( $frmdb->entry_metas, array('meta_value' => serialize($data)), array( 'id' => $meta->id ) );
				}
			}

	        $field_data = $frm_field->getOne($id);
	        $field = array();
	        $field['type'] = $field_data->type;
	        $field['id'] = $id;
	        $field_name = "item_meta[$id]";

			global $frm_ajax_url;
	        require(FRMPLUS_VIEWS_PATH.'/frmplus-fields/table-option.php'); 
			if ( defined( 'FRM_VIEWS_PATH' ) ){
				// Older versions of Formidable
		        require(FRM_VIEWS_PATH.'/frm-forms/new-option-js.php'); // this does the editInPlace piece
			}
			die(); // it's an AJAX call.  Go die!
		}
		else{
			FrmFieldsController::add_option();
		}
	}
	
	/** 
	 * Called when a row or column is deleted from the table field within the admin area
	 */
	function delete_option(){
        global $frm_field;
		
		// if opt_key begins with 'col' or 'row'
		if (in_array($t = substr($_POST['opt_key'],0,3),array('col','row'))){
			$id = $_POST['field_id'];
	        $field = $frm_field->getOne($id);
			if (!$field){
				return;
			}
	        $options = maybe_unserialize($field->options);
		    list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($options);
			if ($t == 'col'){
				$target = array_search($_POST['opt_key'],array_keys($columns));
			}
			else{
				$target = array_search($_POST['opt_key'],array_keys($rows));
			}
			if ($target === false){
				return;
			}

			// We need to go through all of the entry_metas and remove the data corresponding to this field
			global $wpdb;
			$frmdb = & FrmPlusAppController::get_frmdb();
			$metas = $wpdb->get_results("SELECT id, meta_value FROM {$frmdb->entry_metas} WHERE field_id={$id}");
			if (is_array($metas)){
				foreach ($metas as $meta){
					$data = FrmPlusEntryMetaHelper::sanitize(maybe_unserialize($meta->meta_value),true); // true = stripslashes
					if ($t == 'row'){
						unset($data[$target]);
						$new_data = array();
						foreach ($data as $row_index => $d){
							$new_data[$row_index-1] = $d; // the -1 is to account for the row we've just deleted
						}
						$data = $new_data;
					}
					else{
						foreach ($data as $r => $row){
							unset($row[$target]);
							$new_data = array();
							foreach ($row as $c){
								$new_data[] = $c;
							}
							$data[$r] = $new_data;
						}
					}
			      	$query_results = $wpdb->update( $frmdb->entry_metas, array('meta_value' => serialize($data)), array( 'id' => $meta->id ) );
				}
			}

	        unset($options[$_POST['opt_key']]);
	        $frm_field->update($_POST['field_id'], array('options' => maybe_serialize($options)));
			echo json_encode( array( 'other' => true ) );
			die();
		}		
	}
	
	/** 
	 * Called when the administrator changes the name of a field
	 */
	function edit_option(){
        $ids = explode('-', $_POST['element_id']);
        $id = str_replace('field_', '', $ids[0]);
        if(strpos($_POST['element_id'], 'key_')){
            $id = str_replace('key_', '', $id);
        }
        
        $frm_field = new FrmField();
        $field = $frm_field->getOne($id);
		if ( $field->type == 'table' ){
			$new_name = $_POST['update_value'];
			// Let's do our work here.  
	        $these_options = maybe_unserialize($field->options);
			$this_opt = $these_options[ $ids[1] ];
			
			list( $type, $name, $options ) = FrmPlusFieldsHelper::parse_option( $this_opt );
			$_POST['update_value'] = stripslashes( $_POST['update_value'] );
			switch( $_POST['update_what'] ){
			case 'type':
				$type = ( empty($_POST['update_value']) ? 'text' : $_POST['update_value'] );
				break;
			case 'options':
				$options = wp_parse_args( $_POST['update_value'] );
				if ( is_array( $options['frmplus_options'] ) ){
					$options = $options['frmplus_options'];
				}
				
				if ( isset( $options['options'] ) && !is_array( $options['options'] ) ){
					$options['options'] = explode( "\n", str_replace( "\r", '', $options['options']) );
				}
				break;
			case 'name':
			default:
				$name = $_POST['update_value'];
		        echo (trim($name) == '') ? __('(Blank)', 'formidable') : $name;
				break;
			}
			
			//$new_opt = ( 'text' === $type ? '' : "$type:" ) . $name . ( empty( $options ) ? '' : ':' . json_encode( FrmPlusFieldsHelper::convert_deep($options)) );
			$new_opt = array( 
				'type' => $type,
				'name' => $name,
				'options' => $options
			);
			$these_options[ $ids[1] ] = $new_opt;
	        $frm_field->update($id, array('options' => maybe_serialize( FrmPlusFieldsHelper::convert_deep($these_options) ) ) );
			die();
		}
	}
	
	/** 
	 * Called to get the options form for a particular field
	 */
	function get_options_form(){
        $ids = explode('-', $_POST['element_id']);
        $id = str_replace('field_', '', $ids[0]);
        
        $frm_field = new FrmField();
        $field = $frm_field->getOne($id);

		if ( $field && $field->type == 'table'){
	        $these_options = maybe_unserialize($field->options);
			$opt = $these_options[ $ids[1] ];
			echo FrmPlusFieldsHelper::get_options_form( $opt, $field, $ids[1] );
			die();
		}
	}
	
	/** 
	 * Called when an user adds a row to a table in their form
	 * - returns the markup for the table row.
	 */
	function add_table_row(){
        global $frm_field;
		extract($_POST);

        $field_data = $frm_field->getOne($field_id );
        $options = maybe_unserialize($field_data->options);
	    list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($options);
	
		/*
        $field = array();
        $field['field_key'] = $field_data->field_key;
        $field['type'] = $field_data->type;
        $field['id'] = $field_id;
		$field['options'] = $field_data->options;
		*/
		$field = (array)$field_data;
        $field_name = "item_meta[$field_id]";
		ob_start();
		require(FRMPLUS_VIEWS_PATH.'/frmplus-fields/table-row.php');
		// For some reason, this was adding slashes. Don't know why, but I don't want them.  
		echo stripslashes(ob_get_clean());
		die();
	}
	
	/** 
	 * Called when an admin reorders the rows or columns of a table
	 */
	function reorder_table_options(){
		// When reordering fields, it is vital that we reorder any data that might exist for 
        global $frm_field;
		extract($_POST);

        $field = $frm_field->getOne($field_id);
		if (!$field or $field->type != 'table'){
			die();
		}
        $options = maybe_unserialize($field->options);
	    list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($options);
		$array_name = "frm_field-$field_id-$which";
		if ($which == "col"){
			$saved = $columns;
		}
		else{
			$saved = $rows;
		}
		$new = array();
		$map = array();
		$keys = array_keys($saved);
		foreach($_POST[$array_name] as $key){			
			// Formidable only hides deleted options, it doesn't removed them from the DOM,
			// So, it's possible to be sent reorder data for options that don't exist anymore
			// That's why we do the key_exists check.
			if (array_key_exists("{$which}_{$key}",$saved)){ 
				$new["{$which}_{$key}"] = $saved["{$which}_{$key}"];
				$map[] = array_search("{$which}_{$key}",$keys);
			}
		}
		if ($which == "col"){
			$options = FrmPlusFieldsHelper::set_table_options($options,$new,$rows);
		}
		else{
			$options = FrmPlusFieldsHelper::set_table_options($options,$columns,$new);
		}
        $frm_field->update($field_id, array('options' => serialize($options)));
		
		// Now that we've updated the field, let's update the item_metas
		global $wpdb;
		$frmdb = & FrmPlusAppController::get_frmdb();
		$metas = $wpdb->get_results("SELECT id, meta_value FROM {$frmdb->entry_metas} WHERE field_id={$field_id}");
		if (is_array($metas)){
			foreach ($metas as $meta){
				$data = FrmPlusEntryMetaHelper::sanitize(maybe_unserialize($meta->meta_value),true); // true = stripslashes
				$new_data = array();
				if ($which == 'row'){
					foreach ($map as $old_loc){
						if (array_key_exists($old_loc,$data)){
							$new_data[] = $data[$old_loc];
						}
						else{
							// Need to spoof it.
							$new_data[] = false; 
						}
					}
					$new_data = array_filter( $new_data ); // get rid of the ones we set to false above
				}
				else{
					foreach ($data as $row_index => $row){
						$new_row_data = array();
						foreach ($map as $old_loc){
							if (array_key_exists($old_loc,$row)){
								$new_row_data[] = $row[$old_loc];
							}
							else{
								$new_row_data[] = '';
							}
						}
						$new_data[ $row_index ] = $new_row_data;
					}
				}
		      	$query_results = $wpdb->update( $frmdb->entry_metas, array('meta_value' => serialize($new_data)), array( 'id' => $meta->id ) );
			}
		}
		die();
	}
	
	function setup_new_vars($field_values){
		return FrmPlusFieldsHelper::setup_new_vars($field_values);
	}
	
}

?>