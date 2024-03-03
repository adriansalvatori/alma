<?php

namespace App\Livewire;

use Livewire\Component;

class AsyncQuery extends Component
{
    public $posts = [];
    public $posts_links = [];

    public function getPosts(){
        $posts = get_posts();
        if($posts){
            foreach($posts as $post){
                $this->posts[] = [
                    'title' => $post->post_title,
                    'link' => get_field('external_link', $post->ID),
                    'id' => $post->ID,
                    'slug' => $post->post_name,
                    'date' => $post->post_date,
                    'content' => $post->post_content,
                    'excerpt' => $post->post_excerpt,
                    'status' => $post->post_status,
                    'type' => $post->post_type,
                    'author' => $post->post_author,
                    'modified' => $post->post_modified,
                    'modified_gmt' => $post->post_modified_gmt,
                    'comment_status' => $post->comment_status,
                    'comment_count' => $post->comment_count,
                    'thumbnail' => get_the_post_thumbnail($post->ID, 'thumbnail')
                ];
            }
        }
    }

    public function getPostsLinks(){
        $posts = get_posts();
        if($posts){
            foreach($posts as $post){
                $this->posts_links[] = get_field('external_link', $post->ID);
            }
        }
    }

    public function render()
    {
        return view('livewire.async-query');
    }
}
