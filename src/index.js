/**
 * Imports
 */

import { addFilter } from '@wordpress/hooks';
import RestrictUserAccessInspectorControls from './RestrictUserAccessInspectorControls';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/*
 * Register attributes to every block
 */
addFilter(
	'blocks.registerBlockType',
	'crafted/crafted-style-helpers-attributes',
	( settings ) => {
		const { attributes } = settings;
		return {
			...settings,
			attributes: {
				...attributes,
				ruaLevelsBlock: {
					type: 'string',
					default: '',
				},
				ruaLevelsBlock2: {
					type: 'array',
					default: [],
				},
			},
		};
	}
);

/*
 * Add inspector controls to every block
 */
addFilter(
	'editor.BlockEdit',
	'crafted/crafted-style-helpers-inspector',
	RestrictUserAccessInspectorControls
);
