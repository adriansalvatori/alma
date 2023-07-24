<figure x-data class="is-relative is-clipped">
    <template x-for="(image, index) in gallery" :key="index">
        <img x-show="active_thumbnail === index" x-transition class="product-image is-overlay" x-bind:src="`${image}`" alt="@title">
    </template>
</figure>