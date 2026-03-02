import domReady from '@wordpress/dom-ready';
import ServerSideRender from '@wordpress/server-side-render';

domReady(() => {
    // Glob from our Vite plugin's compiled output directory
    const blockJsons = import.meta.glob('../.blocks/**/block.json', { eager: true });
    
    // We still allow legacy edit.jsx blocks if they exist in the views directory
    const editFiles = import.meta.glob('../views/blocks/**/edit.jsx', { eager: true });

    const el = window.wp.element.createElement;
    const { InspectorControls, useBlockProps, MediaUpload, MediaUploadCheck } = window.wp.blockEditor || window.wp.editor;
    const { PanelBody, TextControl, TextareaControl, ToggleControl, SelectControl, Button, BaseControl } = window.wp.components;

    // Custom component to wrap MediaUpload
    const ImageControl = (props) => {
        return el(BaseControl, {
            label: props.label,
            className: 'alma-image-control'
        }, 
            el('div', { className: 'flex gap-2 items-center mt-2' },
                props.value ? el('img', { src: props.value, className: 'w-12 h-12 object-cover rounded border border-gray-200' }) : null,
                el(MediaUploadCheck, null, 
                    el(MediaUpload, {
                        onSelect: (media) => props.onChange(media.url),
                        allowedTypes: ['image'],
                        value: props.value,
                        render: ({ open }) => el(Button, {
                            onClick: open,
                            variant: props.value ? 'secondary' : 'primary',
                            text: props.value ? 'Replace Image' : 'Select Image'
                        })
                    })
                ),
                props.value ? el(Button, {
                    onClick: () => props.onChange(''),
                    isDestructive: true,
                    variant: 'link',
                    text: 'Remove'
                }) : null
            )
        );
    };

    for (const path in blockJsons) {
        const block = blockJsons[path].default || blockJsons[path];
        
        if (block.name && window.wp && window.wp.blocks) {
            // Find corresponding legacy edit path just in case
            const baseDirMatch = path.match(/\/\.blocks\/([^/]+)\/block\.json$/);
            const blockBaseName = baseDirMatch ? baseDirMatch[1] : '';
            const editPath = `../views/blocks/${blockBaseName}/edit.jsx`;
            const editModule = editFiles[editPath];

            let editFunction;
            let saveFunction = () => null;

            if (editModule && editModule.default) {
                // Use React JSX component from edit.jsx if it manually exists
                editFunction = editModule.default;
                if (editModule.save) saveFunction = editModule.save;
            } else {
                // Fallback to ServerSideRender and dynamic InspectorControls
                editFunction = function (props) {
                    const { attributes, setAttributes } = props;
                    
                    const controls = [];
                    if (block.attributes) {
                        for (const [key, attr] of Object.entries(block.attributes)) {
                            if (attr.control) {
                                let Component;
                                switch (attr.control) {
                                    case 'TextControl': Component = TextControl; break;
                                    case 'TextareaControl': Component = TextareaControl; break;
                                    case 'ToggleControl': Component = ToggleControl; break;
                                    case 'SelectControl': Component = SelectControl; break;
                                    case 'ImageControl': Component = ImageControl; break;
                                    default: Component = TextControl;
                                }
                                
                                if (Component) {
                                    // ToggleControl needs `checked`, others need `value`
                                    const valuePropName = attr.control === 'ToggleControl' ? 'checked' : 'value';
                                    
                                    controls.push(el(Component, {
                                        key: key,
                                        label: attr.label || key,
                                        [valuePropName]: attributes[key],
                                        options: attr.options,
                                        onChange: (val) => setAttributes({ [key]: val })
                                    }));
                                }
                            }
                        }
                    }
                    
                    const inspector = controls.length > 0 ? el(InspectorControls, null,
                        el(PanelBody, { title: 'Block Settings', initialOpen: true }, controls)
                    ) : null;
                    
                    const ssr = el(ServerSideRender, {
                        block: block.name,
                        attributes: props.attributes,
                    });
                    
                    const blockProps = useBlockProps ? useBlockProps() : {};
                    
                    return el('div', blockProps, inspector, ssr);
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
