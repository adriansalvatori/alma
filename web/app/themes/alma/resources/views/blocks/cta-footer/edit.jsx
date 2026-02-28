/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks, InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { title, description, imageUrl } = attributes;

    const blockProps = useBlockProps({
        className: 'py-16 border border-dashed border-zinc-300 rounded-xl my-4'
    });

    return (
        <section {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('CTA Settings', 'alma')}>
                    <div className="components-base-control mb-4">
                        <label className="components-base-control__label block mb-2">{__('Image', 'alma')}</label>
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => setAttributes({ imageUrl: media.url })}
                                allowedTypes={['image']}
                                value={imageUrl}
                                render={({ open }) => (
                                    <Button isSecondary onClick={open} className="w-full justify-center">
                                        {imageUrl ? __('Change Image', 'alma') : __('Select Image', 'alma')}
                                    </Button>
                                )}
                            />
                        </MediaUploadCheck>
                        {imageUrl && (
                            <Button
                                isDestructive
                                isLink
                                className="mt-2 w-full text-center"
                                onClick={() => setAttributes({ imageUrl: '' })}
                            >
                                {__('Remove Image', 'alma')}
                            </Button>
                        )}
                    </div>
                </PanelBody>
            </InspectorControls>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="bg-zinc-100 rounded-3xl overflow-hidden shadow-sm border border-zinc-200">
                    <div className="grid grid-cols-1 lg:grid-cols-2">

                        {/* Left Side: CTA Content */}
                        <div className="p-10 sm:p-16 flex flex-col justify-center text-center lg:text-left">
                            <RichText
                                tagName="h2"
                                className="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl lg:text-5xl mb-6 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={title}
                                onChange={(val) => setAttributes({ title: val })}
                                placeholder={__('CTA Heading', 'alma')}
                            />

                            <RichText
                                tagName="p"
                                className="text-lg text-zinc-600 mb-8 max-w-xl mx-auto lg:mx-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={description}
                                onChange={(val) => setAttributes({ description: val })}
                                placeholder={__("Reinforce the download offer...", 'alma')}
                            />

                            {/* Buttons */}
                            <div className="w-full pt-2 p-4 bg-zinc-50 border border-zinc-200 rounded-lg">
                                <div className="text-xs text-zinc-400 font-semibold uppercase mb-2 text-left">Drop Buttons Here</div>
                                <InnerBlocks
                                    allowedBlocks={['core/buttons', 'core/button']}
                                    template={[
                                        ['core/buttons', {}, [
                                            ['core/button', { text: 'Download App', className: 'is-style-fill' }],
                                            ['core/button', { text: 'Learn More', className: 'is-style-outline' }]
                                        ]]
                                    ]}
                                />
                            </div>
                        </div>

                        {/* Right Side: Illustration Placeholder */}
                        <div className="hidden lg:flex items-center justify-center bg-zinc-200 p-12">
                            {imageUrl ? (
                                <img src={imageUrl} alt="CTA Illustration" className="w-full h-auto max-h-96 object-contain" />
                            ) : (
                                <div className="text-zinc-400 flex flex-col items-center">
                                    <svg className="w-32 h-32 mb-4" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                    <span className="text-sm font-medium">Illustration Placeholder</span>
                                </div>
                            )}
                        </div>

                    </div>
                </div>
            </div>
        </section>
    );
}

export const save = () => <InnerBlocks.Content />;
