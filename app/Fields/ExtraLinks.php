<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class ExtraLinks extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $extraLinks = Builder::make('extra_links');

        $extraLinks
            ->setLocation('post_type', '==', 'post');

        $extraLinks
            ->addLink('external_link');

        return $extraLinks->build();
    }
}
