import domReady from '@wordpress/dom-ready';
import ServerSideRender from '@wordpress/server-side-render';

domReady(() => {
    const blockJsons = import.meta.glob('../views/blocks/**/block.json', { eager: true });
    const editFiles = import.meta.glob('../views/blocks/**/edit.jsx', { eager: true });

    for (const path in blockJsons) {
        const block = blockJsons[path].default || blockJsons[path];
        
        if (block.name && window.wp && window.wp.blocks) {
            const editPath = path.replace('block.json', 'edit.jsx');
            const editModule = editFiles[editPath];

            let editFunction;
            let saveFunction = () => null;

            if (editModule && editModule.default) {
                // Use React JSX component from edit.jsx
                editFunction = editModule.default;
                if (editModule.save) saveFunction = editModule.save;
            } else {
                // Fallback to ServerSideRender for simple blade-only blocks
                editFunction = function (props) {
                    return window.wp.element.createElement(ServerSideRender, {
                        block: block.name,
                        attributes: props.attributes,
                    });
                };
            }

            if (editFunction) {
                window.wp.blocks.registerBlockType(block.name, {
                    title: block.title,
                    category: block.category,
                    icon: block.icon,
                    description: block.description,
                    attributes: block.attributes,
                    supports: block.supports,
                    edit: editFunction,
                    save: saveFunction,
                });
            }
        }
    }
});
