@extends('core/table::table')

@section('main-table-' . $id)
    <x-core::form
        :url="route('custom-fields.import')"
        method="post"
        class="import-field-group"
    >
        <input
            type="file"
            accept="application/json"
            class="d-none"
            id="import_json"
        >
        {!! $dataTable->table(compact('id', 'class'), false) !!}
    </x-core::form>
@endsection
