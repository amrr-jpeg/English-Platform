@extends('layouts.kids')

@section('content')
<div class="travelPage">
<a class="link" href="{{ route('travel.index') }}">← Назад к Travel English</a>

<div class="hero">
    <div>
        <h1 class="h1">Симулятор путешествия</h1>
        <p class="muted">Тренируй короткие фразы для аэропорта, отеля, ресторана и такси. За завершение сценария начисляется награда.</p>
    </div>
</div>

<div class="card travelChatCard">
    <div class="travelChat" id="travelChat">
        @foreach($messages as $message)
            <div class="travelMessage travelMessage--{{ $message['role'] }}">
                {{ $message['content'] }}
            </div>
        @endforeach
    </div>

    <form class="travelChatForm" id="travelChatForm">
        @csrf
        <input class="input" type="text" id="travelMessageInput" placeholder="Напиши фразу на английском..." required autocomplete="off">
        <button class="btn" type="submit">Отправить</button>
        <button class="btn btn--ghost" type="button" id="travelResetBtn">Сбросить</button>
    </form>
</div>

<script>
const chat = document.getElementById('travelChat');
const form = document.getElementById('travelChatForm');
const input = document.getElementById('travelMessageInput');
const resetBtn = document.getElementById('travelResetBtn');

function addMessage(role, text) {
    const div = document.createElement('div');
    div.className = 'travelMessage travelMessage--' + role;
    div.textContent = text;
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    addMessage('user', text);
    input.value = '';

    const response = await fetch('{{ route('travel.scenario.chat') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ message: text })
    });

    const data = await response.json();
    addMessage('assistant', data.answer || 'Попробуй ещё раз.');

    if (data.reward) {
        addMessage('assistant', '🎁 ' + data.reward);
    }
});

resetBtn.addEventListener('click', async () => {
    const response = await fetch('{{ route('travel.scenario.reset') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    const data = await response.json();
    chat.innerHTML = '';
    data.messages.forEach((message) => addMessage(message.role, message.content));
});
</script>
</div>
@endsection
