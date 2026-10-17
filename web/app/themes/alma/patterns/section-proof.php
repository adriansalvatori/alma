<?php
/**
 * Title: Social proof band
 * Slug: alma/section-proof
 * Categories: alma-sections
 * Description: Client logos followed by testimonials. Insert anywhere a page needs proof.
 */

use App\Blocks\LogoCloud;
use App\Blocks\Testimonials;
use App\Support\BlockMarkup;

?>
<?= BlockMarkup::block(LogoCloud::class) ?>

<?= BlockMarkup::block(Testimonials::class) ?>
