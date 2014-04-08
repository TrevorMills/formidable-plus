<?php
class FrmPlusDataFromEntriesController{
	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
		add_action( 'wp_ajax_frmplus_get_field_selection', array( &$this, 'get_field_selection' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'data_from_entries',
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( !isset( $options ) ){
			$options = array();
		}
		if ( !isset( $options['source'] ) ){
			$options['source'] = array();
		}
		if ( !isset( $options['source']['form'] ) ){
			$options['source']['form'] = null;
		}
		if ( !isset( $options['source']['field'] ) ){
			$options['source']['field'] = null;
		}
		if ( !isset( $options['display'] ) ){
			$options['display'] = 'select';
		}
		if ( !isset( $options['restrict'] ) ){
			$options['restrict'] = false;
		}
		else{
			$options['restrict'] = $options['restrict'] == 'on';
		}
		if ( !isset( $options['multiselect'] ) ){
			$options['multiselect'] = false;
		}
		else{
			$options['multiselect'] = $options['multiselect'] == 'on';
		}
		if ( !isset( $options['autocom'] ) ){
			$options['autocom'] = false;
		}
		else{
			$options['autocom'] = $options['autocom'] == 'on';
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $index ){ 
		global $frm_form, $frm_field;
		$options = $this->massageOptions( $options );
		$id = "data-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
		$forms = $frm_form->getAll( "is_template=0 AND (status is NULL OR status = '' OR status = 'published')", 'order by name');
		?>
<div id="<?php echo $id; ?>">
	<p class="description">
		<?php _e( 'Blah blah blah', FRMPLUS_PLUGIN_NAME ); ?>
	</p>
	<div class="data-options">
		<div class="section">
			<label><?php _e( 'Import Data from', FRMPLUS_PLUGIN_NAME ); ?></label><br/>
			<select name="frmplus_options[source][form]" class="select-form">
				<option value="">--<?php _e( 'Select Form', FRMPLUS_PLUGIN_NAME ); ?>--</option>
				<option value="taxonomy" <?php selected( 'taxonomy', $options['source']['form'] ); ?>><?php _e('Use a Category/Taxonomy', FRMPLUS_PLUGIN_NAME ); ?></option>
				<?php foreach( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>" <?php selected( $form->id, $options['source']['form'] ); ?>><?php echo $form->name; ?></option>
				<?php endforeach; ?>
			</select>
			<div class="data-source-field">
				<?php if ( !empty( $options['source']['form'] ) ){
					echo $this->get_field_selection( $options['source']['form'], $options['source']['field'], true ); 
				} ?>
			</div>
		</div>
		<div class="section">
			<label><?php _e( 'Display as', FRMPLUS_PLUGIN_NAME ); ?></label>
			<select name="frmplus_options[display]">
				<option value="select" <?php selected( 'select', $options['display'] ); ?>><?php _e( 'Dropdown', FRMPLUS_PLUGIN_NAME ); ?></option>
				<option value="checkbox" <?php selected( 'checkbox', $options['display'] ); ?>><?php _e( 'Checkboxes', FRMPLUS_PLUGIN_NAME ); ?></option>
				<option value="radio" <?php selected( 'radio', $options['display'] ); ?>><?php _e( 'Radio Buttons', FRMPLUS_PLUGIN_NAME ); ?></option>
			</select>
		</div>
		<div class="section">
			<label><input type="checkbox" name="frmplus_options[restrict]" value="on" <?php checked( true, $options['restrict'] ); ?>><?php _e( 'Limit selection choices to those created by the user filling out this form', FRMPLUS_PLUGIN_NAME ); ?></label>
		</div>
		<div class="section">
			<label><input type="checkbox" name="frmplus_options[multiselect]" value="on" <?php checked( true, $options['multiselect'] ); ?>><?php _e( 'enable multiselect', FRMPLUS_PLUGIN_NAME ); ?></label>
			<label><input type="checkbox" name="frmplus_options[autocom]" value="on" <?php checked( true, $options['autocom'] ); ?>><?php _e( 'enable autocomplete', FRMPLUS_PLUGIN_NAME ); ?></label>
		</div>
	</div>
</div>
<script type="text/javascript">
jQuery( function($){
	$( '#<?php echo $id; ?>' ).on( 'change', '.select-form', function(){
		$(this).next( '.data-source-field' ).empty();
		if ( $(this).val() != '' ){
			$.post( ajaxurl, {
				action: 'frmplus_get_field_selection',
				form_id: $(this).val()
			},function( markup ){
				$( '#<?php echo $id; ?> .data-source-field' ).html( markup );
			});
		}
	});
})
</script>
		<?php
	}
	
	public function get_field_selection( $form_id = null, $field_id = null, $return = false){
		if ( defined( 'DOING_AJAX' ) ){
			extract( $_POST );
		}

		if ( 'taxonomy' === $form_id ){
			$fields = false;
			$taxonomies = get_taxonomies(array( 'public' => true ), 'objects');
		}
		else{
	        global $frm_field;
	        $fields = $frm_field->getAll(array('fi.form_id' => (int)$form_id, 'field_order') ); 
			$taxonomies = false;
			if ( !isset( $field_id ) ){
				$field_id = false;
			}
		}
		ob_start(); 
		if ( $fields || $taxonomies ) :?>
			<select name="frmplus_options[source][field]">
			<?php if ( $fields ) : ?>
				<option value="">--<?php _e( 'Select Field', FRMPLUS_PLUGIN_NAME ); ?>--</option>
				<?php foreach ( $fields as $field ) : ?>
					<option value="<?php echo $field->id; ?>" <?php selected( $field->id, $field_id ); ?>><?php echo $field->name; ?></option>
				<?php endforeach; ?>
			<?php elseif ( $taxonomies ) : ?>
				<option value="">--<?php _e( 'Select Taxonomy', FRMPLUS_PLUGIN_NAME ); ?>--</option>
				<?php foreach ( $taxonomies as $key => $taxonomy ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( $key, $field_id ); ?>><?php echo $taxonomy->label; ?></option>
				<?php endforeach; ?>
			<?php endif; ?>
			</select>
		<?php endif;
		
		$buffer = ob_get_clean();
		if ( $return ){
			return $buffer;
		}
		else{
			echo $buffer;
			die();
		}
	}
	
	public function render_callback( $args ){
		extract( $args );
		$options = $this->massageOptions( $options );
		
		if ( !isset( $options['source']['form'] ) || !isset( $options['source']['field'] ) ){
			return;
		}
		
		if ( 'taxonomy' === $options['source']['form'] ){
			$terms = get_terms( $options['source']['field'], array( 'hide_empty' => false ) );
			$values = array( '' => ' ' );
			foreach ( $terms as $term ){
				$values[ $term->term_id ] = $term->name;
			}
		}
		else{
			$values = array(
				'form_select' => $options['source']['field'],
				'hide_field' => false,
				'hide_opt' => false,
				'restrict' => $options['restrict']
			);
			global $frm_field;
			$field = $frm_field->getOne( $options['source']['field'] );
		
			if ( isset( $entry_id ) ){
				$values = FrmProFieldsHelper::get_linked_options( $values, $field, $entry_id );
			}
			else{
				$values = FrmProFieldsHelper::get_linked_options( $values, $field );
			}
		}
		switch ( $options['display'] ){
		case 'select': 
			if ( is_admin() && !defined( 'DOING_AJAX' ) ){
				$options['autocom'] = false;
			}
			if ( $options['autocom'] ){
		        global $frm_vars;
		        $frm_vars['chosen_loaded'] = true;
			}
			if ( is_array( $value ) ) $value = reset( $value ); ?>
			<select class="<?php echo ( $options['autocom'] ? 'frm_chzn' : '' ); ?>" <?php echo ( $options['multiselect'] ? 'multiple data-placeholder=" "' : '' ); ?> name="<?php echo "{$this_field_name}[$col_num]"; ?>" id="<?php echo $this_field_id; ?>">
				<?php foreach ( $values as $v ) : if ( $options['multiselect'] && $v == '' ) continue; ?>
				<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v, $value ); ?>><?php echo $v; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
			break;
		case 'checkbox': ?>
				<?php foreach ( $values as $option_num => $v ) : if ( $v == '' ) continue; ?>
					<label><input type="checkbox" name="<?php echo "{$this_field_name}[$col_num][]"; ?>" value="<?php echo esc_attr( $v ); ?>" class="checkbox table-cell id-has-option" id="<?php echo "{$this_field_id}_{$option_num}"; ?>" <?php checked( true, $v == $value || ( is_array( $value ) && in_array( $v, $value ) ) ); ?>><?php echo $v; ?></label>
				<?php endforeach; ?>
		<?php
			break;
		case 'radio': ?>
				<?php foreach ( $values as $option_num => $v ) : if ( $v == '' ) continue; ?>
					<label><input type="radio" name="<?php echo "{$this_field_name}[$col_num]"; ?>" value="<?php echo esc_attr( $v ); ?>" class="radio table-cell id-has-option" id="<?php echo "{$this_field_id}_{$option_num}"; ?>" <?php checked( true, $v == $value || ( is_array( $value ) && in_array( $v, $value ) ) ); ?>><?php echo $v; ?></label>
				<?php endforeach; ?>
		<?php
			break;
		}
	}
	
}

new FrmPlusDataFromEntriesController();