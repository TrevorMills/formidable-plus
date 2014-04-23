<?php if ( !isset( $display_only ) ) $display_only = false; ?>
<?php if (!isset($row_num)) $row_num = 0; ?>
<?php if (count($rows)) :  ?>
		
	<?php foreach ($rows as $opt_key => $opt) : ?>
		<tr class="row-<?php echo $row_num; ?>">
			<?php if ( !isset( $field['hide_row_headers'] ) || !$field['hide_row_headers'] ) : ?>
				<th><?php echo FrmPlusFieldsHelper::parse_option($opt,'name'); ?></th>
			<?php endif; ?>
		<?php if (!count($columns)) $columns[] = ""; // Spoof to get a column up there to enter data into ?>
		<?php $col_num = 0; foreach ($columns as $col_key => $col_opt) : ?>
			<td class="column-<?php echo $col_num; ?>"><?php require('table-field.php'); $col_num++; ?></td>
		<?php endforeach; ?>
		</tr>
	<?php $row_num++; endforeach; ?>
<?php else : 
		if (count($columns)){
			if ( !isset( $dynamic_options ) ){
				$dynamic_options = FrmPlusFieldsHelper::get_dynamic_options( $field );
			}
			if (isset($field['value']) and is_array($field['value'])){
				$rows_to_output = count($field['value']);
			}
			elseif ( defined( 'DOING_AJAX' ) && $_POST['action'] == 'frm_add_table_row' ){
				$rows_to_output = 1;
			}
			else{
				$rows_to_output = $dynamic_options->starting_rows;
			}
			for($r = 0; $r < $rows_to_output; $r++){ $col_num = 0; ?>
				<tr class="row-<?php echo $row_num; ?>"><?php
				foreach ($columns as $col_key => $col_opt){
					?><td class="column-<?php echo $col_num; ?>"><?php require('table-field.php'); $col_num++; ?></td><?php
				}
			if ($display_only !== true) : ?>
				<td>
					<a class="frmplus-delete-row" href="javascript:delete_row(<?php echo $field['id']; ?>,<?php echo $row_num; ?>)"><img src="<?php echo FRMPLUS_IMAGES_URL ?>/trash.png" alt="<?php echo apply_filters('frmplus_text_delete_row',$dynamic_options->delete_row_text,$field); ?>" title="<?php echo apply_filters('frmplus_text_delete_row',$dynamic_options->delete_row_text,$field); ?>" border="0"></a>
					<?php if ( $dynamic_options->rows_sortable ) : ?>
						<span class="frmplus-sort-row" ><img src="<?php echo FRMPLUS_IMAGES_URL ?>/move.png" alt="<?php echo apply_filters('frmplus_text_sort_row',$dynamic_options->sort_row_text,$field); ?>" title="<?php echo apply_filters('frmplus_text_sort_row',$dynamic_options->sort_row_text,$field); ?>" border="0"></span>
					<?php endif; ?>
				</td>
			<?php endif;
				?></tr>
			<?php $row_num++; }
		}
?>
<?php endif; ?>
