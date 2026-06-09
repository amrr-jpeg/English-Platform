@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Контент-менеджеры</h1>
        <p class="muted">Подписывайся на авторов и проходи их курсы.</p>
    </div>
    <a class="btn btn--ghost" href="{{ route('content.subscriptions') }}">Мои подписки</a>
</div>

@if(session('success')) <div class="toast toast--ok">{{ session('success') }}</div> @endif

<div class="grid">
    @forelse($managers as $manager)
        <article class="card lessonCard">
            <span class="badge">{{ $manager->published_courses_count }} курсов</span>
            <h2>{{ $manager->name }}</h2>
            <p class="muted">{{ $manager->email }}</p>
            <p class="muted small">Подписчиков: {{ $manager->content_manager_followers_count }}</p>
            <div class="row">
                <a class="btn" href="{{ route('content.managers.show', $manager) }}">Открыть</a>
                @if(in_array($manager->id, $subscriptions, true))
                    <form method="POST" action="{{ route('content.managers.unsubscribe', $manager) }}">@csrf @method('DELETE')<button class="btn btn--ghost">Отписаться</button></form>
                @else
                    <form method="POST" action="{{ route('content.managers.subscribe', $manager) }}">@csrf <button class="btn btn--ghost">Подписаться</button></form>
                @endif
            </div>
        </article>
    @empty
        <div class="emptyState card"><h2>Контент-менеджеров пока нет</h2></div>
    @endforelse
</div>
@endsection
