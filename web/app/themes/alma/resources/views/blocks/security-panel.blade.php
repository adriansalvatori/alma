{{--
{
    "name": "alma/security-panel",
    "title": "Security Panel",
    "description": "Security settings and status panel.",
    "category": "alma",
    "icon": "shield",
    "supports": {
        "align": true,
        "multiple": true
    },
    "attributes": {}
}
--}}

<div class="alma-block-security-panel {{ $block->classes ?? '' }}">
    <livewire:security.security-panel :attributes="$attributes" />
</div>
