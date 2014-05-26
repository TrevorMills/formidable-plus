<?php
	if ($field['type'] == 'table'){
		$entry_id = FrmPlusEntriesHelper::getCurrentEntryId(); 
		if ( $entry_id ){ 
			// It used to be that I only grabbed this value when it wasn't already set, but
			// it turns out that there were cases when it wasn't set properly.  Turns out to be 
			// safe to just fetch the value everytime.
			$field['value'] = FrmPlusEntryMetaHelper::fetch($field['id'],$entry_id);
		}
		$field['value'] = FrmPlusEntryMetaHelper::catch_all( $field['value'] );
		require('table.php');
	}
?>