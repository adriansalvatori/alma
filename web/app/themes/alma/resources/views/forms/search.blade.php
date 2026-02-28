<form role="search" method="get" class="search-form flex gap-2" action="{{ home_url('/') }}">
    <flux:input type="search" placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'sage') !!}" value="{{ get_search_query() }}" name="s"
        aria-label="{{ _x('Search for:', 'label', 'sage') }}" class="w-full" />

    <flux:button type="submit">{{ _x('Search', 'submit button', 'sage') }}</flux:button>
</form>
