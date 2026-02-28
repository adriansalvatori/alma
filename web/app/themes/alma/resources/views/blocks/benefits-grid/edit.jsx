/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/blockEditor';

export default function Edit({ attributes, setAttributes }) {
    const { sectionTitle } = attributes;
    const blockProps = useBlockProps({
        className: 'py-16 border border-dashed border-zinc-300 rounded-xl my-4 text-center'
    });

    const TEMPLATE = [
        ['core/group', { className: 'flex flex-col items-center' }, [
            ['core/paragraph', { content: '🚀', className: 'text-4xl mb-4' }],
            ['core/heading', { level: 3, content: 'Blazing Fast', className: 'text-xl font-bold text-zinc-900 mb-2 text-center' }],
            ['core/paragraph', { content: 'Brief benefit explanation text here.', className: 'text-zinc-600 text-center' }]
        ]],
        ['core/group', { className: 'flex flex-col items-center' }, [
            ['core/paragraph', { content: '🛡️', className: 'text-4xl mb-4' }],
            ['core/heading', { level: 3, content: 'Secure', className: 'text-xl font-bold text-zinc-900 mb-2 text-center' }],
            ['core/paragraph', { content: 'Brief benefit explanation text here.', className: 'text-zinc-600 text-center' }]
        ]],
        ['core/group', { className: 'flex flex-col items-center' }, [
            ['core/paragraph', { content: '📱', className: 'text-4xl mb-4' }],
            ['core/heading', { level: 3, content: 'Mobile First', className: 'text-xl font-bold text-zinc-900 mb-2 text-center' }],
            ['core/paragraph', { content: 'Brief benefit explanation text here.', className: 'text-zinc-600 text-center' }]
        ]],
        ['core/group', { className: 'flex flex-col items-center' }, [
            ['core/paragraph', { content: '✨', className: 'text-4xl mb-4' }],
            ['core/heading', { level: 3, content: 'Beautiful Design', className: 'text-xl font-bold text-zinc-900 mb-2 text-center' }],
            ['core/paragraph', { content: 'Brief benefit explanation text here.', className: 'text-zinc-600 text-center' }]
        ]],
    ];

    return (
        <section {...blockProps}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-16">
                    <RichText
                        tagName="h2"
                        className="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl inline-block focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2"
                        value={sectionTitle}
                        onChange={(val) => setAttributes({ sectionTitle: val })}
                        placeholder={__('Why Choose Us Section', 'alma')}
                    />
                    <div className="mt-4 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
                </div>

                <div className="p-4 bg-zinc-50 border border-zinc-200 rounded-lg">
                    <div className="text-xs text-zinc-400 font-semibold uppercase mb-4 text-left">Drop Benefit Columns Here</div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 text-center items-start">
                        <InnerBlocks template={TEMPLATE} allowedBlocks={['core/group', 'core/columns', 'core/image', 'core/heading', 'core/paragraph', 'core/list']} />
                    </div>
                </div>
            </div>
        </section>
    );
}

export const save = () => <InnerBlocks.Content />;
