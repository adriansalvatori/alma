<?php
/**
 * Title: Home
 * Slug: alma/home
 * Categories: alma-pages
 * Description: The homepage: hero, logos, features, benefits, testimonials, FAQ, call to action and contact form.
 * Inserter: no
 */

use App\Blocks\BenefitsGrid;
use App\Blocks\CallToAction;
use App\Blocks\Faq;
use App\Blocks\FeaturesGrid;
use App\Blocks\Hero;
use App\Blocks\LogoCloud;
use App\Blocks\Testimonials;
use App\Support\BlockMarkup;

// Blocks render their own presets (DEFAULTS / repeaterDefaults) for anything not set here, so a
// page only lists what differs from them. Copy edits belong in wp-admin once the copy overlay
// lands; until then, this file is the page.
?>
<?= BlockMarkup::block(Hero::class) ?>

<?= BlockMarkup::block(LogoCloud::class) ?>

<?= BlockMarkup::block(FeaturesGrid::class, [], ['anchor' => 'features']) ?>

<?= BlockMarkup::block(BenefitsGrid::class) ?>

<?= BlockMarkup::block(Testimonials::class) ?>

<?= BlockMarkup::block(Faq::class) ?>

<?= BlockMarkup::block(CallToAction::class) ?>

<?= BlockMarkup::block('lead-form', [], ['align' => 'full', 'anchor' => 'contact']) ?>
