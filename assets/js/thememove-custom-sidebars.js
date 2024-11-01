'use strict';

let tmcs = ( ( $ ) => {

	return {
		init() {
			this.removeSidebar();
			this.addNewSidebar();
		},
		removeSidebar() {
			$( 'body' ).on( 'click', '.tm-remove-sidebar', ( e ) => {
				e.preventDefault();

				let $el = $( e.currentTarget ),
					$error = $( '.tmc-error-text' ),
					$table = $( '#tm-custom-sidebars-table' ),
					row = $el.closest( 'tr' ),
					sidebarName = row.find( 'td' ).eq( 0 ).text(),
					slug = $el.attr( 'data-slug' ),
					nonce = $el.attr( 'data-nonce' ),
					answer = confirm( 'Are you sure you want to remove "' + sidebarName + '" ?\nThis action will remove all widgets you have assigned to the sidebar.' ),
					data = {
						'action': 'remove_custom_sidebar',
						'sidebar_slug': slug,
						'_wpnonce': nonce
					};

				if ( answer ) {

					$error.hide();

					$.ajax({
						type: 'POST',
						url: tmcsVars.ajax_url,
						data: data
					}).done( ( response ) => {

						if ( response.success ) {
							row.remove();

							if ( 1 === $table.find( 'tr' ).length ) {
								$table.append( '<tr class="tm-custom-sidebars-empty"><td colspan="3">No custom sidebar created</td></tr>' );
							}
						} else {
							$error.show().html( response.data.length ? response.data : 'There was an error occurs when deleting this sidebar, please try again.' );
						}
					}).fail( ( jqXHR, textStatus ) => {
						console.error( `${jqXHR.responseText}: ${jqXHR.status}` );
						console.error( `${textStatus}` );
					});
				}
			});
		},
		addNewSidebar() {

			$( '#tm-custom-sidebars-form' ).on( 'submit', ( e ) => {
				e.preventDefault();

				let $sidebarName = $( '#sidebar-name' ),
					$error = $( '.tmc-error-text' ),
					sidebarName = $sidebarName.val(),
					data = {
						'action': 'add_custom_sidebar',
						'sidebar_name': sidebarName,
						'_wpnonce': $( '#sidebar-wpnonce' ).val()
					};

				$error.hide();

				$.ajax({
					type: 'POST',
					url: tmcsVars.ajax_url,
					data: data
				}).done( ( response ) => {

					if ( response.success ) {
						if ( response.data.slug ) {
							$( '#tm-custom-sidebars-table' ).append( '<tr><td>' + sidebarName + '</td><td>' + response.data.slug + '</td><td><a href="#" class="tm-remove-sidebar" data-slug="' + response.data.slug + '" data-nonce="' + response.data.nonce + '"><i class="fal fa-times"></i> Remove</a></td></tr>' );
							$( '.tm-custom-sidebars-empty' ).remove();
							$sidebarName.val( '' );
						}
					} else {
						$error.show().html( response.data.length ? response.data : 'There was an error occurs when adding new sidebar, please try again.' );
					}
				}).fail( ( jqXHR, textStatus ) => {
					console.error( `${jqXHR.responseText}: ${jqXHR.status}` );
					console.error( `${textStatus}` );
				});
			});
		}
	};
})( jQuery );

jQuery( document ).ready( () => tmcs.init() );
