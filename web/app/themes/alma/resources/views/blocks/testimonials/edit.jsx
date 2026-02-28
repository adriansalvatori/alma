/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/blockEditor';

export default function Edit({ attributes, setAttributes }) {
    const { sectionTitle, description } = attributes;

    const blockProps = useBlockProps({
        className: 'py-24 border border-dashed border-zinc-300 rounded-xl my-4 bg-zinc-50'
    });

    const TEMPLATE = [
        ['core/group', { className: 'bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 flex flex-col justify-between' }, [
            ['core/paragraph', { content: '⭐⭐⭐⭐⭐', className: 'text-yellow-400 mb-4' }],
            ['core/paragraph', { content: '"Incredibly fast and beautifully designed. The development team really knew what they were doing when they built this."', className: 'text-zinc-600 italic mb-6' }],
            ['core/group', { className: 'flex items-center space-x-4' }, [
                ['core/image', { url: 'https://i.pravatar.cc/150?img=1', alt: 'Avatar', className: 'w-10 h-10 rounded-full' }],
                ['core/group', {}, [
                    ['core/heading', { level: 4, content: 'Alex Johnson', className: 'text-sm font-bold text-zinc-900 mb-0' }],
                    ['core/paragraph', { content: 'United States', className: 'text-xs text-zinc-500 mb-0' }]
                ]]
            ]]
        ]],
        ['core/group', { className: 'bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 flex flex-col justify-between' }, [
            ['core/paragraph', { content: '⭐⭐⭐⭐⭐', className: 'text-yellow-400 mb-4' }],
            ['core/paragraph', { content: '"Testimonials with names, ratings, and short blurbs help build authenticity and trust. This app completely changed my workflow."', className: 'text-zinc-600 italic mb-6' }],
            ['core/group', { className: 'flex items-center space-x-4' }, [
                ['core/image', { url: 'https://i.pravatar.cc/150?img=2', alt: 'Avatar', className: 'w-10 h-10 rounded-full' }],
                ['core/group', {}, [
                    ['core/heading', { level: 4, content: 'Maria Garcia', className: 'text-sm font-bold text-zinc-900 mb-0' }],
                    ['core/paragraph', { content: 'Spain', className: 'text-xs text-zinc-500 mb-0' }]
                ]]
            ]]
        ]],
        ['core/group', { className: 'bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 flex flex-col justify-between' }, [
            ['core/paragraph', { content: '⭐⭐⭐⭐', className: 'text-yellow-400 mb-4' }],
            ['core/paragraph', { content: '"We integrated this into our daily operations and saw a 40% increase in productivity. Highly recommended for remote teams."', className: 'text-zinc-600 italic mb-6' }],
            ['core/group', { className: 'flex items-center space-x-4' }, [
                ['core/image', { url: 'https://i.pravatar.cc/150?img=3', alt: 'Avatar', className: 'w-10 h-10 rounded-full' }],
                ['core/group', {}, [
                    ['core/heading', { level: 4, content: 'David Chen', className: 'text-sm font-bold text-zinc-900 mb-0' }],
                    ['core/paragraph', { content: 'Canada', className: 'text-xs text-zinc-500 mb-0' }]
                ]]
            ]]
        ]],
        ['core/group', { className: 'bg-white p-8 rounded-2xl shadow-sm border border-zinc-200 flex flex-col justify-between' }, [
            ['core/paragraph', { content: '⭐⭐⭐⭐⭐', className: 'text-yellow-400 mb-4' }],
            ['core/paragraph', { content: '"The support is fantastic and the features exactly match what we need. Cannot imagine going back to our old system."', className: 'text-zinc-600 italic mb-6' }],
            ['core/group', { className: 'flex items-center space-x-4' }, [
                ['core/image', { url: 'https://i.pravatar.cc/150?img=4', alt: 'Avatar', className: 'w-10 h-10 rounded-full' }],
                ['core/group', {}, [
                    ['core/heading', { level: 4, content: 'Sarah Williams', className: 'text-sm font-bold text-zinc-900 mb-0' }],
                    ['core/paragraph', { content: 'UK', className: 'text-xs text-zinc-500 mb-0' }]
                ]]
            ]]
        ]]
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
                        placeholder={__('Review Section', 'alma')}
                    />
                    <RichText
                        tagName="p"
                        className="mt-4 text-lg text-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 max-w-2xl mx-auto"
                        value={description}
                        onChange={(val) => setAttributes({ description: val })}
                        placeholder={__('Let happy users convince the rest.', 'alma')}
                    />
                    <div className="mt-6 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
                </div>

                <div className="p-4 bg-zinc-100 border border-zinc-200 rounded-lg">
                    <div className="text-xs text-zinc-400 font-semibold uppercase mb-4 text-left">Drop Review Cards Here</div>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
                        <InnerBlocks template={TEMPLATE} allowedBlocks={['core/group', 'core/columns', 'core/image', 'core/heading', 'core/paragraph']} />
                    </div>
                </div>
            </div>
        </section>
    );
}

export const save = () => <InnerBlocks.Content />;
