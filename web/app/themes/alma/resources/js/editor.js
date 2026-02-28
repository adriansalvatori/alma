import domReady from '@wordpress/dom-ready';

domReady(() => {
    const blocks = import.meta.glob('../views/blocks/**/block.json', { eager: true });

    for (const path in blocks) {
        const block = blocks[path].default || blocks[path];
        if (block.name && window.wp && window.wp.blocks && window.wp.serverSideRender) {
            window.wp.blocks.registerBlockType(block.name, {
                title: block.title,
                category: block.category,
                icon: block.icon,
                description: block.description,
                attributes: block.attributes,
                supports: block.supports,
                edit: function (props) {
                    return window.wp.element.createElement(window.wp.serverSideRender, {
                        block: block.name,
                        attributes: props.attributes,
                    });
                },
            });
        }
    }
});
