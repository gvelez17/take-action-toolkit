import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { columns } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Business Directory Settings', 'take-action-toolkit' ) }>
					<RangeControl
						label={ __( 'Columns', 'take-action-toolkit' ) }
						value={ columns }
						onChange={ ( value ) => setAttributes( { columns: value } ) }
						min={ 1 }
						max={ 4 }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<div className="tat-editor-placeholder">
					<span className="dashicons dashicons-store" style={ { fontSize: '36px' } }></span>
					<p><strong>{ __( 'Business Directory', 'take-action-toolkit' ) }</strong></p>
					<p>{ __( 'Displays aligned local businesses.', 'take-action-toolkit' ) }</p>
				</div>
			</div>
		</>
	);
}
