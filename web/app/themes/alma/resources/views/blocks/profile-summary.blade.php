{{--
{
    "name": "alma/profile-summary",
    "title": "Profile Summary",
    "description": "A summary of the user profile.",
    "category": "alma",
    "icon": "admin-users",
    "supports": {
        "align": true,
        "multiple": true
    },
    "attributes": {}
}
--}}

<div class="alma-block-profile-summary {{ $block->classes ?? '' }}">
    <livewire:profile.profile-summary :attributes="$attributes" />
</div>
