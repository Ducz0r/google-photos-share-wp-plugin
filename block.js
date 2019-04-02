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

            function onChangeContent(newContent) {
                props.setAttributes({ content: newContent });
            }

            return el(
                'textarea', {
                    className: props.className,
                    onChange: onChangeContent,
                    value: content
                }
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