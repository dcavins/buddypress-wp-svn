/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies.
 */
import './dynamic-members.scss';
import editDynamicMembersBlock from './edit';
import metadata from './block.json';

registerBlockType( metadata, {
	icon: {
		background: '#fff',
		foreground: '#d84800',
		src: 'groups',
	},
	edit: editDynamicMembersBlock,
} );
