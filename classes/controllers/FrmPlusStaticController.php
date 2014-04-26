<?php
class FrmPlusStaticController{
	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'static',
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( !isset( $options['text'] ) ){
			$options['text'] = '';
		}
		if ( !isset( $options['multiline'] ) ){
			$options['multiline'] = false;
		}
		else{
			$options['multiline'] = $options['multiline'] == 'yes';
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $opt_key ){
		$options = $this->massageOptions( $options );
		$id = "static-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
?>
<div id="<?php echo $id; ?>">
	<div class="section">
		<label><?php _e( 'Text', FRMPLUS_PLUGIN_NAME ); ?>: </label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'The readonly string to insert into the table', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
		<?php if ( $options['multiline'] ) : ?>
			<textarea name="frmplus_options[text]"><?php echo esc_html( $options['text'] ); ?></textarea>
		<?php else: ?>
			<input type="text" name="frmplus_options[text]" value="<?php echo esc_attr($options['text']); ?>" >
		<?php endif; ?>
		<br/>
		<label><input type="checkbox" name="frmplus_options[multiline]" value="yes" <?php checked( true, $options['multiline'] ); ?>> <?php _e( 'Multiline', FRMPLUS_PLUGIN_NAME ); ?></label>
	</div>
</div>
<script type="text/javascript">
jQuery( function($){
	$( '#<?php echo $id; ?>' ).on( 'change', '[name="frmplus_options[multiline]"]', function(){
		var $input = $(this).closest( '.section' ).find( '[name="frmplus_options[text]"]' ),
			value = $input.val();
			
		if ( $(this).is( ':checked' ) ){
			$input.after( $( '<textarea name="frmplus_options[text]" />').val( value ) );
		}
		else{
			$input.after( $( '<input type="text" name="frmplus_options[text]" />').val( value ) );
		}
		$input.remove();
	});
});
</script>
	<?php
	}

	public function render_callback( $args ){
				
		extract( $args );
		$options = $this->massageOptions( $options );
		if ( empty( $value ) && !empty( $options['text'] ) ){
			$value = $options['text'];
		}

		if ( $options['multiline'] ){
			echo "<textarea readonly name=\"{$this_field_name}[$col_num]\" id=\"$this_field_id\" class=\"auto_width table-cell readonly\" >" . esc_html( $value ) . '</textarea>';
			echo '
<script type="text/javascript">
	jQuery(function($){
		var $input = $( "#' . $this_field_id . '" );
		$input.height( $input.get(0).scrollHeight );
	});	
</script>';
		}
		else{
			echo "<input type=\"text\" readonly name=\"{$this_field_name}[$col_num]\" id=\"$this_field_id\" value=\"" . esc_attr( $value ) . "\" class=\"auto_width table-cell readonly\"/>";
		}
	}	
	
}

new FrmPlusStaticController();
	
