/**
 * Organization Directory block — Interactivity API store.
 *
 * Handles category filtering on the frontend.
 */
import { store, getContext } from '@wordpress/interactivity';

store( 'take-action/orgs', {
	state: {
		get isHidden() {
			const ctx = getContext();
			const { filter } = ctx;
			if ( filter === 'all' ) {
				return false;
			}
			const categories = ( ctx.categories || '' ).split( ',' );
			return ! categories.includes( filter );
		},
	},
	actions: {
		setFilter( event ) {
			const ctx = getContext();
			ctx.filter = event.target.closest( '[data-filter]' ).dataset.filter;
		},
	},
} );
