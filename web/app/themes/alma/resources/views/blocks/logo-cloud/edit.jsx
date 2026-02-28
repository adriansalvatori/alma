/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/blockEditor';

export default function Edit({ attributes, setAttributes }) {
    const { text } = attributes;

    const blockProps = useBlockProps({
        className: 'py-12 border border-dashed border-zinc-300 rounded-xl my-4 bg-white'
    });

    const TEMPLATE = [
        ['core/image', { url: 'https://tailwindui.com/plus/img/logos/158x48/tuple-logo-gray-900.svg', alt: 'Tuple', className: 'h-8 object-contain' }],
        ['core/image', { url: 'https://tailwindui.com/plus/img/logos/158x48/reform-logo-gray-900.svg', alt: 'Reform', className: 'h-8 object-contain' }],
        ['core/image', { url: 'https://tailwindui.com/plus/img/logos/158x48/savvycal-logo-gray-900.svg', alt: 'SavvyCal', className: 'h-8 object-contain' }],
        ['core/image', { url: 'https://tailwindui.com/plus/img/logos/158x48/statamic-logo-gray-900.svg', alt: 'Statamic', className: 'h-8 object-contain' }],
    ];

    return (
        <section {...blockProps}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
                    <div className="md:w-1/3">
                        <RichText
                            tagName="p"
                            className="text-sm font-semibold text-zinc-500 uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                            value={text}
                            onChange={(val) => setAttributes({ text: val })}
                            placeholder={__('WE ARE PARTNERED WITH...', 'alma')}
                        />
                    </div>
                    <div className="md:w-2/3 items-center opacity-60">
                        <div className="p-4 bg-zinc-50 border border-zinc-200 rounded-lg">
                            <div className="text-xs text-zinc-400 font-semibold uppercase mb-4 text-left">Drop Logos Here</div>
                            <div className="grid grid-cols-2 gap-8 md:grid-cols-4 items-center">
                                <InnerBlocks template={TEMPLATE} allowedBlocks={['core/image']} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

export const save = () => <InnerBlocks.Content />;
