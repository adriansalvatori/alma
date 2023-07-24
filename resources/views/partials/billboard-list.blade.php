@if($billboards)
<section class="billboards columns is-multiline section">
    @foreach($billboards as $billboard)
        <pre class="is-hidden">
            {{print_r($billboard)}}
        </pre>
        <x-billboard :data="$billboard" />
    @endforeach
</section>
@endif