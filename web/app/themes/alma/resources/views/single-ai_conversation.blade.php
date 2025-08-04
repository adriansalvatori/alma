@extends('layouts.app')

@section('content')
    <pre>
        {{ print_r(json_decode(get_field('messages'))) }}
    </pre>
@endsection
