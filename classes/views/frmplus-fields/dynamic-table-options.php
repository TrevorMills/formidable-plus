<div class="dynamic-table-options" <?php if ( !empty( $rows ) ) : ?>style="display:none"<?php endif; ?>>
	<p class="description">
		<?php _e( 'Because you have no rows defined, this is a dynamic table; the user can add, delete and sort rows themselves.  You can set a few options below:', FRMPLUS_PLUGIN_NAME ); ?>
	</p>
	<div class="dynamic-options">
		<?php $dynamic_options = FrmPlusFieldsHelper::get_dynamic_options( $field ); $_d = & $dynamic_options; // shorthand?>
		<label for="frm_starting_rows_<?php echo $field['id']; ?>"><?php _e( 'Initial number of rows', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_starting_rows_<?php echo $field['id']; ?>" type="number" name="field_options[starting_rows_<?php echo $field['id']; ?>]" class="auto_width" value="<?php echo $_d->starting_rows; ?>" min="0" max="25" /><br/>
		<label><?php _e( 'Rows are Sortable', FRMPLUS_PLUGIN_NAME ); ?>:</label>
			<label class="auto_width"><input type="radio" value="yes" name="field_options[rows_sortable_<?php echo $field['id']; ?>]" <?php checked( true, $_d->rows_sortable ); ?> /><?php _e( 'Yes', FRMPLUS_PLUGIN_NAME ); ?></label>&nbsp;&nbsp;&nbsp;
			<label class="auto_width"><input type="radio" value="no" name="field_options[rows_sortable_<?php echo $field['id']; ?>]" <?php checked( false, $_d->rows_sortable ); ?> /><?php _e( 'No', FRMPLUS_PLUGIN_NAME ); ?></label><br/>
		<label for="frm_add_row_text_<?php echo $field['id']; ?>"><?php _e( 'Add Row Label', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_add_row_text_<?php echo $field['id']; ?>" type="text" name="field_options[add_row_text_<?php echo $field['id']; ?>]" class="auto_width" value="<?php echo $_d->add_row_text; ?>" /><br/>
		<label for="frm_delete_row_text_<?php echo $field['id']; ?>"><?php _e( 'Delete Row Label', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_delete_row_text_<?php echo $field['id']; ?>" type="text" name="field_options[delete_row_text_<?php echo $field['id']; ?>]" class="auto_width" value="<?php echo $_d->delete_row_text; ?>" /><br/>
	</div>
</div>