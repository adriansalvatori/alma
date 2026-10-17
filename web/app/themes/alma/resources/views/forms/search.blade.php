{{-- Search form: a Flux input and submit button, used by get_search_form(). --}}
<form role="search" method="get" class="search-form flex gap-2" action="{{ home_url('/') }}">
  <flux:input type="search" name="s" :value="get_search_query()" :placeholder="esc_attr_x('Search …', 'placeholder', 'alma')" :aria-label="_x('Search for:', 'label', 'alma')" class="w-full" />
  <flux:button type="submit">{{ _x('Search', 'submit button', 'alma') }}</flux:button>
</form>
