<div class="tags has-addons">
  <span class="tag is-dark">{{ __('Posted on', 'alma') }}</span>
  <span class="tag is-info">{{ get_the_date() }}</span>
</div>

<div class="tags has-addons">
  <span class="tag is-dark">{{ __('By', 'alma') }}</span>
  <a class="tag is-info" href="{{ get_author_posts_url(get_the_author_meta('ID')) }}">{{ get_the_author() }}</a>
</div>

