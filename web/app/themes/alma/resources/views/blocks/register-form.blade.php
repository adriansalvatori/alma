{{--
{
    "name": "alma/register-form",
    "title": "Register Form",
    "description": "Native registration form for Alma.",
    "category": "alma",
    "icon": "admin-users",
    "supports": {
        "align": true,
        "multiple": false
    },
    "attributes": {}
}
--}}

<div class="alma-block-register-form {{ $block->classes ?? '' }}">
    <livewire:auth.register-form :attributes="$attributes" />
</div>
