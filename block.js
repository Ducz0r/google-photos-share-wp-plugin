/**
 * Gutenberg block element
 */
(function(blocks, i18n, element, editor) {
    var __ = i18n.__;
    var el = element.createElement;

    blocks.registerBlockType('google-photos-share-block/block', {
        title: __('Google Photos Share', 'GB'),
        icon: 'images-alt',
        category: 'common',
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'p',
            },
        },

        edit: function(props) {
            var content = props.attributes.content;

            return el(
                'div', { className: 'lmgps-block-content' },
                el('div', { className: 'lmgps-label' },
                    el('span', {
                        className: 'dashicons dashicons-images-alt'
                    }),
                    el('span', {
                        className: 'lmgps-label'
                    }, __('Google Photos Share', 'GB')),
                ),
                el('div', { className: 'lmgps-instructions' },
                    __('Choose an existing Google Photos Share, or use a new one.', 'GB'),
                ),
                el('div', { className: 'lmgps-btns' },
                    el('a', {
                        className: 'components-button is-button is-default is-large lmgps-existing'
                    }, __('Existing Share', 'GB')),
                    el('a', {
                        className: 'components-button is-button is-default is-large lmgps-new'
                    }, __('New Share', 'GB'))
                )
            );
        },

        save: function(props) {
            return el(
                'p', { value: props.attributes.content }
            );
        },
    });
}(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.editor
));