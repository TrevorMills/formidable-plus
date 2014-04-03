<?php
class FrmPlusCalculationsController{
	
	var $available_calculations = array(
		'sum',
		'average',
		'count',
	);

	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'calculation',
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( isset( $options['calculations'] ) && is_object( $options['calculations'] ) ){
			$options['calculations'] = (array)$options['calculations'];
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $index ){
		$options = $this->massageOptions( $options );
		$id = "calculation-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
		?>
<div id="<?php echo $id; ?>">
	<p class="description">
		<?php printf( __( 'Blah blah blah' ) ); ?>
	</p>
	<div class="calculation-option">
		<label><?php _e( 'Calculation', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<select name="frmplus_options[calculations][type]">
			<?php foreach ( $this->available_calculations as $option ) : ?>
				<option value="<?php echo $option; ?>" <?php selected( $option, $options['calculations']['type'] ); ?>><?php echo __( ucwords( $option ), FRMPLUS_PLUGIN_NAME ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>
		<?php
	}
	
	public function render_callback( $args ){
		if ( !$this->enqueued ){
			$this->enqueued = true;
		    wp_enqueue_script('jquery-ui-datepicker');
		    wp_enqueue_style('jquery-theme');
		    add_action( 'wp_footer', array( &$this, 'particulars' ) );
		}
		
		extract( $args );

		if ( !isset( $this->particulars[ $field['id'] ] ) ){
			$this->particulars[ $field['id'] ] = array();
		}

		$key = ( $precedence == 'column' ? "column-$col_num" : "row-$row_num" );
		$this->particulars[ $field['id'] ][$key] = $this->massageOptions($options);
		
		echo '<input type="text" size="10" id="'.$this_field_id.'" name="'.$this_field_name.'['.$col_num.']" value="'.esc_attr($value).'" class="auto_width table-cell" />';
	}
	
	public function particulars(){
		ob_start();
		?>
<script type="text/javascript">
jQuery(function($){
	<?php foreach ( $this->particulars as $field_id => $instances ) : ?>
		<?php foreach ( $instances as $key => $options ) : ?>
			$( '#frm-table-<?php echo "$field_id .$key"; ?> input' ).datepicker(<?php echo json_encode( $options ); ?>);
			$( '#frm-table-<?php echo "$field_id"; ?>' ).on('add_row',function( event, field_id, tr ){
				tr.find( '.<?php echo $key; ?> input' ).datepicker(<?php echo json_encode( $options ); ?>);
			});
		<?php endforeach; ?>
	<?php endforeach; ?>
});
</script>		
		<?php
		echo str_replace( array( "\t", "\n" ), '', ob_get_clean() );
	}	
}

new FrmPlusCalculationsController();
