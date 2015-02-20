<?php
class FrmPlusDatePickerController{
	/** 
	 * Holds available options for the jQuery.datepicker UI
	 * See http://api.jqueryui.com/datepicker/
	 */
	var $dp_options = array(
			'appendText',
			'changeMonth',
			'changeYear',
			'constrainInput',
			'dateFormat',
			'dayNames',
			'dayNamesMin',
			'dayNamesShort',
			'defaultDate',
			'firstDay',
			'gotoCurrent',
			'hideIfNoPrevNext',
			'isRTL',
			'maxDate',
			'minDate',
			'monthNames',
			'monthNamesShort',
			'navigationAsDateFormat',
			'nextText',
			'numberOfMonths',
			'prevText',
			'selectOtherMonths',
			'shortYearCutoff',
			'showAnim',
			'showCurrentAtPos',
			'showMonthAfterYear',
			'showOtherMonths',
			'showWeek',
			'stepMonths',
			'weekHeader',
			'yearRange',
			'yearSuffix',
		);
		
	var $dp_boolean_options = array(
		'changeMonth',
		'changeYear',
		'constrainInput',
		'gotoCurrent',
		'hideIfNoPrevNext',
		'isRTL',
		'navigationAsDateFormat',
		'selectOtherMonths',
		'showMonthAfterYear',
		'showOtherMonths',
		'showWeek',
	);

	var $dp_array_options = array(
		'dayNames',
		'dayNamesMin',
		'dayNamesShort',
		'monthNames',
		'monthNamesShort',
	);

	var $dp_numeric_options = array(
		'firstDay',
		'numberOfMonths',
		'showCurrentAtPos',
		'shortYearCutoff',
		'stepMonths',
	);
	
	/** 
	 * Whether scripts have been enqueued
	 */
	var $enqueued = false;
	
	/** 
	 * Particulars for each instance
	 */
	var $particulars = array();
	
	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'datepicker',
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( empty( $options ) || !isset( $options['option'] ) || !isset( $options['value'] ) ){
			return array();
		}
		$o = array_combine( $options['option'], $options['value'] );
		array_pop( $o ); // the last one is saved from the template <div/>
		foreach ( $o as $key => $value ){
			if ( in_array( $key, $this->dp_boolean_options ) ){
				$o[$key] = ( $value == 'true' ? true : false );
			}
			if ( in_array( $key, $this->dp_array_options ) ){
				$o[$key] = array_map( 'trim', explode( ',', $value ) );
			}
			if ( in_array( $key, $this->dp_numeric_options ) ){
				$o[$key] = intval( $value );
			}
		}
		return $o;
	}
	
	public function options_callback( $options, $field, $index ){ 
		$options = $this->massageOptions( $options );
		$id = "datepicker-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
		?>
<div id="<?php echo $id; ?>">
	<p class="description">
		<input type="button" class="add-option" value="<?php echo __( 'Add Option', FRMPLUS_PLUGIN_NAME ); ?>" style="float:right;margin:0 0 0.5em 0.5em"/>
		<?php printf( __( 'Enter as many options for this jQuery.datepicker instance as you want.  Information about options can be found at %s.  For %s types, write %s or %s.  For %s types, write a comma separated list', FRMPLUS_PLUGIN_NAME ), '<a href="http://api.jqueryui.com/datepicker/" target="_blank">api.jqueryui.com/datepicker</a>', '<strong>Boolean</strong>', 'true', 'false', '<strong>Array</strong>' ); ?>
	</p>
	<div class="datepicker-option template" style="display:none">
		<select name="frmplus_options[option][]">
				<option value=""><?php _e( 'Select Option', FRMPLUS_PLUGIN_NAME ); ?></option>
			<?php foreach ( $this->dp_options as $option ) : ?>
				<option value="<?php echo $option; ?>"><?php echo $option; ?>
					<?php echo ( in_array( $option, $this->dp_boolean_options ) ? sprintf( '(%s)', __( 'Boolean', FRMPLUS_PLUGIN_NAME ) ) : '' ); ?>
					<?php echo ( in_array( $option, $this->dp_array_options ) ? sprintf( '(%s)', __( 'Array', FRMPLUS_PLUGIN_NAME ) ) : '' ); ?>
					<?php echo ( in_array( $option, $this->dp_numeric_options ) ? sprintf( '(%s)', __( 'Numeric', FRMPLUS_PLUGIN_NAME ) ) : '' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="frmplus_options[value][]" />
		<input type="button" class="delete" value="<?php _e( 'Delete', FRMPLUS_PLUGIN_NAME ); ?>" />
	</div>
</div>
<script type="text/javascript">		
jQuery(function($){
	var options = <?php echo json_encode( $options ); ?>,
		$wrapper = $( '#<?php echo $id; ?>' );
		$template = $wrapper.find( '.template' );

	$.each( options, function( option, value ){
		var $clone = $template.clone().removeClass( 'template' );
		$clone.find( 'select' ).val( option );
		$clone.find( 'input[type="text"]' ).val( value );
		$template.before( $clone );
		$clone.show();
	});
	
	$wrapper
		.on( 'click', '.add-option', function(){
			var $clone = $wrapper.find( '.template' ).clone().removeClass( 'template' );
			$template.before( $clone );
			$clone.show();
		})
		.on( 'click', '.delete', function(){
			var $form = $(this).parents( '.form-contents' );
			$(this).parents( '.datepicker-option' ).remove();
			$form.find( '.add-option' ).trigger( 'change' ); // saves the form back to the server
		});
});
</script>
		<?php
	}
	
	public function render_callback( $args ){
		if ( !$this->enqueued ){
			$this->enqueued = true;
		    wp_enqueue_script('jquery-ui-datepicker');
		    wp_enqueue_style('jquery-theme');
			if ( is_admin() && !defined( 'DOING_AJAX' ) ){
			    add_action( 'admin_footer', array( &$this, 'particulars' ) );
			}
			else{
			    add_action( 'wp_footer', array( &$this, 'particulars' ) );
			}
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
			console.log( $('#frm-table-<?php echo "$field_id"; ?>') );
			$( '#frm-table-<?php echo "$field_id .$key"; ?> input' ).datepicker(<?php echo json_encode( $options ); ?>);
			$( '#frm-table-<?php echo "$field_id"; ?>' )
				.on('add_row',function( event, field_id, tr ){
					tr.find( '.<?php echo $key; ?> input' ).datepicker(<?php echo json_encode( $options ); ?>);
				})
				.on('sort_rows',function( event, field_id ){
					$('#frm-table-'+field_id).find( '.<?php echo $key; ?> input' ).datepicker('destroy').datepicker(<?php echo json_encode( $options ); ?>);
				})
			;
		<?php endforeach; ?>
	<?php endforeach; ?>
});
</script>		
		<?php
		echo str_replace( array( "\t", "\n" ), '', ob_get_clean() );
	}	
}

new FrmPlusDatePickerController();
