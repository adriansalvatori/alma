<form role="search" method="get" class="search-form flex items-center space-x-2" action="{{ home_url('/') }}">
  <label class="sr-only">
    {{ _x('Search for:', 'label', 'sage') }}
  </label>

  <input
    type="search"
    placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'sage') !!}"
    value="{{ get_search_query() }}"
    name="s"
    class="border px-2 py-1 rounded-md shadow-sm"
  >

  <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded-md">
    {{ _x('Search', 'submit button', 'sage') }}
  </button>
</form>

