(function($) {

	$(document).ready(function() {
		
		var currentTime = new Date(),
			payablesByDate = [], 
			calendarEl = $( '#payables-calendar' ), 
			calendarObj = new FullCalendar.Calendar( calendarEl[0], {
				customButtons: {
				  prev: {
					text: 'Prev',
					click: function() {				
						init.loadPayables( 'prev' );
					}
				  },
				  next: {
					text: 'Next',
					click: function() {
						init.loadPayables( 'next' );
					}
				  },
				  today: {
					text: 'Hoje',
					click: function() {
						init.loadPayables( 'today' );
					}
				  },
				},
				initialDate: currentTime.toISOString(),
				initialView: 'dayGridMonth',
				locale: 'br'
			});
			
			calendarObj.render();

		var init = {
			
			requestPayables: function(order_id, date) {
			
				calendarEl.toggleClass( 'loading' );
				
				var data = {
						action: 'get_recipient_payables',
						nonce: PAGARME_MKTPC.nonce,
						data: { order_id: order_id, month: date },
					};
			
				$.post( PAGARME_MKTPC.ajaxurl, data, function(resp) {		
				console.log(resp); return;
					if (resp.success == true) {
						payablesByDate[date] = JSON.parse( resp.data );
						init.renderPayables( date, payablesByDate[date] );
						$( '#payables-calendar' ).trigger( 'loadPayables' );	
					} else {
						alert( 'Houve um erro ao obter os dados. Por favor se o problema persistir entre em contato com o responsável pelo site.' );
					}
				});
			},
			
			renderPayables: function(date, payables) {

				var transactions = payables.transactions,
					total_month = payables.toltal;

				$.each(transactions, function(date, payables) {
					init.setCalendarEvents( date, payables );
				});
				
				init.renderPayablesTotals(date);
			},

			renderPayablesTotals: function(date) {
				
				if( typeof payablesByDate[date] != 'undefined' ) {
					$( '#month-total' ).text( ( payablesByDate[date]['total'] / 100 ).toLocaleString('en-US', { style: 'currency', currency: 'BRL' }) );
				}
			},

			getCachedPayablesByDate: function(date) {
				
				if( typeof payablesByDate[date] == 'undefined' ) {
					init.requestPayables( false, date );
				}
				
				init.renderPayablesTotals(date);
				
				return payablesByDate[date];
			},

			setCalendarEvents: function(date, payables) {

				var payables_staus = payables.type,
					payables_total = payables.type.total / 100,
					labelcolor = ( payables.type.status ==  'waiting_funds' ? '#de9f25' : (payables.type.status ==  'paid' ? '#5cb85c' : (payables.type.status ==  'prepaid' ? '#de9f25' : '#eee' ) ) )
							
					calendarObj.addEvent({
						classNames: ['payables-day'],
						title: payables_total.toLocaleString('en-US', { style: 'currency', currency: 'BRL' }),
						start: date,
						end: date,
						color: labelcolor
					});
			},
			
			loadPayables: function( action ) {
				
				if( typeof action != 'undefined' ) {
					switch (action) {
						case 'prev':
							calendarObj.prev();
							break;
						case 'next':
							calendarObj.next();
							break;
						case 'today':
							calendarObj.today();
							break;
					}
				}
				
				var date = new Date(calendarObj.getDate()).toISOString().substr(0, 8) + '01';
				
				init.getCachedPayablesByDate( date );
			},
			
			renderOrderSummary: function( data ) {
				
				var html = '';
				
				$.each( data.transactions, function( i, transaction ) {
					html += '<li>' + 
						'<div class="img-type">' +
							'<img src="' + PAGARME_MKTPC.contenturl + 'assets/img/icons/' + transaction.payment_method + '.png"' + '/>' +
						'</div>' +
						'<div class="orde-info">' +
							'<strong class="order-id">' + ( ( transaction.order_id ) ? (  '<a target="_blank" href="' + transaction.order_link + '">#' + transaction.order_id + '</a>' ) : ( transaction.transaction_id ) ) +  '</strong>' +
							'<span class="order-desc">' + ( transaction.payment_method == 'boleto' ? 'Boleto Bancário' : ( transaction.payment_method == 'credit_card' ? 'Cartão de Crédito' : 'PIX' ) ) + '</span>' +
						'</div>' +
						'<span class="order-price">' + ( transaction.amount/100 ).toLocaleString('en-US', { style: 'currency', currency: 'BRL' }) + '</span>' +
					'</li>';
				});

				html += '<li>' + 
					'<div class="img-type" style="visibility: hidden">' +
					'</div>' +
					'<div class="orde-info">' +
						'<strong class="order-id">Total</strong>' +
					'</div>' +
					'<span class="order-price">' + ( data.total/100 ).toLocaleString('en-US', { style: 'currency', currency: 'BRL' }) + '</span>' +
				'</li>';
					
				$( '#payables-summary .order-list' ).html( html );
				
			}

		}
		
		$( '#payables-calendar' ).on( 'click', '.payables-day', function() { 
			
			var self = $( this ),
				allEventsDay = $( '#payables-calendar .payables-day' ),
				dateSelected = self.closest( '.fc-day[data-date]' ).data('date'),
				dateKey = new Date( dateSelected ).toISOString().substr(0, 8) + '01';
			
			allEventsDay.removeClass( 'payables-day-actived' ); 
			self.addClass( 'payables-day-actived' );
			init.renderOrderSummary( payablesByDate[dateKey]['transactions'][dateSelected]['type'] );						
		});

		$( '#payables-calendar' ).on( 'loadPayables', function() {
			
			if( $( '.fc-daygrid-event' ).hasClass( 'payables-day' ) ) {
				$( 'td.fc-daygrid-day.fc-day:not(.fc-day-other) .payables-day' )[0].click();
			}
			
			calendarEl.toggleClass( 'loading' );
		});


		
		init.loadPayables('today');

	});
	
})(jQuery);