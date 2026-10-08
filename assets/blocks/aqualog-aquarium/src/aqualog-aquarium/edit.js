/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
    const { postId } = attributes;

    // 1. Pobieranie opublikowanych wpisów z WordPress store
    const { posts, hasResolved } = useSelect((select) => {
        const query = {
            status: 'publish',
            per_page: 100, // Maksymalna liczba wpisów do pobrania
        };

        return {
            posts: select(coreDataStore).getEntityRecords('postType', 'iw_aquarium', query),
            hasResolved: select(coreDataStore).hasFinishedResolution('getEntityRecords', ['postType', 'iw_aquarium', query]),
        };
    }, []);
	    const options = [
        { value: '', label: __('Select Aquarium', 'aqualog') }
    ];
    if (posts) {
        posts.forEach((post) => {
            options.push({
                value: post.id.toString(), 
                label: post.title.rendered ? post.title.rendered : `(No title #${post.id})`
            });
        });
    }
	/**
	 * get selected post
	 */
	const selectedPost = posts ? posts.find(post => post.id.toString() === postId) : null;
	const selectedPostTitle = selectedPost && selectedPost.title.rendered 
        ? selectedPost.title.rendered 
        : '('+__('No title #', 'aqualog')+postId+')';

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Aquarium', 'aqualog' ) }>
					<SelectControl
                        label={__('Select Aquarium', 'aqualog')}
                        value={postId}
                        options={options}
                        onChange={(value) => {
                            const selectedPost = posts ? posts.find(post => post.id.toString() === value) : null;
                            const selectedPostTitle = selectedPost && selectedPost.title.rendered 
                                ? selectedPost.title.rendered 
                                : '('+__('No title #', 'aqualog')+value+')';
                            setAttributes({ postId: value, postTitle: selectedPostTitle });
                        }}
                        disabled={!hasResolved}
                        help={!hasResolved ? __('Loading...', 'aqualog') : ''}
                    />
                </PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
                {postId ? (
                    <p>{__('Selected aquarium:', 'aqualog')} {selectedPostTitle}</p>
                ) : (
                    <p>{__('No aquarium selected.', 'aqualog')}</p>
                )}
			</div>
		</>
	);
}
