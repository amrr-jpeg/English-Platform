@extends('layouts.kids')

@section('content')
<div class="authWrap">
    <div class="card authCard">
        <h1 class="h1">Подтверждение регистрации</h1>

        <p class="muted">
            Мы отправили 6-значный код на вашу почту. Введите его ниже.
        </p>

        @if (session('status'))
            <div class="toast toast--good">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.verify') }}" class="stack">
            @csrf

            <div>
                <label class="label" for="code">Код подтверждения</label>

                <input
                    class="input"
                    id="code"
                    type="text"
                    name="code"
                    maxlength="6"
                    required
                    autofocus
                >

                @error('code')
                    <div class="toast toast--bad">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button class="btn" type="submit">
                Подтвердить регистрацию
            </button>
        </form>

        <form method="POST" action="{{ route('register.verify.resend') }}" class="stack" style="margin-top:12px;">
            @csrf

            <button class="btn" type="submit">
                Отправить код повторно
            </button>
        </form>
    </div>
</div>
@endsection