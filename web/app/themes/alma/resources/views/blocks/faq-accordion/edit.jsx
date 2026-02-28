/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, InnerBlocks } from '@wordpress/blockEditor';
import { Button } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { sectionTitle, description, faqs = [] } = attributes;

    const blockProps = useBlockProps({
        className: 'py-24 border border-dashed border-zinc-300 rounded-xl my-4'
    });

    const updateFaq = (index, key, value) => {
        const newFaqs = [...faqs];
        newFaqs[index] = { ...newFaqs[index], [key]: value };
        setAttributes({ faqs: newFaqs });
    };

    const addFaq = () => {
        setAttributes({ faqs: [...faqs, { question: 'New Question?', answer: 'New Answer.' }] });
    };

    const removeFaq = (index) => {
        const newFaqs = [...faqs];
        newFaqs.splice(index, 1);
        setAttributes({ faqs: newFaqs });
    };

    return (
        <section {...blockProps}>
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="lg:grid lg:grid-cols-3 lg:gap-12">

                    <div className="lg:col-span-1 mb-8 lg:mb-0 text-center lg:text-left">
                        <RichText
                            tagName="h2"
                            className="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl inline-block focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                            value={sectionTitle}
                            onChange={(val) => setAttributes({ sectionTitle: val })}
                            placeholder={__('FAQ Section', 'alma')}
                        />
                        <RichText
                            tagName="p"
                            className="mt-4 text-lg text-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-2 -mx-2"
                            value={description}
                            onChange={(val) => setAttributes({ description: val })}
                            placeholder={__('Reduce hesitation with smart answers.', 'alma')}
                        />
                        <div className="mt-8 p-4 bg-zinc-50 border border-zinc-200 rounded-lg">
                            <div className="text-xs text-zinc-400 font-semibold uppercase mb-4 text-left">Drop CTA Button Here</div>
                            <InnerBlocks allowedBlocks={['core/button', 'core/buttons']} template={[['core/buttons', {}, [['core/button', { text: 'Contact Support' }]]]]} />
                        </div>
                    </div>

                    <div className="lg:col-span-2 space-y-4">
                        {faqs.map((faq, index) => (
                            <div key={index} className="bg-zinc-50 border border-zinc-200 rounded-xl overflow-hidden relative group">
                                <div className="flex justify-between items-center w-full px-6 py-5 text-left border-b border-zinc-200">
                                    <RichText
                                        tagName="span"
                                        className="font-bold text-zinc-900 w-full focus:outline-none"
                                        value={faq.question}
                                        onChange={(val) => updateFaq(index, 'question', val)}
                                        placeholder={__('Question?', 'alma')}
                                    />
                                    <Button
                                        isDestructive
                                        isSmall
                                        onClick={() => removeFaq(index)}
                                        className="opacity-0 group-hover:opacity-100 transition-opacity ml-4 shrink-0"
                                    >
                                        Remove
                                    </Button>
                                    <svg className="w-5 h-5 text-zinc-400 shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                                <div className="px-6 py-5 text-zinc-600 bg-white">
                                    <RichText
                                        tagName="div"
                                        className="focus:outline-none"
                                        value={faq.answer}
                                        onChange={(val) => updateFaq(index, 'answer', val)}
                                        placeholder={__('Answer here...', 'alma')}
                                    />
                                </div>
                            </div>
                        ))}
                        <Button isSecondary onClick={addFaq} className="w-full justify-center">
                            + Add FAQ Item
                        </Button>
                    </div>

                </div>
            </div>
        </section>
    );
}

export const save = () => <InnerBlocks.Content />;
