import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Save function for the Aqualog Aquarium block.
 *
 * Renders the block content for the frontend display. Shows the selected
 * aquarium title if an aquarium is selected, or a message if none is selected.
 *
 * @since 1.0.0
 *
 * @param {Object} props               Block props.
 * @param {Object} props.attributes    Block attributes.
 * @param {number} props.attributes.postId    Selected aquarium post ID.
 * @param {string} props.attributes.postTitle Selected aquarium post title.
 * @return {JSX.Element} The rendered block content.
 */
export default function save( { attributes } ) {
    const { postId, postTitle } = attributes;
    const hasAquarium = postId && postId !== '';
    return (
        <div { ...useBlockProps.save() }>
            {hasAquarium ? (
                <p>{__('Selected aquarium:', 'aqualog')} {postTitle}</p>
            ) : (
                <p>{__('No aquarium selected.', 'aqualog')}</p>
            )}
        </div>
    );
}