<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class Hero extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('hero');

        $fields
            ->setLocation('post_type', '==', 'post');

        $fields
            ->addRepeater('items')
                ->addText('item')
                ->addText('link')
                ->addImage('image')
                ->addWysiwyg('text')
            ->endRepeater();

        return $fields->build();
    }
}
