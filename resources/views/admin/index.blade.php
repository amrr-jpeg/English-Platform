@extends('layouts.kids')

@section('content')
<section class="adminHero card">
    <div>
        <div class="badge">Панель управления</div>
        <h1 class="h1">Админ-панель English Platform</h1>
        <p class="muted">Управляй уроками, пользователями, прогрессом и аналитикой платформы.</p>
    </div>

    <div class="adminHero__actions">
        <a class="btn" href="{{ route('admin.lessons.create') }}">+ Новый урок</a>
        <a class="btn btn--ghost" href="{{ route('admin.lessons') }}">Все уроки</a>
        <a class="btn btn--ghost" href="{{ route('dashboard') }}">На сайт</a>
    </div>
</section>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<div class="adminGrid">
    <a class="adminTile card" href="{{ route('admin.lessons') }}">
        <div class="adminTile__icon">📚</div>
        <div>
            <h2 class="h2">Конструктор уроков</h2>
            <p class="muted small">Создание урока, теории и всех упражнений на одной странице.</p>
        </div>
    </a>

    <a class="adminTile card" href="{{ route('admin.users') }}">
        <div class="adminTile__icon">👥</div>
        <div>
            <h2 class="h2">Пользователи</h2>
            <p class="muted small">Просмотр учеников, прогресса, блокировка и выдача прав администратора.</p>
        </div>
    </a>

    <a class="adminTile card" href="{{ route('admin.stats') }}">
        <div class="adminTile__icon">📊</div>
        <div>
            <h2 class="h2">Статистика</h2>
            <p class="muted small">Общая аналитика по платформе, активности и качеству прохождения.</p>
        </div>
    </a>

    <div class="adminTile card">
        <div class="adminTile__icon">🧩</div>
        <div>
            <h2 class="h2">Логика обучения</h2>
            <p class="muted small">Уроки открываются последовательно, экзамены берут задания из базы, игры используют изученные слова.</p>
        </div>
    </div>
</div>
@endsection
