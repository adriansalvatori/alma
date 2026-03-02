{{--
{
    "name": "alma/login-form",
    "title": "Login Form",
    "description": "Native login form for Alma.",
    "category": "alma",
    "icon": "lock",
    "supports": {
        "align": true,
        "multiple": false
    },
    "attributes": {}
}
--}}

<div class="alma-block-login-form {{ $block->classes ?? '' }}">
    <livewire:auth.login-form :attributes="$attributes" />
</div>
