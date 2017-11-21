<?php
class FrmPlusCalculationsController{
	
	var $available_calculations = array(
		'sum',
		'average',
		'count',
		'product',
	);
	
	var $enqueued = false;

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
		if ( isset( $options ) && is_object( $options ) ){
			$options = (array)$options;
		}
		elseif( !isset( $options ) ){
			$options = array();
		}
		
		if ( !isset( $options['precision'] ) ){
			$options['precision'] = 2;
		}
		else{
			$options['precision'] = intval( $options['precision'] );
		}
		if ( !isset( $options['include_empty'] ) ){
			$options['include_empty'] = true;
		}
		else{
			$options['include_empty'] = $options['include_empty'] == 'yes';
		}
		if ( isset( $options['forced'] ) ){
			$options['forced'] = $options['forced'] == 'on';
		}
		foreach ( array( 'rows', 'columns' ) as $what ){
			if ( !isset( $options[ "all_$what" ] ) ){
				$options[ "all_$what" ] = true;
			}
			else{
				$options[ "all_$what" ] = $options[ "all_$what" ] == 'yes';
			}
		}
		if ( !isset( $options['other'] ) || empty( $options['other']['active'] ) || empty( $options['other']['id'] )){
			$options['other'] = array( 
				'active' => false,
				'function' => null,
				'id' => null
			);
		}
		else{
			$options['other']['active'] = $options['other']['active'] == 'yes';
		}
		if ( !isset( $options['prefix'] ) ){
			$options['prefix'] = '';
		}
		if ( !isset( $options['suffix'] ) ){
			$options['suffix'] = '';
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $opt_key ){
		$options = $this->massageOptions( $options );
		$id = "calculation-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
		
	    list($columns,$rows) = FrmPlusFieldsHelper::get_table_options( maybe_unserialize($field->options) );
		$is_a = substr($opt_key,0,3); // 'row' or 'col'
		?>
<div id="<?php echo $id; ?>">
	<div class="calculation-option">
		<div class="section">
			<label><?php _e( 'Function', FRMPLUS_PLUGIN_NAME ); ?>:</label>
			<select name="frmplus_options[function]">
				<?php foreach ( apply_filters( 'frmplus_available_calculations', $this->available_calculations ) as $option ) : ?>
					<option value="<?php echo $option; ?>" <?php selected( $option, $options['function'] ); ?>><?php echo __( ucwords( $option ), FRMPLUS_PLUGIN_NAME ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="section">
			<label><?php _e( 'Precision:', FRMPLUS_PLUGIN_NAME ); ?></label>
			<select name="frmplus_options[precision]">
				<?php for( $p = 0; $p < 5; $p ++) : ?>
					<option value="<?php echo $p; ?>" <?php selected( $p, $options['precision'] ); ?>><?php echo $p . ' ' . __( 'decimal places', FRMPLUS_PLUGIN_NAME ); ?></option>
				<?php endfor; ?>
			</select>
			<label>
				<input type="checkbox" name="frmplus_options[forced]" value="on" <?php checked( true, $options['forced'] ); ?>> <?php _e( 'forced', FRMPLUS_PLUGIN_NAME ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'If forced, then this number of decimals will always show.  Otherwise, they only show when non-zero.', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
			</label>
		</div>
		<div class="section">
			<label><?php _e( 'Prefix', FRMPLUS_PLUGIN_NAME ); ?>: </label>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'This string will be prepended to the calculation.  Examples might be $ (dollar sign) or other currency symbol.', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
			<input type="text" name="frmplus_options[prefix]" value="<?php echo $options['prefix']; ?>" >
			<br/>
			<label><?php _e( 'Suffix', FRMPLUS_PLUGIN_NAME ); ?>: </label>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'This string will be appended to the calculation.  Examples might be % (percent)', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
			<input type="text" name="frmplus_options[suffix]" value="<?php echo $options['suffix']; ?>" >
		</div>
		<div class="section">
			<?php _e( 'Include empty inputs in calculation?', FRMPLUS_PLUGIN_NAME ); ?>
			<label><input type="radio" value="yes" name="frmplus_options[include_empty]" <?php checked( true, $options['include_empty'] ); ?>><?php _e( 'Yes', FRMPLUS_PLUGIN_NAME ); ?></label>
			<label><input type="radio" value="no" name="frmplus_options[include_empty]" <?php checked( false, $options['include_empty'] ); ?>><?php _e( 'No', FRMPLUS_PLUGIN_NAME ); ?></label>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( __( 'This comes into play for calculations where the number of elements is important (like average and count)', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
		</div>
		<?php foreach( array( 'rows', 'columns' ) as $what ) : ?>
			<div class="section">
				<div class="all_<?php echo $what; ?>">
					<?php _e( 'Include all', FRMPLUS_PLUGIN_NAME ); ?> <?php _e( $what, FRMPLUS_PLUGIN_NAME ); ?>:
					<label><input type="radio" value="yes" name="frmplus_options[<?php echo "all_{$what}"; ?>]" <?php checked( true, $options[ "all_{$what}" ] ); ?>><?php _e( 'Yes', FRMPLUS_PLUGIN_NAME ); ?></label>
					<label><input type="radio" value="no" name="frmplus_options[<?php echo "all_{$what}"; ?>]" <?php checked( false, $options[ "all_{$what}" ] ); ?>><?php _e( 'No', FRMPLUS_PLUGIN_NAME ); ?></label>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php echo esc_attr( sprintf( __( 'If there are %s that are not meant to be numeric, like a label or a date, then those %s should not be included in the calculation.', FRMPLUS_PLUGIN_NAME ), __( $what, FRMPLUS_PLUGIN_NAME ), __( $what, FRMPLUS_PLUGIN_NAME ) ) ); ?>"></span>
				</div>
				<div class="select_<?php echo $what; ?>" <?php if ( $options[ "all_{$what}" ] ) : ?>style="display:none"<?php endif; ?>>
					<?php printf( __( 'Which %s should be included?', FRMPLUS_PLUGIN_NAME ), __( $what, FRMPLUS_PLUGIN_NAME ) ); ?>
					<?php foreach ( $$what as $target => $opt ) : $label = FrmPlusFieldsHelper::parse_option( $opt, 'name' ); 
						if ( $target == $opt_key ) continue;
						if ( false && substr( $what, 0, 3 ) == substr( $is_a, 0, 3 ) && FrmPlusFieldsHelper::parse_option( $opt, 'type' ) == 'calculation' ) continue; ?>
						<div>
							<label><input type="checkbox" name="frmplus_options[<?php echo $what; ?>][]" value="<?php echo $target; ?>" <?php checked( true, !isset( $options[$what] ) || in_array( $target, $options[$what] ) ); ?>> <?php echo $label; ?></label>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<div class="section">
			<label><input type="checkbox" name="frmplus_options[other][active]" value="yes" <?php checked( true, $options[ 'other' ]['active'] ); ?>> <?php _e( 'Place the', FRMPLUS_PLUGIN_NAME ); ?></label>
			<select name="frmplus_options[other][function]">
				<?php foreach ( $this->available_calculations as $option ) : ?>
					<option value="<?php echo $option; ?>" <?php selected( $option, $options['other']['function'] ); ?>><?php echo __( ucwords( $option ), FRMPLUS_PLUGIN_NAME ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php _e( 'of these fields into this field: ', FRMPLUS_PLUGIN_NAME ); ?>
			<?php 
				global $wpdb;
				$fields = FrmField::getAll( $wpdb->prepare( 'fi.form_id = %d AND fi.type IN ( "text", "number", "hidden" )', $field->form_id ), 'field_order');
			?>
			<select name="frmplus_options[other][id]">
				<option value="">--<?php _e( 'Choose a field', FRMPLUS_PLUGIN_NAME ); ?>--</option>
				<?php foreach ( $fields as $f ) : ?>
					<option value="<?php echo $f->id; ?>" <?php selected( $f->id, $options['other']['id'] ); ?>><?php echo $f->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</div>
		<?php
	}
	
	public function render_callback( $args ){
		if ( !$this->enqueued ){
			$this->enqueued = true;
		    wp_enqueue_script( 'frm-plus-calculations', plugins_url( 'formidable-plus/js/frm-plus-calculations.js' ), array( 'jquery' ) );
			add_action( ( is_admin() && !defined( 'DOING_AJAX' ) ) ? 'admin_footer' : 'wp_footer', array( &$this, 'localize_script' ) );
		}
				
		extract( $args );

		if ( !isset( $this->particulars[ $field['id'] ] ) ){
			$this->particulars[ $field['id'] ] = array();
		}

		$key = ( $precedence == 'column' ? "column-$col_num" : "row-$row_num" );
		$this->particulars[ $field['id'] ][$key] = $this->massageOptions($options);

		echo '<input type="text" size="10" id="'.$this_field_id.'" name="'.$this_field_name.'['.$col_num.']" value="'.esc_attr($value).'" class="auto_width table-cell calculation" readonly />';
	}
	
	public function localize_script(){
		wp_localize_script( 'frm-plus-calculations', 'FRM_PLUS_CALCULATIONS', 
			apply_filters( 'frm-plus-calculations-localization', array( 
				'particulars' => $this->prepareForLocalization( $this->particulars ),
				'__' => array(
					'error' => __( 'Error', FRMPLUS_PLUGIN_NAME ),
					'row_indicator' => '↔',
					'column_indicator' => '↕',
					'decimal' => '.',
					'thousands' => ','
				)
			))
		);
	}
	
	public function prepareForLocalization( $particulars ){
		//echo '<pre>' . print_r( $particulars, true ) . '</pre>';
		foreach ( $particulars as $field_id => $table_fields ){
			$field = FrmField::getOne( $field_id );
			list( $columns, $rows ) = FrmPlusFieldsHelper::get_table_options( maybe_unserialize($field->options) );
			//echo '<pre>' . print_r( $rows, true ) . '</pre>';
			foreach ( $table_fields as $key => $settings ){
				foreach ( array( 'rows', 'columns' ) as $what ){
					if ( $settings[ "all_$what" ] ){
						$settings[ $what ] = array();
						foreach ( array_keys( $$what ) as $index ){
							$settings[ $what ][] = $index;
						}
					}
					foreach ( $settings[ $what ] as $index => $target ){
						$settings[ $what ][ $index ] = substr( $target, 0, 3 ) == 'row' ? 'row-' . array_search( $target, array_keys( $rows ) ) : 'column-' . array_search( $target, array_keys( $columns ) );
						if ( $settings[ $what ][ $index ] == $key ) {
							// Do not include the $what itself, it leads to exponential growth :)
							unset( $settings[ $what ][ $index ] );
						}
					}
					$settings[ $what ] = array_values( $settings[ $what ] );
					unset( $settings[ "all_$what" ] ); // not needed anymore
				}
				// @debug
				if ( isset( $settings[ 'on' ] ) ){
					unset( $settings[ 'on' ] );
				}
				if ( !isset( $settings[ 'function' ] ) ){
					$settings[ 'function' ] = 'sum';
				}
				if ( !$settings['other']['active'] ){
					unset( $settings['other'] );
				}
				if ( count( $rows ) == 0 ){
					$settings[ 'rows' ] = 'tr';
				}
				if ( empty( $settings['prefix'] ) ){
					unset( $settings['prefix'] );
				}
				if ( empty( $settings['suffix'] ) ){
					unset( $settings['suffix'] );
				}
				$particulars[ $field_id ][ $key ] = $settings;
			}
		}
		return $particulars;
	}
	
}

new FrmPlusCalculationsController();
