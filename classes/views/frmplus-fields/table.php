<?php
// Strange, but it seems like it's possible to get here without having $field['id'] or $field['field_key'] set
if ( !isset( $field['id'] ) && isset( $field['field_id'] ) ){
	$field['id'] = $field['field_id'];
}
if ( !isset( $field['field_key'] ) ){
	global $frmdb, $wpdb;
	$field['field_key'] = $wpdb->get_var( $wpdb->prepare("SELECT `field_key` FROM $frmdb->fields WHERE id = %d", $field['id'] ) );
}	
if ( !isset( $display_only ) ){
	$display_only = false;
}
?>
<?php 
	list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($field['options']);
	echo apply_filters('frm-table-container-extras','',$field['id']);
	$dynamic_options = FrmPlusFieldsHelper::get_dynamic_options( $field ); 
	if ( $dynamic_options->is_dynamic && $dynamic_options->rows_sortable && $display_only !== true ){
		wp_enqueue_script( 'jquery-ui-sortable' );
	}
?>
<div id="frm-table-container-<?php echo $field['id']; ?>" class="frm-table-container">
<table id="frm-table-<?php echo $field['id']; ?>" class="frm-table<?php if (count($classes = apply_filters('frm_table_classes',array(),$field['id']))) echo ' '.join(' ',$classes); ?> <?php if ( $dynamic_options->is_dynamic && $dynamic_options->rows_sortable && $display_only !== true ) : ?>ui-sortable<?php endif; ?>">
<?php if (count($columns)) : ?>
	<?php // First Row - Column Headers ?>
	<?php if ( !isset( $field['hide_column_headers'] ) || !$field['hide_column_headers'] ) : ?>
		<thead>
			<tr>
			<?php if (count($rows)) : ?>
				<?php // Blank column header to go above Row headers ?>
				<?php if ( !isset( $field['hide_row_headers'] ) || !$field['hide_row_headers'] ) : ?>
					<th>&nbsp;</th>
				<?php endif; ?>
			<?php endif; ?>
			<?php $col_num = 0; foreach ($columns as $opt_key => $opt) : ?>
				<th class="column-<?php echo $col_num++; ?>"><?php echo FrmPlusFieldsHelper::parse_option($opt,'name'); ?></th>
			<?php endforeach; ?>
			<?php if (!count($rows)) : ?>
				<?php if ($display_only !== true) : // Blank column header for action buttons (delete row, insert row) ?>
				<th>&nbsp;</th>
				<?php endif; ?>
			<?php endif; ?>
			</tr>
		</thead>
	<?php endif; ?>
<?php endif; ?>
	<tbody>
<?php require('table-row.php'); ?>
	</tbody>
</table>
<?php if (count($columns) and !count($rows) and $display_only !== true) : ?>
<a class="frmplus-add-row" id="frmplus-add-row-<?php echo $field['id']; ?>" href="javascript:add_row(<?php echo $field['id']; ?>)"><?php echo apply_filters('frmplus_text_add_row',$dynamic_options->add_row_text,$field); ?> <img style="vertical-align:middle" src="<?php echo FRMPLUS_IMAGES_URL ?>/duplicate.png" alt="New Row" title="New Row" border="0"></a> 	
<?php endif; ?>
</div>