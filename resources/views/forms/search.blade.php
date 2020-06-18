<form role="search" method="get" class="form field has-addons" action="{{ home_url('/') }}">
  <div class="control">
    <input type="search" class="search input" placeholder="{!! esc_attr_x('Busca algo &hellip;', 'placeholder', 'sage') !!}" value="{{ get_search_query() }}" name="s">
  </div>
  <div class="control">
    <button class="button has-text-primary" type="submit"> <i data-feather="search"></i></button>
  </div>
</form>
