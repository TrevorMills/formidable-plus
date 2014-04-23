<?php

class FrmPlusAppController{
    function FrmPlusAppController(){
        add_action('frm_standalone_route', array(&$this, 'standalone_route'),14,1);
        add_action('init', array(&$this, 'front_head'),1); // needs to come before the FRMPlus one
    }
    
    function front_head(){
        $css = apply_filters('get_frmplus_stylesheet', FRMPLUS_URL .'/css/frm-plus.css');
        wp_enqueue_style('frmplus-forms', $css);
		$script = apply_filters('get_frmplus_script', FRMPLUS_URL .'/js/frm_plus.js');
        wp_enqueue_script('frmplus-scripts', $script, array('jquery'));
		wp_localize_script( 'frmplus-scripts', 'FRM_PLUS_FRONT', array(
			'are_you_sure' => __( 'Are you sure you wish to permanently delete this row?  This cannot be undone.', FRMPLUS_PLUGIN_NAME ),
			'leave_one_row' => __( 'Sorry, you must leave at least one row in this table.', FRMPLUS_PLUGIN_NAME )
		));
		if (!is_admin() or ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
			add_action('wp_print_scripts',array($this,'declare_ajaxurl'));
		}
		if ( is_admin() ){
	        wp_enqueue_style('frmplus-admin', FRMPLUS_URL . '/css/frm-plus-admin.css' );
	        wp_enqueue_script('frmplus-admin', FRMPLUS_URL . '/js/frm-plus-admin.js' );
			wp_localize_script( 'frmplus-admin', 'FRMPLUS', array(
				'types_with_options' => FrmPlusFieldsHelper::get_types( 'with_options' )
			));
		}
    }  

    function standalone_route($controller, $action=''){
        global $frm_forms_controller;
        if($controller=='settings'){
            global $frmpro_settings;
            require(FRMPLUS_PATH .'/css/frm-plus.css');
		}
    }

	static function & get_frmdb(){
		// The global $frmdb was introduced in Formidable > 1.02.  To get Formidable Plus working with earlier versions, I'll spoof it here for what I need
		global $frmdb;
		if (!isset($frmdb)){
			$frmdb = new stdClass;
			global $frm_entry_meta;
			$frmdb->entry_metas = $frm_entry_meta->table_name;
		}
		return $frmdb;
	}
	
	function declare_ajaxurl(){
		echo '
		<script type="text/javascript">
		//<![CDATA[
		var ajaxurl = "'.admin_url('admin-ajax.php').'";
		//]]>
		</script>
		';
	}

}

?>