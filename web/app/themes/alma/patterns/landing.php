<?php
/**
 * Title: Campaign landing page
 * Slug: alma/landing
 * Categories: alma-pages
 * Description: A single-offer landing page for paid traffic: centred hero, benefits, proof, FAQ and the form. No distractions.
 * Inserter: no
 */

use App\Blocks\BenefitsGrid;
use App\Blocks\Faq;
use App\Blocks\Hero;
use App\Blocks\Testimonials;
use App\Support\BlockMarkup;

?>
<?= BlockMarkup::block(Hero::class, [
    'layout' => 'glow',
    'eyebrow' => 'Limited offer',
    'headline' => 'Book a free 30-minute strategy call',
    'subheadline' => 'Tell us where your site is today and leave with a plan for the next ninety days.',
    'primary_cta_text' => 'Book my call',
    'primary_cta_url' => '#contact',
    'secondary_cta_text' => '',
]) ?>

<?= BlockMarkup::block(BenefitsGrid::class, ['heading' => 'What you get from the call']) ?>

<?= BlockMarkup::block(Testimonials::class) ?>

<?= BlockMarkup::block(Faq::class, ['contact_text' => '']) ?>

<?= BlockMarkup::block('lead-form', [
    'heading' => 'Book your call',
    'button_label' => 'Request my call',
], ['align' => 'full', 'anchor' => 'contact']) ?>
