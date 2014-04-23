jQuery( function($){
	var me = FRM_PLUS_INCREMENTER;
	
	$.extend( me, {
		init: function(){
			$.each( me.particulars, function( index, particular ){
				particular.start = me.findStart( particular.start, particular.style );
				$( '#frm-table-' + particular.id ).on( 'add_row', null, particular, me.do_the_do );
				$( '#frm-table-' + particular.id ).on( 'delete_row', null, particular, me.do_the_do );
				$( '#frm-table-' + particular.id ).on( 'sort_rows', null, particular, me.do_the_do );
				// do it now
				me.do_the_do( {
					data: particular
				}, particular.id );
			});
		},
		
		styles: {
			1: function( c ){ return c; },
			A: function( c ){
				var a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
					b = ''; // the value that will be returned
				
				c = c - 1;
				if ( c >= a.length ){
					b = a.substr( Math.floor( c / a.length ) - 1, 1 );
					c = c  % a.length;
				}
				return b + a.substr( c, 1 );
			},
			a: function ( c ){ return me.styles.A( c ).toLowerCase(); },
			I: function ( c ){ return me.romanize( c ); },
			i: function ( c ){ return me.romanize( c ).toLowerCase(); },
		},
		
		do_the_do: function( e, field_id, tr ){
			var counter = e.data.start, $table = $( '#frm-table-' + field_id );
			$table.find( 'tbody .' + e.data.selector ).each( function(){
				$(this).find( 'input.incrementer' ).each( function(){
					$(this).val( me.styles[ e.data.style ](counter++) + e.data.suffix );
				});
			});
		},
		
		// Thanks http://blog.stevenlevithan.com/archives/javascript-roman-numeral-converter
		romanize: function(num) {
			if (!+num)
				return false;
			var	digits = String(+num).split(""),
				key = ["","C","CC","CCC","CD","D","DC","DCC","DCCC","CM",
				       "","X","XX","XXX","XL","L","LX","LXX","LXXX","XC",
				       "","I","II","III","IV","V","VI","VII","VIII","IX"],
				roman = "",
				i = 3;
			while (i--)
				roman = (key[+digits.pop() + (i * 10)] || "") + roman;
			return Array(+digits.join("") + 1).join("M") + roman;
		},
		
		findStart: function( start, style ){
			if ( !isNaN( parseInt( start ) ) ){
				return parseInt( start );
			}
			for( var i = 1; i < 10; i++ ){
				if ( me.styles[ style ]( i ) == start ){
					return i;
				}
			}
			return 1;
		}
	});
	
	me.init();
});
	
	