<form role="search" method="get" class="search-form field is-grouped" action="{{ home_url('/') }}">
  <label class="sr-only">
    {{ _x('Search for:', 'label', 'alma') }}
  </label>

  <div class="control">
    <input
      type="search"
      placeholder="{!! esc_attr_x('Search &hellip;', 'placeholder', 'alma') !!}"
      value="{{ get_search_query() }}"
      name="s"
      class="input"
    >
  </div>

  <div class="control">
    <button type="submit" class="button is-primary">
      {{ _x('Search', 'submit button', 'alma') }}
    </button>
  </div>
</form>

