@extends('scalar::layout')

@section('content')
    <script
        id="api-reference"
        data-url="{{ config('scalar.url') }}"
        data-spec-url="{{ config('scalar.url') }}"
        data-configuration="{{ json_encode(\Scalar\Scalar::configuration()) }}"></script>
    <script src="{{ \Scalar\Scalar::cdn() }}"></script>
@endsection
