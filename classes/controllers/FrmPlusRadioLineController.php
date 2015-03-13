<?php
class FrmPlusRadioLineController{
	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'radioline',
			'needs_massaging' => true,
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( !isset( $options['value'] ) ){
			$options['value'] = 'incrementer';
		}
		if ( !isset( $options['starting_value'] ) ){
			$options['starting_value'] = 1;
		}
		if ( !isset( $options['increment'] ) ){
			$options['increment'] = 1;
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $opt_key ){
		$options = $this->massageOptions( $options );
		$id = "radioline-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
?>
<div id="<?php echo $id; ?>">
	<div class="section">
		<label><?php _e( 'Value', FRMPLUS_PLUGIN_NAME ); ?>: </label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'Where to take the value for the radio button.  Header means the value will be the the header of the row/column the radio button appears in.  Incrementer allows you to set the values to series of numbers.', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
		<input type="radio" name="frmplus_options[value]" value="header" <?php checked( 'header', $options['value'] ); ?> /> <?php _e( 'Header', FRMPLUS_PLUGIN_NAME ); ?>
		<input type="radio" name="frmplus_options[value]" value="incrementer" <?php checked( 'incrementer', $options['value'] ); ?> /> <?php _e( 'Number Series', FRMPLUS_PLUGIN_NAME ); ?>
		
		<div class="show-for-incrementer" style="display:none">
			<label><?php _e( 'Starting Value:', FRMPLUS_PLUGIN_NAME ); ?></label><br/>
			<input type="number" name="frmplus_options[starting_value]" value="<?php echo esc_attr( $options['starting_value'] ); ?>"><br/>
			<label><?php _e( 'Increment:', FRMPLUS_PLUGIN_NAME ); ?></label><br/>
			<input type="number" name="frmplus_options[increment]" value="<?php echo esc_attr( $options['increment'] ); ?>">
		</div>	
	</div>
</div>
<script type="text/javascript">
jQuery( function($){
	$( '#<?php echo $id; ?>' ).on( 'change', '[name="frmplus_options[value]"]', function(){
		if ( $(this).val() == 'incrementer' ) {
			$( '#<?php echo $id; ?>' ).find( '.show-for-incrementer' ).show();
		} else {
			$( '#<?php echo $id; ?>' ).find( '.show-for-incrementer' ).hide();
		}
	});
	$( '#<?php echo $id; ?>' ).find( '[name="frmplus_options[value]"]' ).filter( ':checked' ).trigger( 'change' );
});
</script>
	<?php
	}

	public function render_callback( $args ){
				
		extract( $args );
		$options = $this->massageOptions( $options );

		switch ($precedence){
		case 'row':
			// This is a row of radio buttons, grouped together (so selecting one column deselects all others)
			switch( $options['value'] ) {
			case 'header':
				$column = $field['options']['col_' . ( $col_num + 1 ) ];
				$option_value = is_array( $column ) ? $column['name'] : $column;
				break;
			case 'incrementer':
				$option_value = $options['starting_value'] + ( $col_num * $options['increment'] );
				break;
			}
			echo '<input type="radio" class="radio table-cell" id="'.$this_field_id.'" name="'.$this_field_name.'" value="'.esc_attr($option_value).'" '.checked(true,in_array($value,array($option_value,FrmPlusFieldsHelper::get_simple_on_value())),false).' />'."\n";
			break;
		case 'column':
			$field_name = preg_replace( '/\[[0-9]+\]$/', '', $this_field_name );
			// This is a column of radio buttons, grouped together (so selecting one row deselects all others)
			switch( $options['value'] ) {
			case 'header':
				$row = $field['options']['row_' . ( $row_num + 1 ) ];
				$option_value = is_array( $row ) ? $row['name'] : $row;
				break;
			case 'incrementer':
				$option_value = $options['starting_value'] + ( $row_num * $options['increment'] );
				break;
			}
			echo '<input type="radio" class="radio radioline-transpose" id="'.$this_field_id.'" name="'.$field_name.'[transpose]['.$col_num.']" value="'.esc_attr($option_value).'" '.checked(true,in_array($value,array($option_value,FrmPlusFieldsHelper::get_simple_on_value())),false).' />'."\n";
			break;
		}
	}	
	
}

new FrmPlusRadioLineController();
	
