<?php
class FrmPlusIncrementerController{
	var $enqueued = false;
	var $particulars = array();
	
	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'incrementer',
			'has_options' => false,
			//'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}

	public function render_callback( $args ){
				
		extract( $args );

		list( $columns, $rows ) = FrmPlusFieldsHelper::get_table_options( maybe_unserialize($field['options']) );
		
		if ( !count( $rows ) ){
			if ( !$this->enqueued ){
				$this->enqueued = true;
			    wp_enqueue_script( 'frm-plus-incrementer', plugins_url( 'formidable-plus/js/frm-plus-incrementer.js' ), array( 'jquery' ) );
				add_action( ( is_admin() && !defined( 'DOING_AJAX' ) ) ? 'admin_footer' : 'wp_footer', array( &$this, 'localize_script' ) );
			}
			$this->particulars[] = array(
				'id' => (int)$field['id']
			);
		}

		$number = ( $precedence == 'row' ? $col_num : $row_num ) + 1;
		
		echo "<input type=\"text\" readonly name=\"$this_field_name\" id=\"$this_field_id\" value=\"$number\" class=\"auto_width table-cell readonly incrementer\"/>";
	}
	
	public function localize_script(){
		wp_localize_script( 'frm-plus-incrementer', 'FRM_PLUS_INCREMENTER', 
			apply_filters( 'frm-plus-incrementer-localization', array( 
				'particulars' => $this->particulars
			))
		);
	}
	
	
}

new FrmPlusIncrementerController();
	
