jQuery( document ).ready( function ( $ ) {
		var body = $( 'body' );

		body.on( 'click', '.fed_notification_close_button', function ( e ) {
			var click = $( this );
			$url = click.data( 'url' );
			click.closest( '.fed_notification_container' ).remove();
			if ( $url.length > 10 ) {
				$.ajax( {
					type: 'POST',
					url: $url,
					data: {},
					success: function ( results ) {
						if ( results.success ) {
						}
					}
				} );
			}
		} );
	}
);
