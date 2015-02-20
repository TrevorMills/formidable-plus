// Array indexOf Polyfill
if(!Array.prototype.indexOf){Array.prototype.indexOf=function(e,t){var n;if(this==null){throw new TypeError('"this" is null or not defined')}var r=Object(this);var i=r.length>>>0;if(i===0){return-1}var s=+t||0;if(Math.abs(s)===Infinity){s=0}if(s>=i){return-1}n=Math.max(s>=0?s:i-Math.abs(s),0);while(n<i){var o;if(n in r&&r[n]===e){return n}n++}return-1}}
// String trim Polyfill
if(!String.prototype.trim){(function(){var e=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;String.prototype.trim=function(){return this.replace(e,"")}})()}

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

		/** 
		 * allowed prefixes for calculable fields
		 */
		prefixes: [ '$', '(' ],

		/** 
		 * allowed suffixes for calculable fields
		 */
		suffixes: [')', 'k', 'm' ],
		
		/** 
		 * only create this these regexps once
		 */
		regexps: {
			parseNum: false,
			ignorableChars: false
		},
		
		init: function(){
			$.each( me.particulars, function( field_id, fields ){
				var table_selector = me.getTableSelector( field_id );

				// Sanitize Settings
				$.each( fields, function( key, settings ){					

					if ( typeof settings['function'] == 'undefined' ){


						settings['function'] = 'sum'; // default to "Sum"
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
					if ( typeof settings.prefix != 'undefined' && me.prefixes.indexOf( settings.prefix ) == -1 ){
						me.prefixes.push( settings.prefix );
					}
					if ( typeof settings.suffix != 'undefined' && me.suffixes.indexOf( settings.suffix ) == -1 ){
						me.suffixes.push( settings.suffix );
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
						
						if ( typeof me.calculators[ field_id ][ key ] != 'undefined' && typeof me.calculators[ field_id ][ key ][ selector ] != 'undefined' && me.calculators[ field_id ][ key ][ selector ][ 'function' ] == settings['function'] ){
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
				inputs = $( table_selector + '.' + classes[0] + ' :input' ); //.not( '.calculation' );
				
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
						me[ settings['function']]( inputs.filter( function(){
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
			
			// Radiolines are a little tricky.  Changing a radio button in one cell affects the selected values of other cells.  
			// This little snippet checks to see if the cell we're processing contains just one radio button.  If it does, then we'll
			// call it a radioline and trigger 'change' on all associated buttons (with the same name).  The doing_radioline piece is so that 
			// when we trigger change on another radio button, it doesn't in turn trigger changes on the group again.
			var radioline_test = $(this).find('input[type="radio"]');
			if ( radioline_test.length == 1 && !me.doing_radioline ) {
				me.doing_radioline = true;
				$( table_selector ).find( 'input[name="' + radioline_test.attr('name') + '"]' ).not( radioline_test ).each( function(){
					$(this).trigger( 'change' );
				});
				me.doing_radioline = false;
			}
						
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
						me[ settings['function'] ]( inputs.filter( function(){
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
						me[ settings.other['function']]( inputs.filter( function(){
							if ( !settings.include_empty && $(this).val() == '' ){
								// easy
								return false;
							}
							// trickier.  This is saying to return true if this input has a parent that matches the selectors given by getOpposite
							return $(this).parentsUntil( 'table', '.' + settings[ me.getOpposite( key ) + 's' ].join( ', .' ) ).length > 0;
						}))
					, settings )
				).trigger( 'change' );
			});
		},
		
		// Thanks http://stackoverflow.com/questions/2593637/how-to-escape-regular-expression-in-javascript
		quote: function(str) {
			return (str+'').replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
		},
		
		// Thanks http://stackoverflow.com/questions/3753483/javascript-thousand-separator-string-format
		addThousandsSeparator: function(nStr) {
		    nStr += '';
		    x = nStr.split('.');
		    x1 = x[0];
		    x2 = x.length > 1 ? '.' + x[1] : '';
		    var rgx = /(\d+)(\d{3})/;
		    while (rgx.test(x1)) {
		            x1 = x1.replace(rgx, '$1' + me.__.thousands + '$2');
		    }
		    return x1 + x2;
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
				if ( typeof settings.prefix != 'undefined' ){
					number = settings.prefix + number;
				}
				if ( typeof settings.suffix != 'undefined' ){
					number = number + settings.suffix;
				}
				if ( me.__.thousands != '' ){
					number = me.addThousandsSeparator( number );
				}
				if ( me.__.decimal != '.' ){
					number = number.replace( /\./, me.__.decimal );
				}
				return number;
			}
		},
		
		parseNum: function(_str){
			var c;
			var str = ('' + _str).trim();
			if ( me.regexps.parseNum === false ){
				var escaped = {
					prefixes: me.prefixes,
					suffixes: me.suffixes
				}
				$.each( escaped, function( index, what ){
					$.each( what, function( inner, fix ){
						what[ inner ] = me.quote( fix );
					});
				})
				//console.log( '^(' + escaped.prefixes.join('|') + ')?[0-9' + me.quote( me.__.decimal ) + me.quote( me.__.thousands ) + ']+(' + escaped.suffixes.join('|') + ')?$' );
				me.regexps.parseNum = new RegExp( '^(\-|' + escaped.prefixes.join('|') + ')?[0-9' + me.quote( me.__.decimal ) + me.quote( me.__.thousands ) + ']+(' + escaped.suffixes.join('|') + ')?$', 'i' );
				me.regexps.ignorableChars = [
					new RegExp( '^(' + escaped.prefixes.join('|') + ')', 'i' ), // strips prefixes
					new RegExp( '(' + escaped.suffixes.join('|') + ')$', 'i' ), // strips suffixes
					new RegExp( '[' + me.quote( me.__.thousands ) + ']' ), //strips the thousands separator
				];
				me.regexps.decimalPoint = new RegExp( '[' + me.__.decimal + ']' );
			}
			//if (str.match(/^\(?[\$ -]?[0-9\,\.]+[km ]?\)?$/i)){ 	// parse number (including $10,000; 10K and 100.00)
			if ( str.match( me.regexps.parseNum ) ){ 	// parse number (including $10,000; 10K and 100.00)
				var full_str = str;
				$.each( me.regexps.ignorableChars, function( index, regexp ){
					str = str.replace( regexp, '' );
				});
				
				// convert the decimal point to a . ( as that's the only thing parseFloat understands )
				if ( me.__.decimal != '.' ){
					str = str.replace( me.regexps.decimalPoint, '.' );
				}
				c = parseFloat( str );
				if (full_str.match(/k$/i)) c = c * 1000;
				if (full_str.match(/m$/i)) c = c * 1000000;
				if (full_str.match(/^\(.*\)$/)) c = -1 * c; // if surrounded by parentheses, treat as negative number, 
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
				var value;
				if ( ( $(this).is( 'input[type="radio"]' ) || $(this).is( 'input[type="checkbox"]' ) ) && !$(this).is( ':checked' ) ) {
					value = 0;
				} else {
					value = me.parseNum( $(this).val() );
				};
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