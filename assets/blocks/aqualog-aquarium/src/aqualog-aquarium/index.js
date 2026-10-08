/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';
/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';
import Edit from './edit';
import metadata from './block.json';
import save from './save';

/**
 * custom icon
 */
const calendarIcon = (
    <svg
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
        focusable="false"
    >
        <path d="m 9.099,6.223 c -0.93,0.38 -1.8,2.16 -1.91,3.08 -1.568,0.36 -4.12,1.927 -4.19,3.277 0.47,1.82 2.214,2.7 3.74,3.17 0.47,1.35 1.75,1.76 2.96,1.58 0.371,-0.22 0.21,-0.74 0.21,-1.03 2.341,-0.25 4.521,-0.66 6.211,-1.99 l 4.43,3.45 c 1.37,0.38 -0.79,-3.92 -1.01,-4.53 v 0 c -0.5,-5.217 2.99,-7.727 -3.47,-2.42 -0.36,-0.3 -0.67,-0.52 -1.08,-0.7 0.23,-0.967 1.53,-1.737 0.81,-2.337 0,0 -1.1,-0.43 -2.43,-0.83 -1.77,-0.48 -3.19,-0.79 -4.211,-0.72 z" />
    </svg>
);
/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	icon: calendarIcon,
	edit: Edit,
	save: save,
} );
