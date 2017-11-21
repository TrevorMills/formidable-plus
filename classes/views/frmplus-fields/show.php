<?php if ($field['type'] == 'table'){ ?>
    <div class="frm-show-click">
    <?php list($columns,$rows) = FrmPlusFieldsHelper::get_table_options($field['options']); ?>
		Column Headings<br/>
	<div id="frm_column_list_<?php echo $field['id']; ?>">
		<div id="frm_field_col_<?php echo $field['id']; ?>_opts">
<?php
		foreach ($columns as $opt_key => $opt){
			require('table-option.php');
		}
?>	
		</div>
		<div id="frm_add_field_col_<?php echo $field['id']; ?>" class="frm-show-click-col"> <?php // had to change this class to get the thing to appear ?>
		    <a href="javascript:frm_add_field_option('<?php echo 'col_'.$field['id']; ?>')"><span class="ui-icon ui-icon-plusthick alignleft"></span> <?php _e( 'Add a Column', FRMPLUS_PLUGIN_NAME ); ?></a>
		</div>
	</div> <!-- frm_column_list_<?php echo $field['id']; ?> -->
		<br/>Row Headings<br/>
	<div id="frm_row_list_<?php echo $field['id']; ?>">

		<div id="frm_field_row_<?php echo $field['id']; ?>_opts">
		<?php require( 'dynamic-table-options.php' ); ?>
<?php
		foreach ($rows as $opt_key => $opt){
			require('table-option.php');
		}
?>		
		</div>
	    <div id="frm_add_field_row_<?php echo $field['id']; ?>" class="frm-show-click-row">
	        <a class="frm_add_field_row" href="javascript:frm_add_field_option('<?php echo 'row_'.$field['id']; ?>')"><span class="ui-icon ui-icon-plusthick alignleft"></span> <?php _e( 'Add a Row', FRMPLUS_PLUGIN_NAME ); ?></a>
	        <?php do_action('frm_add_multiple_opts', $field); ?>
	    </div>
	</div> <!-- frm_row_list_<?php echo $field['id']; ?> -->
	</div> <!-- frm-show-click -->

	<!-- putting this field to work around a potential Javascript error from Formidable Pro -->
	<input type="checkbox" id="separate_value_<?php echo $field['id']; ?>" style="display:none" />


	<script type="text/javascript">
	jQuery(function($){
		$("#frm_column_list_<?php echo $field['id']; ?>").sortable({
			axis:'y',
		    cursor:'move',
			handle:'.frm_sortable_handle',
		    revert:true,
			items:'div.frm_single_option_sortable',
		    update:function(){
		        var order= $('#frm_column_list_<?php echo $field['id']; ?>').sortable('serialize');
		        $.ajax({
		            type:"POST",
		            url:ajaxurl,
		            data:"action=frm_table_option_order&which=col&field_id=<?php echo $field['id']; ?>&"+order,
					success:function(msg){$('#frm_column_list_<?php echo $field['id']; ?>').sortable('refresh');}
		        });
		    }
		});

		$("#frm_row_list_<?php echo $field['id']; ?>").sortable({
			axis:'y',
		    cursor:'move',
			handle:'.frm_sortable_handle',
		    revert:true,
			items:'div.frm_single_option_sortable',
		    update:function(){
		        var order= $('#frm_row_list_<?php echo $field['id']; ?>').sortable('serialize');
		        $.ajax({
		            type:"POST",
		            url:ajaxurl,
		            data:"action=frm_table_option_order&which=row&field_id=<?php echo $field['id']; ?>&"+order,
					success:function(msg){$('#frm_row_list_<?php echo $field['id']; ?>').sortable('refresh');}
		        });
		    }
		});
	});

	</script>
<?php } ?>
