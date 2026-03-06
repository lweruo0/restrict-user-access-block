/**
 * WordPress Imports.
 */
import { InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, TextareaControl } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import TokenMultiSelectControl from './token-multiselect-control';

export default createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		/**
		 * Extract Props
		 */
		const { attributes, setAttributes } = props;

		const [ruaLevelsBlock2, setValue] = useState(attributes.ruaLevelsBlock2);

		const handleChangeruaLevelsBlock2 = (newValue) => {
		  setValue(newValue);
		  setAttributes({ ruaLevelsBlock2: newValue });
		};
		
		const active_rua_levels = useSelect((select) => {
			const settings = select('core/editor').getEditorSettings();
			return settings.active_rua_levels;
		}, []);

		const OnChangeValueOptions = Object.keys(active_rua_levels).map(levelname => {
			return {
				value: levelname,
				label: levelname
			};
		});

		//console.log(OnChangeValueOptions); // Füge dies hinzu, um den Wert von

		return (
			<Fragment>
				<BlockEdit { ...props  } />
				<InspectorControls key="InspectorControls">
					<Panel>
						<PanelBody
							title={ __( 'Restrict User Access', 'rua' ) }
							icon="lock"
						>
						<TokenMultiSelectControl
						  label={__('select Levels')}
						  value={ruaLevelsBlock2}
						  options={OnChangeValueOptions}
						  onChange={handleChangeruaLevelsBlock2}
						/>
						</PanelBody>
					</Panel>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'RestrictUserAccessInspectorControls' );


/*
				{ruaLevelsBlock2.length > 0 ? (
				<div className="rua-locked-symboldiv" >
					<button className="rua-locked-symbol" >RUA Lock !!!!!!</button>
					<BlockEdit { ...props  } />
				</div>
				) : (
				<BlockEdit { ...props  } />
				)}
*/
