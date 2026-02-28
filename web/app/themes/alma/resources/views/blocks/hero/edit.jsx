import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { badge, title, subtitle, downloadsText, imageUrl } = attributes;

    const blockProps = useBlockProps({
        className: 'relative w-full overflow-hidden py-16 lg:py-24 border border-dashed border-zinc-300 rounded-xl my-4',
    });

    return (
        <section {...blockProps}>
            {/* Sidebar Controls for things that are harder to click */}
            <InspectorControls>
                <PanelBody title={__('Hero Settings', 'alma')}>
                    <TextControl
                        label={__('Badge Text', 'alma')}
                        value={badge}
                        onChange={(val) => setAttributes({ badge: val })}
                        help={__('Optional small badge above title.', 'alma')}
                    />
                    <TextControl
                        label={__('Downloads Text', 'alma')}
                        value={downloadsText}
                        onChange={(val) => setAttributes({ downloadsText: val })}
                    />
                    <TextControl
                        label={__('Image URL', 'alma')}
                        value={imageUrl}
                        onChange={(val) => setAttributes({ imageUrl: val })}
                    />
                </PanelBody>
            </InspectorControls>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                    {/* Left Column: Content */}
                    <div className="flex flex-col items-start text-left space-y-6">

                        {/* Badge */}
                        {badge && (
                            <div className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-zinc-100 text-zinc-800">
                                {badge}
                                <svg className="w-4 h-4 ml-2 text-zinc-500" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </div>
                        )}

                        {/* Heading */}
                        <RichText
                            tagName="h1"
                            className="text-4xl sm:text-5xl lg:text-6xl font-black text-zinc-900 tracking-tight leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                            value={title}
                            onChange={(val) => setAttributes({ title: val })}
                            placeholder={__('High Converting Heading Comes Here', 'alma')}
                        />

                        {/* Subtitle */}
                        <RichText
                            tagName="p"
                            className="text-lg text-zinc-500 max-w-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                            value={subtitle}
                            onChange={(val) => setAttributes({ subtitle: val })}
                            placeholder={__('Use a clear headline, value prop, and app store buttons...', 'alma')}
                        />

                        {/* InnerBlocks Area for Buttons */}
                        <div className="w-full pt-2 p-4 bg-zinc-50 border border-zinc-200 rounded-lg">
                            <div className="text-xs text-zinc-400 font-semibold uppercase mb-2">Drop Buttons Here</div>
                            <InnerBlocks
                                allowedBlocks={['core/buttons', 'core/button', 'alma/cta-footer']}
                                template={[
                                    ['core/buttons', {}, [
                                        ['core/button', { text: 'Download App', className: 'is-style-fill' }],
                                        ['core/button', { text: 'Learn More', className: 'is-style-outline' }]
                                    ]]
                                ]}
                            />
                        </div>

                        {/* Social Proof / Downloads */}
                        <div className="flex items-center space-x-4 pt-4">
                            <div className="flex -space-x-2">
                                <div className="w-8 h-8 rounded-full border-2 border-white bg-zinc-200"></div>
                                <div className="w-8 h-8 rounded-full border-2 border-white bg-zinc-300"></div>
                                <div className="w-8 h-8 rounded-full border-2 border-white bg-zinc-400"></div>
                            </div>
                            <span className="text-sm font-medium text-zinc-600">
                                {downloadsText}
                            </span>
                        </div>
                    </div>

                    {/* Right Column: Image/Mockup Placeholder */}
                    <div className="relative w-full h-[500px] lg:h-[600px] flex items-center justify-center bg-zinc-100 rounded-3xl overflow-hidden border border-zinc-200">
                        {imageUrl ? (
                            <img src={imageUrl} alt="App Mockup" className="w-full h-full object-cover" />
                        ) : (
                            <div className="text-zinc-400 flex flex-col items-center">
                                <svg className="w-24 h-24 mb-4 text-zinc-300" fill="none" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                                <span className="text-sm font-medium">App Mockup Placeholder</span>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}
