<div class="frm_single_option_sortable" id="frm_field-<?php echo $field['id']; ?>-<?php echo $opt_key; ?>">
<span id="frm_delete_field_<?php echo $field['id']; ?>-<?php echo $opt_key; ?>_container" class="frm_single_option">
	<?php if ( defined( 'FRM_IMAGES_URL' ) ) : // older version of Formidable.  Do it the old way ?>
		<a href="javascript:frm_delete_field_option(<?php echo $field['id']?>, '<?php echo $opt_key ?>',ajaxurl);" class="frm_single_visible_hover alignleft" ><img src="<?php echo FRM_IMAGES_URL ?>/trash.png" alt="Delete"></a>
	    <a href="javascript:void(0);" class="frm_single_visible_hover alignleft frm_sortable_handle" ><img src="<?php echo FRM_IMAGES_URL ?>/move.png" alt="Reorder"></a>
    <?php elseif ( !FrmPlusAppHelper::is_version2() ) : // newer version, do it the newer way ?>
		<a href="javascript:void(0)" class="frm_single_visible_hover frm_icon_font frm_delete_icon frm_delete_field_<?php echo substr( $opt_key, 0, 3 ); ?>" style="visibility: hidden;"> </a>
	    <a href="javascript:void(0);" class="frm_single_visible_hover alignleft frm_sortable_handle frm_icon_font frm_move_field" > </a>
	<?php else : // Formidable Version 2 or greater, yet another adjustment ?>	
		<a href="javascript:void(0)" class="frm_single_visible_hover frm_icon_font frm_delete_icon frm_delete_field_<?php echo substr( $opt_key, 0, 3 ); ?>"> </a>
	    <a href="javascript:void(0);" class="frm_single_visible_hover alignleft frm_sortable_handle frm_icon_font frm_move_field" > </a>
	<?php endif; ?>
	<?php
		list ( $type, $name, $options ) = FrmPlusFieldsHelper::parse_option( $opt );
	?>
		<select class="frmplus_field_type" id="field_<?php echo $field['id']?>-<?php echo $opt_key ?>-type">
			<?php foreach ( FrmPlusFieldsHelper::get_types( 'valid' ) as $available_type ) : ?>
				<option value="<?php echo $available_type; ?>" <?php selected( $type, $available_type ); ?>><?php echo __( ucwords( str_replace( '_', ' ', $available_type ) ), FRMPLUS_PLUGIN_NAME ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="frmplus_field_options icon16" id="field_<?php echo $field['id']?>-<?php echo $opt_key ?>-options" <?php echo ( in_array( $type, FrmPlusFieldsHelper::get_types( 'with_options' ) ) ? '' : 'style="display:none"' ); ?>></span>
    <span class="frm_ipe_field_option" id="field_<?php echo $field['id']?>-<?php echo $opt_key ?>"><?php echo $name ?></span>
	<div class="frmplus_options_form">
		<div class="form-contents">
			<!-- this gets filled in via AJAX -->
		</div>
	</div>
</span>
<div class="clear"></div>
</div> <!-- frm_single_option_sortable -->