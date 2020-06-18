
@set($ubicaciones, get_terms([
    'taxonomy' => 'barrio'
]))
@set($cats, get_terms([
    'taxonomy' => 'category'
]))
@set($types, get_terms([
    'taxonomy' => 'transaccion'
]))

<form role="search" method="get" class="form field has-addons has-addons-centered has-margin-top-50"
  action="{{ home_url('/') }}">
  <div class="control has-icons-left">
    <div class="select">
      <select data-filter-type="locs">
        <option value="">Ubicación</option>
        @foreach ($ubicaciones as $u)
        <option value="{{$u->name}}">{{$u->name}}</option>
        @endforeach
      </select>
    </div>
    <div class="icon is-small is-left">
      <i data-feather="globe"></i>
    </div>
  </div>
  <div class="control has-icons-left">
    <div class="select">
      <select data-filter-type="cats">
        <option value="">Tipo de Inmueble</option>
        @foreach ($cats as $c)
        <option value="{{$c->name}}">{{$c->name}}</option>
        @endforeach
      </select>
    </div>
    <div class="icon is-small is-left">
        <i data-feather="home"></i>
      </div>
  </div>
  <div class="control has-icons-left">
    <div class="select">
      <select data-filter-type="type">
        <option value="">Tipo de Transacción</option>
        @foreach ($types as $t)
        <option value="{{$t->name}}">{{$t->name}}</option>
        @endforeach
      </select>
    </div>
    <div class="icon is-small is-left">
        <i data-feather="file-text"></i>
      </div>
  </div>
  <div class="control">
    <input type="hidden" id="filterbox" placeholder="{!! esc_attr_x('Busca algo &hellip;', 'placeholder', 'sage') !!}"
      value="{{ get_search_query() }}" name="s">
    <button class="button is-dark" type="submit"><span>Buscar</span> <span class="icon is-medium"><i
          data-feather="search"></i></span></button>
  </div>
</form>
