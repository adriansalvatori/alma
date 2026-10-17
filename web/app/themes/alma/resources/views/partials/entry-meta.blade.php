{{-- Entry meta: publication date, author and estimated reading time. --}}
<p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
  <time class="dt-published" datetime="{{ get_post_time('c', true) }}">{{ get_the_date() }}</time>
  <span aria-hidden="true">·</span>
  <span>
    {{ __('By', 'alma') }}
    <a href="{{ get_author_posts_url(get_the_author_meta('ID')) }}" class="p-author h-card font-medium text-zinc-700 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-white">{{ get_the_author() }}</a>
  </span>
  <span aria-hidden="true">·</span>
  <span>{{ sprintf(_n('%d min read', '%d min read', $minutes = \App\Support\ReadingTime::minutes((string) get_post_field('post_content')), 'alma'), $minutes) }}</span>
</p>
