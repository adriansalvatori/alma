<time class="dt-published text-sm text-gray-600 dark:text-gray-400" datetime="{{ get_post_time('c', true) }}">
  {{ get_the_date() }}
</time>

<p class="flex items-center text-sm text-gray-600 dark:text-gray-400">
  <span class="mr-2">{{ __('By', 'sage') }}</span>
  <a href="{{ get_author_posts_url(get_the_author_meta('ID')) }}" class="p-author h-card hover:underline">
    {{ get_the_author() }}
  </a>
</p>

