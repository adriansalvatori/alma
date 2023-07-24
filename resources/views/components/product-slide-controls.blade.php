<product-controls x-data style="cursor:pointer">
    <template x-for="(control, index) in gallery" :key="index">
        <span data-cursor-text=" " data-cursor="-hidden" x-on:click="active_thumbnail=index" class="icon is-small has-text-grey" style="margin-left:2px;" :class="{'is-filled' : active_thumbnail === index }">
            <i data-feather="circle" style="stroke-width: 2.5"></i>
        </span>
    </template>
</product-controls>
