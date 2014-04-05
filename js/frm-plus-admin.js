jQuery(function($){
	var me = FRMPLUS;
	$.extend( FRMPLUS, {
		init: function(){
			$('#frm_form_editor_container')
				.on( 'change', '.frmplus_field_type', function(){
					var $form = $(this).parents( '.frm_single_option' ).find( '.frmplus_options_form' );
					var reopen = false;
					if ( $form.is( ':visible' ) ){
						$form.slideToggle( 200 );
						if ( me.types_with_options.indexOf( $(this).val() ) != -1 ){
							$form.parents( '.frm_single_option' ).find( '.frmplus_field_options' ).addClass( 'working' );
							reopen = true;
						}
					}
					$.post( ajaxurl, {
						action: 'frm_plus_edit_option',
						element_id: $(this).attr( 'id' ),
						update_what: 'type',
						update_value: $(this).val()
					}, function(){
						if ( reopen ){
							$form.parents( '.frm_single_option' ).find( '.frmplus_field_options' ).trigger( 'click' );
						}
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
					
					// Close, without saving, any other options forms that are open
					$(this).parents( 'form' ).find( '.frmplus_options_form' ).not( $form ).each( function(){
						if ( $(this).css( 'display' ) == 'block' ){
							$(this).slideToggle( 200, function(){
								$(this).find( '.form-contents' ).empty();
							});
						}
					});
					
					if ( $form.is( ':visible' ) ){
						$(this).removeClass( 'working' );
						$form.slideToggle( 200, function(){
							$(this).find( '.form-contents' ).empty();
						});
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
						$options.trigger( 'click' ); // will close the form
					});
				})
				.on( 'click', '.frmplus_options_form .cancel', function(){
					$(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' ).trigger( 'click' );
				})
				.on( 'change', '.frmplus_options_form input[name*="all_rows"]', function(){
					$( this ).parents( '.all_rows' ).next( '.select_rows' ).slideToggle();
				})
				.on( 'change', '.frmplus_options_form input[name*="all_columns"]', function(){
					$( this ).parents( '.all_columns' ).next( '.select_columns' ).slideToggle();
				})
				;
		}
	});
	
	me.init();
});