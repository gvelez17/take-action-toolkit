import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { limit, showFilter, showSubscribe, showMap } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Event List Settings', 'take-action-toolkit' ) }>
					<RangeControl
						label={ __( 'Number of events', 'take-action-toolkit' ) }
						value={ limit }
						onChange={ ( value ) => setAttributes( { limit: value } ) }
						min={ 1 }
						max={ 100 }
					/>
					<ToggleControl
						label={ __( 'Show event type filter', 'take-action-toolkit' ) }
						checked={ showFilter }
						onChange={ ( value ) => setAttributes( { showFilter: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show subscribe links', 'take-action-toolkit' ) }
						checked={ showSubscribe }
						onChange={ ( value ) => setAttributes( { showSubscribe: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show map links', 'take-action-toolkit' ) }
						checked={ showMap }
						onChange={ ( value ) => setAttributes( { showMap: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<div className="tat-editor-placeholder">
					<span className="dashicons dashicons-calendar-alt" style={ { fontSize: '36px' } }></span>
					<p><strong>{ __( 'Event Calendar', 'take-action-toolkit' ) }</strong></p>
					<p>{ __( 'Displays upcoming events from your connected calendar.', 'take-action-toolkit' ) }</p>
					<p className="tat-editor-meta">
						{ __( 'Showing up to', 'take-action-toolkit' ) } { limit } { __( 'events', 'take-action-toolkit' ) }
						{ showFilter && ' · ' + __( 'Filter enabled', 'take-action-toolkit' ) }
					</p>
				</div>
			</div>
		</>
	);
}
