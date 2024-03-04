<div>
  <div class="columns is-centered">
    <div class="column is-6">
      <div class="title">Posts</div>
      <pre>
        {{ print_r($posts) }}
    </pre>
      <div class="title">Posts external links</div>
      <pre>
        {{ print_r($posts_links) }}
    </pre>
      <div class="button is-primary" wire:click="getPosts">get posts</div>
      <div class="button is-dark" wire:click="getPostsLinks">get posts external links</div>

      <hr>


    <div class="title">
        <span>{{$username}}</span>
        <span>{{$password}}</span>
        <span>{{$email}}</span>
        <span>{{$user_id}}</span>
    </div>
    <hr>

    <form class="user-register-form" wire:submit="registerUser">
        <input placeholder="Username" wire:model="username" type="text" name="username" class="input" id="username">
        <input placeholder="Email" wire:model="email" type="text" name="email" class="input" id="email">
        <input placeholder="Password" wire:model="password" type="password" name="password" class="input" id="password">
        <button class="button is-primary" type="submit">Register</button>
    </form>
        
    </div>
  </div>

</div>
