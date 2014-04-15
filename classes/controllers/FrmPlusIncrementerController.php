<?php
class FrmPlusIncrementerController{
	var $enqueued = false;
	var $particulars = array();
	var $styles = array(
		'1' => '1, 2, 3 ...',
		'A' => 'A, B, C ...',
		'a' => 'a, b, c ...',
		'I' => 'I, II, III, IV ...',
		'i' => 'i, ii, iii, iv ...',
	);
	
	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'incrementer',
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( !isset( $options['start'] ) ){
			$options['start'] = 1;
		}
		if ( !isset( $options['style'] ) ){
			$options['style'] = '1';
		}
		if ( !isset( $options['suffix'] ) ){
			$options['suffix'] = '';
		}
		if ( isset( $options['options'] ) ){
			unset( $options['options'] );
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $opt_key ){
		$options = $this->massageOptions( $options );
		$id = "incrememter-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
?>
<div id="<?php echo $id; ?>">
	<div class="section">
		<label><?php _e( 'Style', FRMPLUS_PLUGIN_NAME ); ?>: </label>
		<select name="frmplus_options[style]">
			<?php foreach ( $this->styles as $style => $label ) : ?>
				<option value="<?php echo $style; ?>" <?php selected( $style, $options['style'] ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="section">
		<label><?php _e( 'Start from', FRMPLUS_PLUGIN_NAME ); ?>: </label>
		<input type="text" name="frmplus_options[start]" value="<?php echo $options['start']; ?>" >
	</div>
	<div class="section">
		<label><?php _e( 'Suffix', FRMPLUS_PLUGIN_NAME ); ?>: </label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'This string will be appended to the incrementer.  Examples might be . (period), ) (parenthesis), or : (colon)', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
		<input type="text" name="frmplus_options[suffix]" value="<?php echo $options['suffix']; ?>" >
	</div>
</div>
	<?php
	}

	public function render_callback( $args ){
				
		extract( $args );
		$options = $this->massageOptions( $options );

		list( $columns, $rows ) = FrmPlusFieldsHelper::get_table_options( maybe_unserialize($field['options']) );
		
		if ( !count( $rows ) ){
			if ( !$this->enqueued ){
				$this->enqueued = true;
			    wp_enqueue_script( 'frm-plus-incrementer', plugins_url( 'formidable-plus/js/frm-plus-incrementer.js' ), array( 'jquery' ) );
				add_action( ( is_admin() && !defined( 'DOING_AJAX' ) ) ? 'admin_footer' : 'wp_footer', array( &$this, 'localize_script' ) );
			}
		}
		$this->particulars[] = array_merge(
			array(
				'id' => (int)$field['id'],
				'selector' => ( $precedence == 'row' ? "row-$row_num" : "column-$col_num" )
			),
			$options
		);

		echo "<input type=\"text\" readonly name=\"{$this_field_name}[$col_num]\" id=\"$this_field_id\" value=\"\" class=\"auto_width table-cell readonly incrementer\"/>";
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
	
