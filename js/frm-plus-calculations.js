jQuery( function($){
	var me = FRM_PLUS_CALCULATIONS;
	
	$.extend( me, {
		/** 
		 * The event to watch to trigger recalculation.
		 * The keyup event makes changes to text fields trigger recalculation as they type
		 * The change event is necessary for select boxes
		 * older versions of IE may suffer performance hits using keyup on a form with lots of calculations.  
		 * Change this via something like jQuery(function($){FRM_PLUS_CALCULATIONS.keyup_event = 'change';})
		 */
		keyup_event: 'keyup change', // 
		
		/** 
		 * calculators is the internal cache of calculators
		 */
		calculators: {},

		/** 
		 * calculators that are the intersection of calculated rows and calculated columns
		 */
		special_calculators: {},
		
		init: function(){
			$.each( me.particulars, function( field_id, fields ){
				var table_selector = me.getTableSelector( field_id );

				// Sanitize Settings
				$.each( fields, function( key, settings ){					
					if ( typeof settings.function == 'undefined' ){
						settings.function = 'sum'; // default to "Sum"
					}		
					if ( settings.rows == 'tr' ){
						// It's a dynamic table set it up to contain the rows in the actual table
						settings.rows = [];
						$( table_selector + 'tbody tr' ).each( function(){
							settings.rows.push( $(this).attr( 'class' ).match( /row-[0-9]+/ )[0] );
						});
						
						$( table_selector ).on( 'add_row', function( e, field_id, tr){
							var selector = tr.attr( 'class' ).match( /row-[0-9]+/ )[0];
							if ( !tr.data( 'listener-added' ) ){
								tr.data( 'listener-added', true ); // only need to add this listener for the new row once.  This only kicks in if there is more than one calculation column
								$(this).on( me.keyup_event, '.' + selector, { field_id: field_id }, me.keyupListener );
							}

							if ( typeof me.calculators[ field_id ][ selector ] == 'undefined' ){
								me.calculators[ field_id ][ selector ] = {};
							}
							settings.rows.push( selector );
							me.calculators[ field_id ][ selector ][ key ] = settings;
						});
						$( table_selector ).on( 'delete_row', function ( e, field_id ){
							var last; // need to remove the last row out of the me.calculators[ field_id ] object
							$.each( me.calculators[ field_id ], function ( key ){
								last = key;
							});
							delete me.calculators[ field_id ][ last ];
							me.calculateOthers( table_selector ); // if there are other fields calculated with values from this table, this will update those calculations
						});
					}			
				});
				
				// Refactor Calculators - if we have multiple calculation lines in a single table, no need to add listeners
				// for each one.  Make a single listener and perform all calculations there
				var selectors = [], others = {};
				me.calculators[ field_id ] = {};
				me.special_calculators[ field_id ] = [];
				$.each( fields, function( key, settings ){
					$.each( settings[ me.getOpposite( key ) + 's' ], function( index, selector ){
						if ( typeof me.calculators[ field_id ][ selector ] == 'undefined' ){
							me.calculators[ field_id ][ selector ] = {};
						}
						me.calculators[ field_id ][ selector ][ key ] = settings;
						if ( typeof settings.other != 'undefined' ){
							others[ key ] = settings;
							$( me.getOtherSelector( settings.other.id ) ).prop( 'readonly', true ).addClass( 'calculation' );
						}
						if ( selectors.indexOf( selector ) == -1 ){
							selectors.push( '.' + selector );
						}
						
						if ( typeof me.calculators[ field_id ][ key ] != 'undefined' && typeof me.calculators[ field_id ][ key ][ selector ] != 'undefined' && me.calculators[ field_id ][ key ][ selector ][ 'function' ] == settings.function ){
							// This is a case where a calculated column intersects with a calculated row.  We can handle that.  
							me.special_calculators[ field_id ].push( me.getOpposite( selector ) == 'column' ? { row: selector, column: key } : { row: key, column: selector } ); 
						}
					});
				});
				if ( !$.isEmptyObject( others ) ){
					$( table_selector ).data( 'others', others );
				}
				
				$( table_selector ).on( me.keyup_event, selectors.join( ',' ), { field_id: field_id }, me.keyupListener);
			});
		},
		
		getTableSelector: function( field_id ){
			return '#frm-table-' + field_id + ' ';
		},
		
		getOtherSelector: function( id ){
			return 'form :input[name="item_meta[' + id + ']"]';
		},
		
		getOpposite: function( key ){ 
			return ( key.match( /^(row|column)-[0-9]+$/ )[1] == 'row' ? 'column' : 'row' ); // returns 'row' or 'column'
		},
		
		keyupListener: function( e ){
			if ( $(e.target).hasClass( 'calculation' ) ){
				// original target is a calculation field.  No further action required
				return;
			}
			
			// loop through all of the calculators for this selector
			var field_id = e.data.field_id,
				table_selector = me.getTableSelector( field_id ),
				classes = $(this).attr( 'class' ).match( /(row|column)-[0-9]+/ ),
				inputs = $( table_selector + '.' + classes[0] + ' :input' ).not( '.calculation' );
				
			$.each( me.calculators[ field_id ][ classes[0] ], function( key, settings ){
				var target = table_selector;
				if ( classes[1] == 'row' )
					target += '.' + classes[0] + ' .' + key; // row must always come first in the target_input_selector
				else
					target += '.' + key + ' .' + classes[0];
				
				target += ' input.calculation';	
				
				$( target ).val(
					me.toFixed( 
						// this next line may look a little confusing, but it's just a compact way of calling a function
						// ( i.e. me.add() ) with either all of the inputs, or just the non-empty inputs ( depending on 
						// the value of settings.include_empty )
						me[ settings.function ]( inputs.filter( function(){
							if ( !settings.include_empty && $(this).val() == '' ){
								// easy
								return false;
							}
							// trickier.  This is saying to return true if this input has a parent that matches the selectors given by getOpposite
							return $(this).parentsUntil( 'table', '.' + settings[ me.getOpposite( classes[0] ) + 's' ].join( ', .' ) ).length > 0;
						}))
					, settings )
				);
			});
			
			$.each( me.special_calculators[ field_id ], function( index, calculator ){
				var settings, target = table_selector + '.' + calculator.row + ' .' + calculator.column + ' input.calculation';

				if ( classes[1] == 'column' ){
					// We've just calculated a row ( based on inputs in a column )
					inputs = $( table_selector + '.' + calculator.row + ' :input' ).not( function(){ return $(this).parents( 'td' ).hasClass( calculator.column ); } );
					settings = me.calculators[ field_id ][ calculator.column ][ calculator.row ];
				}
				else{
					inputs = $( table_selector + '.' + calculator.column + ' :input' ).not( function(){ return $(this).parents( 'tr' ).hasClass( calculator.row ); } );
					settings = me.calculators[ field_id ][ calculator.row ][ calculator.column ];
				}
				
				$( target ).data( 'result-' + classes[1], 
					me.toFixed( 
						// this next line may look a little confusing, but it's just a compact way of calling a function
						// ( i.e. me.add() ) with either all of the inputs, or just the non-empty inputs ( depending on 
						// the value of settings.include_empty )
						me[ settings.function ]( inputs.filter( function(){
							if ( !settings.include_empty && $(this).val() == '' ){
								// easy
								return false;
							}
							// trickier.  This is saying to return true if this input has a parent that matches the selectors 
							return $(this).parentsUntil( 'table', '.' + settings[ classes[1] + 's' ].join( ', .' ) ).length > 0;
						}))
					, settings )
				);
				
				if ( $( target ).data( 'result-row' ) == $( target ).data( 'result-column' ) ){
					$( target ).val( $( target ).data( 'result-row' ) );
				}
				else if ( typeof $( target ).data( 'result-row' ) != 'undefined' && typeof $( target ).data( 'result-column' ) != 'undefined' ){
					$( target ).val( $( target ).data( 'result-row' ) + ' ' + me.__.column_indicator + ' ' + $( target ).data( 'result-column' ) + ' ' + me.__.row_indicator );
				}
			});
			
			me.calculateOthers( table_selector );
		},
		
		calculateOthers: function ( table_selector ){
			$.each( $( table_selector ).data( 'others' ) || {}, function( key, settings ){
				var other_target = me.getOtherSelector( settings.other.id ),
					inputs;
				if ( key.substr( 0, 3 ) == 'row' ){
					inputs = $( table_selector + '.' + key + ' td :input' );
				}
				else{
					inputs = $( table_selector + 'tr td.' + key + ' :input' );
				}
				
				$( other_target ).val(
					me.toFixed( 
						// this next line may look a little confusing, but it's just a compact way of calling a function
						// ( i.e. me.add() ) with either all of the inputs, or just the non-empty inputs ( depending on 
						// the value of settings.include_empty )
						me[ settings.other.function ]( inputs.filter( function(){
							if ( !settings.include_empty && $(this).val() == '' ){
								// easy
								return false;
							}
							// trickier.  This is saying to return true if this input has a parent that matches the selectors given by getOpposite
							return $(this).parentsUntil( 'table', '.' + settings[ me.getOpposite( key ) + 's' ].join( ', .' ) ).length > 0;
						}))
					, settings )
				);
			});
		},
		
		toFixed: function( number, settings ){
			if ( typeof number == 'string' ){
				return number;
			}
			else if ( isNaN( number ) || number == 0 ){
				return '';
			}
			else{
				number = number.toFixed( settings.precision );
				if ( !settings.forced ){
					number = number.replace( /0+$/, '' ); // strip off trailing 0's
					number = number.replace( /\.$/, '' ); // if there are no decimal points to display, strip the decimal
				}
				return number;
			}
		},
		
		parseNum: function(_str){
			var c;
			var str = '' + _str;
			if (str.match(/^\(?[\$ -]?[0-9\,\.]+[km ]?\)?$/i)){ 	// parse number (including $10,000; 10K and 100.00)
				c = parseFloat(str.replace(/[\$,\(\) ]/g,''));
				if (str.match(/k$/i)) c = c * 1000;
				if (str.match(/m$/i)) c = c * 1000000;
				if (str.match(/^\(.*\)$/)) c = -1 * c;
				return c;
			}
			else if (str.length === 0){
				return 0;
			}
			else{
				return false;
			}
		},
		
		sum: function( inputs ){
			var sum = 0, error = false;
			inputs.each( function(){
				var value = me.parseNum( $(this).val() );
				if ( value === false )
					error = true;
				else
					sum+= value;
				
			});
			return error ? me.__.error : sum;
		},

		average: function( inputs ){
			var sum = me.sum( inputs );
			if ( sum == me.__.error ){
				return sum;
			}
			else{
				return sum / me.count( inputs );
			}
		},
		
		count: function( inputs ){
			return inputs.length;
		},
		
		product: function( inputs ){
			var product = false, error = false;
			inputs.each( function(){
				var value = me.parseNum( $(this).val() );
				if ( value === false )
					error = true;
				else if ( product === false )
					product = value;
				else
					product = product * value;
			
			});
			return error ? me.__.error : product;
		}
		
	});
	
	me.init();
})