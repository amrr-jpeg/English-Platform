@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'form-error small']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
