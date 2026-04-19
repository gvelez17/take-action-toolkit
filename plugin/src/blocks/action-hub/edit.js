import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { showVolunteer, showDonate, showNewsletter } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Action Hub Settings', 'take-action-toolkit' ) }>
					<ToggleControl
						label={ __( 'Show volunteer button', 'take-action-toolkit' ) }
						checked={ showVolunteer }
						onChange={ ( value ) => setAttributes( { showVolunteer: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show donate button', 'take-action-toolkit' ) }
						checked={ showDonate }
						onChange={ ( value ) => setAttributes( { showDonate: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show newsletter signup', 'take-action-toolkit' ) }
						checked={ showNewsletter }
						onChange={ ( value ) => setAttributes( { showNewsletter: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<div className="tat-editor-placeholder">
					<span className="dashicons dashicons-megaphone" style={ { fontSize: '36px' } }></span>
					<p><strong>{ __( 'Action Hub', 'take-action-toolkit' ) }</strong></p>
					<p>{ __( 'Volunteer, donate, and newsletter buttons from your settings.', 'take-action-toolkit' ) }</p>
				</div>
			</div>
		</>
	);
}
