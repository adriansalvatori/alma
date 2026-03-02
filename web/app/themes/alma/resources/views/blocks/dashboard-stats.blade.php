{{--
{
    "name": "alma/dashboard-stats",
    "title": "Dashboard Stats",
    "description": "Dynamic Livewire dashboard statistics.",
    "category": "alma",
    "icon": "dashboard",
    "supports": {
        "align": true,
        "multiple": true,
        "jsx": true
    },
    "attributes": {}
}
--}}

@if (is_user_logged_in())
    <div class="alma-block-dashboard-stats {{ $block->classes ?? '' }}">
        <livewire:dashboard.dashboard-stats :attributes="$attributes" />
    </div>
@endif
