@php
    /**
     * Display single product reviews (comments)
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.blade.php.
     */

    defined('ABSPATH') || exit();

    global $product;

    if (!comments_open()) {
        return;
    }
@endphp

<div id="reviews" class="woocommerce-Reviews w-full">
    <div id="comments" class="mb-10">
        <h2 class="woocommerce-Reviews-title text-2xl font-bold text-zinc-900 dark:text-white mb-6">
            @php
                $count = $product->get_review_count();
                if ($count && wc_review_ratings_enabled()) {
                    /* translators: 1: reviews count 2: product name */
                    $reviews_title = sprintf(
                        esc_html_nx(
                            '%1$s review for %2$s',
                            '%1$s reviews for %2$s',
                            $count,
                            'reviews title',
                            'woocommerce',
                        ),
                        esc_html($count),
                        '<span>' . get_the_title() . '</span>',
                    );
                    echo apply_filters('woocommerce_reviews_title', $reviews_title, $count, $product); // WPCS: XSS ok.
                } else {
                    esc_html_e('Reviews', 'woocommerce');
                }
            @endphp
        </h2>

        @if (have_comments())
            <ol class="commentlist space-y-6 list-none pl-0">
                @php wp_list_comments(apply_filters('woocommerce_product_review_list_args', ['callback' => 'woocommerce_comments'])); @endphp
            </ol>

            @if (get_comment_pages_count() > 1 && get_option('page_comments'))
                <nav class="woocommerce-pagination mt-6">
                    @php
                        paginate_comments_links(
                            apply_filters('woocommerce_comment_pagination_args', [
                                'prev_text' => '&larr;',
                                'next_text' => '&rarr;',
                                'type' => 'list',
                            ]),
                        );
                    @endphp
                </nav>
            @endif
        @else
            <p class="woocommerce-noreviews text-zinc-500 dark:text-zinc-400 italic">
                {{ esc_html__('There are no reviews yet.', 'woocommerce') }}
            </p>
        @endif
    </div>

    @if (get_option('woocommerce_review_rating_verification_required') === 'no' ||
            wc_customer_bought_product('', get_current_user_id(), $product->get_id()))
        <div id="review_form_wrapper"
            class="bg-zinc-50 dark:bg-zinc-900/50 p-6 rounded-2xl border border-zinc-100 dark:border-zinc-800">
            <div id="review_form">
                @php
                    $commenter = wp_get_current_commenter();
                    $comment_form = [
                        /* translators: %s is product title */
                        'title_reply' =>
                            '<span class="text-xl font-bold text-zinc-900 dark:text-white block mb-2">' .
                            (have_comments()
                                ? esc_html__('Add a review', 'woocommerce')
                                : sprintf(
                                    esc_html__('Be the first to review &ldquo;%s&rdquo;', 'woocommerce'),
                                    get_the_title(),
                                )) .
                            '</span>',
                        /* translators: %s is product title */
                        'title_reply_to' => esc_html__('Leave a Reply to %s', 'woocommerce'),
                        'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
                        'title_reply_after' => '</h3>',
                        'comment_notes_after' => '',
                        'label_submit' => esc_html__('Submit Review', 'woocommerce'),
                        'logged_in_as' => '',
                        'comment_field' => '',
                        // Style submit button with Tailwind & Flux aesthetics manually by intercepting the CSS class
                        'class_submit' =>
                            'submit mt-4 inline-flex justify-center items-center gap-2 px-6 py-3 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-800 dark:hover:bg-zinc-100 transition-colors shadow-sm w-full sm:w-auto',
                    ];

                    $name_email_required = (bool) get_option('require_name_email', 1);
                    $fields = [
                        'author' => [
                            'label' => __('Name', 'woocommerce'),
                            'type' => 'text',
                            'value' => $commenter['comment_author'],
                            'required' => $name_email_required,
                        ],
                        'email' => [
                            'label' => __('Email', 'woocommerce'),
                            'type' => 'email',
                            'value' => $commenter['comment_author_email'],
                            'required' => $name_email_required,
                        ],
                    ];

                    $comment_form['fields'] = [];

                    foreach ($fields as $key => $field) {
                        $field_html = '<div class="comment-form-' . esc_attr($key) . ' mb-4">';
                        $field_html .=
                            '<label for="' .
                            esc_attr($key) .
                            '" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">' .
                            esc_html($field['label']);
                        if ($field['required']) {
                            $field_html .= '&nbsp;<span class="required text-primary">*</span>';
                        }
                        $field_html .= '</label>';
                        $field_html .=
                            '<input id="' .
                            esc_attr($key) .
                            '" name="' .
                            esc_attr($key) .
                            '" type="' .
                            esc_attr($field['type']) .
                            '" value="' .
                            esc_attr($field['value']) .
                            '" size="30" ' .
                            ($field['required'] ? 'required' : '') .
                            ' class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm" />';
                        $field_html .= '</div>';
                        $comment_form['fields'][$key] = $field_html;
                    }

                    $account_page_url = wc_get_page_permalink('myaccount');
                    if ($account_page_url) {
                        /* translators: %s opening and closing link tags respectively */
                        $comment_form['must_log_in'] =
                            '<p class="must-log-in text-sm text-zinc-500 mb-4">' .
                            sprintf(
                                esc_html__('You must be %1$slogged in%2$s to post a review.', 'woocommerce'),
                                '<a href="' . esc_url($account_page_url) . '" class="text-primary hover:underline">',
                                '</a>',
                            ) .
                            '</p>';
                    }

                    if (wc_review_ratings_enabled()) {
                        $comment_form['comment_field'] =
                            '<div class="comment-form-rating mb-4"><label for="rating" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">' .
                            esc_html__('Your rating', 'woocommerce') .
                            (wc_review_ratings_required() ? '&nbsp;<span class="required text-primary">*</span>' : '') .
                            '</label><select name="rating" id="rating" required class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"><option value="">' .
                            esc_html__('Rate&hellip;', 'woocommerce') .
                            '</option><option value="5">' .
                            esc_html__('Perfect', 'woocommerce') .
                            '</option><option value="4">' .
                            esc_html__('Good', 'woocommerce') .
                            '</option><option value="3">' .
                            esc_html__('Average', 'woocommerce') .
                            '</option><option value="2">' .
                            esc_html__('Not that bad', 'woocommerce') .
                            '</option><option value="1">' .
                            esc_html__('Very poor', 'woocommerce') .
                            '</option></select></div>';
                    }

                    $comment_form['comment_field'] .=
                        '<div class="comment-form-comment mb-4"><label for="comment" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">' .
                        esc_html__('Your review', 'woocommerce') .
                        '&nbsp;<span class="required text-primary">*</span></label><textarea id="comment" name="comment" cols="45" rows="5" required class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></textarea></div>';

                    comment_form(apply_filters('woocommerce_product_review_comment_form_args', $comment_form));
                @endphp
            </div>
        </div>
    @else
        <p
            class="woocommerce-verification-required text-sm text-zinc-500 dark:text-zinc-400 p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg">
            {{ esc_html__('Only logged in customers who have purchased this product may leave a review.', 'woocommerce') }}
        </p>
    @endif
</div>
