{{-- A plain data table in a rounded frame (inside a flush card it loses its own border). Put <thead>/<tbody> in the slot. --}}
<div class="alma-table-wrap">
  <table {{ $attributes->class(['alma-table']) }}>{{ $slot }}</table>
</div>
