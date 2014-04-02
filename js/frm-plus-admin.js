jQuery(function($){
	var me = FRMPLUS;
	$.extend( FRMPLUS, {
		init: function(){
			$('#frm_form_editor_container')
				.on( 'change', '.frmplus_field_type', function(){
					$.post( ajaxurl, {
						action: 'frm_plus_edit_option',
						element_id: $(this).attr( 'id' ),
						update_what: 'type',
						update_value: $(this).val()
					});
					if ( me.types_with_options.indexOf( $(this).val() ) != -1 ){
						$(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' ).show();
					}
					else{
						$(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' ).hide();
					}
				})
				.on( 'click', '.frmplus_field_options', function(){
					var $form = $(this).parents( '.frm_single_option' ).find( '.frmplus_options_form' );
					if ( $form.is( ':visible' ) ){
						$(this).removeClass( 'working' );
						$form.slideToggle( 200 );
					}
					else{
						var $this = $(this);
						$this.addClass( 'working' );
						$.post( ajaxurl, {
							action: 'frm_plus_get_options_form',
							element_id: $this.attr( 'id' )
						}, function( markup ){
							$this.removeClass( 'working' );
							$form.find( '.form-contents' ).html( markup );
							$form.find( '.save' ).removeAttr( 'disabled' );
							$form.show();
						})
					}
				})
				.on( 'click', '.frmplus_options_form .save', function(){
					var $options = $(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' );
					$options.addClass( 'working' );
					$(this).prop( 'disabled', true );
					$.post( ajaxurl, {
						action: 'frm_plus_edit_option',
						element_id: $options.attr( 'id' ),
						update_what: 'options',
						update_value: $(this).parents( '.frmplus_options_form' ).find( ':input' ).serialize()
					}, function( result ){
						console.log( result );
						$options.trigger( 'click' ); // will close the form
					});
				})
				.on( 'click', '.frmplus_options_form .cancel', function(){
					$(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' ).trigger( 'click' );
				});
		}
	});
	
	me.init();
});