/**
 * Event List block — Interactivity API store.
 *
 * Handles In-Person / Virtual / Hybrid filtering on the frontend.
 */
import { store, getContext } from '@wordpress/interactivity';

store( 'take-action/events', {
	state: {
		get isHidden() {
			const ctx = getContext();
			const { filter } = ctx;
			if ( filter === 'all' ) {
				return false;
			}
			return ctx.type !== filter;
		},
	},
	actions: {
		setFilter( event ) {
			const ctx = getContext();
			ctx.filter = event.target.closest( '[data-filter]' ).dataset.filter;
		},
	},
} );
