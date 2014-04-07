jQuery( function($){
	var me = FRM_PLUS_INCREMENTER;
	
	$.extend( me, {
		init: function(){
			$.each( me.particulars, function( index, particular ){
				$( '#frm-table-' + particular.id ).on( 'add_row', me.do_the_do );
				$( '#frm-table-' + particular.id ).on( 'delete_row', me.do_the_do );
			});
		},
		
		do_the_do: function( e, field_id, tr ){
			var counter = 1, $table = $( '#frm-table-' + field_id );
			$table.find( 'tbody tr' ).each( function(){
				$(this).find( 'input.incrementer' ).each( function(){
					$(this).val( counter++ );
				});
			});
		}
	});
	
	me.init();
});
	
	