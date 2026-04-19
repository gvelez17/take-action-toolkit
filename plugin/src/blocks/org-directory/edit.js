import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { showFilter, columns } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Directory Settings', 'take-action-toolkit' ) }>
					<ToggleControl
						label={ __( 'Show category filter', 'take-action-toolkit' ) }
						checked={ showFilter }
						onChange={ ( value ) => setAttributes( { showFilter: value } ) }
					/>
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
					<span className="dashicons dashicons-groups" style={ { fontSize: '36px' } }></span>
					<p><strong>{ __( 'Organization Directory', 'take-action-toolkit' ) }</strong></p>
					<p>{ __( 'Displays your local organizations as filterable cards.', 'take-action-toolkit' ) }</p>
					<p className="tat-editor-meta">
						{ columns } { __( 'columns', 'take-action-toolkit' ) }
						{ showFilter && ' · ' + __( 'Filter enabled', 'take-action-toolkit' ) }
					</p>
				</div>
			</div>
		</>
	);
}
