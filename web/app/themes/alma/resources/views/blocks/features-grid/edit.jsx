/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/blockEditor';

export default function Edit({ attributes, setAttributes }) {
    const {
        sectionTitle,
        feature1Title, feature1Desc,
        feature2Title, feature2Desc,
        feature3Title, feature3Desc
    } = attributes;

    const blockProps = useBlockProps({
        className: 'py-16 border border-dashed border-zinc-300 rounded-xl my-4 bg-zinc-50'
    });

    return (
        <section {...blockProps}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-12">
                    <RichText
                        tagName="h2"
                        className="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl inline-block focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2"
                        value={sectionTitle}
                        onChange={(val) => setAttributes({ sectionTitle: val })}
                        placeholder={__('Features Section', 'alma')}
                    />
                    <div className="mt-4 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 text-left">
                    {/* Feature 1 */}
                    <div className="md:col-span-2 bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden flex flex-col md:flex-row">
                        <div className="p-8 md:w-1/2 flex flex-col justify-center">
                            <RichText
                                tagName="h3"
                                className="text-2xl font-bold text-zinc-900 mb-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={feature1Title}
                                onChange={(val) => setAttributes({ feature1Title: val })}
                                placeholder={__('Highlighted Feature 1', 'alma')}
                            />
                            <RichText
                                tagName="p"
                                className="text-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={feature1Desc}
                                onChange={(val) => setAttributes({ feature1Desc: val })}
                                placeholder={__('Feature description...', 'alma')}
                            />
                        </div>
                        <div className="bg-zinc-100 md:w-1/2 h-64 md:h-auto flex items-center justify-center">
                            <span className="text-4xl">📸</span>
                        </div>
                    </div>

                    {/* Feature 2 */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden flex flex-col">
                        <div className="p-8 flex-1">
                            <RichText
                                tagName="h3"
                                className="text-xl font-bold text-zinc-900 mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={feature2Title}
                                onChange={(val) => setAttributes({ feature2Title: val })}
                                placeholder={__('Highlighted Feature 2', 'alma')}
                            />
                            <RichText
                                tagName="p"
                                className="text-zinc-600 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={feature2Desc}
                                onChange={(val) => setAttributes({ feature2Desc: val })}
                                placeholder={__('Feature description...', 'alma')}
                            />
                        </div>
                        <div className="bg-zinc-100 h-48 flex items-center justify-center">
                            <span className="text-4xl">📊</span>
                        </div>
                    </div>

                    {/* Feature 3 */}
                    <div className="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden flex flex-col">
                        <div className="p-8 flex-1">
                            <RichText
                                tagName="h3"
                                className="text-xl font-bold text-zinc-900 mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={feature3Title}
                                onChange={(val) => setAttributes({ feature3Title: val })}
                                placeholder={__('Highlighted Feature 3', 'alma')}
                            />
                            <RichText
                                tagName="p"
                                className="text-zinc-600 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                                value={feature3Desc}
                                onChange={(val) => setAttributes({ feature3Desc: val })}
                                placeholder={__('Feature description...', 'alma')}
                            />
                        </div>
                        <div className="bg-zinc-100 h-48 flex items-center justify-center">
                            <span className="text-4xl">⚡</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    );
}

export const save = () => null;
