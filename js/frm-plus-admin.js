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
							$form.parents( '.frm_single_option' ).find( '.frmplus_field_options' ).trigger( 'mouseup' );
						}
					});
					if ( me.types_with_options.indexOf( $(this).val() ) != -1 ){
						$(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' ).show();
					}
					else{
						$(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' ).hide();
					}
				})
				.on( 'mouseup', '.frmplus_field_options', function(){
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
							$form.show();
						})
					}
				})
				.on( 'change', '.frmplus_options_form :input', function(){
					var $options = $(this).parents( '.frm_single_option' ).find( '.frmplus_field_options' );
					$options.addClass( 'working' );
					$.post( ajaxurl, {
						action: 'frm_plus_edit_option',
						element_id: $options.attr( 'id' ),
						update_what: 'options',
						update_value: $(this).parents( '.frmplus_options_form' ).find( ':input' ).serialize()
					}, function( result ){
						$options.removeClass( 'working' );
					});
				})
				.on( 'change', '.frmplus_options_form input[name*="all_rows"]', function(){
					$( this ).parents( '.all_rows' ).next( '.select_rows' ).slideToggle();
				})
				.on( 'change', '.frmplus_options_form input[name*="all_columns"]', function(){
					$( this ).parents( '.all_columns' ).next( '.select_columns' ).slideToggle();
				})
				.on( 'click', '.frm_add_field_row', function(){
					var $options = $(this).closest( '.ui-sortable' ).find( '.dynamic-table-options' );
					if ( $options.is( ':visible' ) ){
						var hideMe = function(){
							$( document ).off( 'ajaxComplete', hideMe );
							if ( $options.is( ':visible' ) ){
								$options.fadeOut({queue: false}).slideToggle({queue: false});
							}
						}
						$( document ).on( 'ajaxComplete', hideMe );
					}
				})
				.on( 'click', '.frm_delete_field_row', function(){
					// If they're deleting the last row, wait for thr XHR to return and then show the dynamic options
					var $siblings = $(this).closest( '.frm_single_option_sortable' ).siblings( '.frm_single_option_sortable' ),
						has_visible_siblings = false;
					
					$siblings.each( function(){
						if ( $(this).find( '.frm_single_option' ).is( ':visible' ) ){
							has_visible_siblings = true;
							return false;
						}
					});
					if ( !has_visible_siblings ){
						var $options = $(this).closest( '.ui-sortable' ).find( '.dynamic-table-options' );
						var showMe = function(){
							$( document ).off( 'ajaxComplete', showMe );
							if ( !$options.is( ':visible' ) ){
								setTimeout( function(){
									$options.fadeIn({queue: false});
								}, 600 ); // timeout to coincide with when the option has faded out
							}
						}
						$( document ).on( 'ajaxComplete', showMe );
					}
				})
				;
				
		}
	});
	
	if ( typeof window.frm_add_field_option == 'undefined' ) {
		$.extend( window, {
			frm_add_field_option: function(field_id,table){
				var data = {action:'frm_add_field_option',field_id:field_id,t:table};
				jQuery.post(ajaxurl,data,function(msg){
					jQuery('#frm_field_'+field_id+'_opts').append(msg);
					if(table=='row'){ jQuery('#frm-grid-'+field_id+' tr:last').after(msg);}
				});
			},
			frm_delete_field_option: function(){
				var cont = jQuery(this).parent('.frm_single_option').attr('id'); 
				//id = 'frm_delete_field_'+field_id+'-'+opt_key+'_container'
				var fk=cont.replace('frm_delete_field_', '').replace('_container', '').split('-');
				jQuery.ajax({type:'POST',url:ajaxurl,
			        data:'action=frm_delete_field_option&field_id='+fk[0]+'&opt_key='+fk[1],
			        success:function(msg){
						jQuery('#'+cont).fadeOut('slow', function(){
							jQuery('#'+cont).remove();
						});
					}
			    });
			}
		});
	}
	
	me.init();
});