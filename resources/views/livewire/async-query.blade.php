<div>
    <div class="title">Posts</div>
    <pre>
        {{print_r($posts)}}
    </pre>
    <div class="title">Posts external links</div>
    <pre>
        {{print_r($posts_links)}}
    </pre>
    <div class="button is-primary" wire:click="getPosts">get posts</div>
    <div class="button is-dark" wire:click="getPostsLinks">get posts external links</div>
</div>
