<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class PostFields extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('post_fields');

        $fields
            ->setLocation('post_type', '==', 'post');

        $fields
           ->addPostObject('related', [
               'label' => 'Related Posts',
               'required' => false,
               'multiple' => true,
               'post_type' => 'post',
           ]);

        return $fields->build();
    }
}
