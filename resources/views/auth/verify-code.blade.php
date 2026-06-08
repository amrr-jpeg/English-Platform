<x-guest-layout>
    <form method="POST" action="{{ route('verification.check') }}">
        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div>
            <label>Код подтверждения</label>
            <input type="text" name="code" required>
        </div>

        @error('code')
            <p style="color:red;">{{ $message }}</p>
        @enderror

        <button type="submit">
            Подтвердить
        </button>
    </form>
</x-guest-layout>