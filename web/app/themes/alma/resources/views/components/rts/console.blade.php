@if (app()->environment('production'))
    <script>
        console.log = function () {};
        console.error = function () {};
    </script>
@endif
