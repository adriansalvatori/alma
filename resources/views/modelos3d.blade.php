{{--
  Template Name: Modelos 3d
--}}

@extends('layouts.app')

@section('content')
  @if(!is_user_logged_in())
  <div class="hero">
    <div class="hero-body has-padding-top-140">
      <div class="container">
        <h2 class="title">Configuracion de modelos 3d</h2>
        <div class="column">
          <h3 class="title is-4">Modelo 3d</h3>
        </div>
        <form action="modelos_3d" id="models3dform" enctype="multipart/form-data">
          <div class="column">
            <label for="">Nombre del modelo - <i>El nombre no debe incluir: espacios, mayusculas ni caracteres especiales</i></label>
            <input type="text" name="folder_name" class="input" required>
          </div>
          <div class="column">
            <input type="file" name="modelo_3d_archivo" id="" required>
          </div>
          <div id="configuraciones">
            @include('forms.model-config')
          </div>
          <div class="column">
            <input type="submit" class="button" value="Guardar">
          </div>
        </form>
        <h2 class="title has-margin-top-60">Modelos creados</h2>
        {{App\get_models(false)}}
        
      </div>
    </div>
  </div>
  @else

  @endif
@endsection
