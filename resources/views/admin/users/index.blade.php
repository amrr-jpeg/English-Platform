@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Пользователи</h1>
        <p class="muted">Администрирование учеников, администраторов и контент-менеджеров.</p>
    </div>

    <a class="btn btn--ghost" href="{{ route('admin.index') }}">Админ-панель</a>
</div>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('admin.users') }}" class="card adminSearchForm">
    <label class="label" for="q">Поиск пользователя</label>
    <div class="row">
        <input class="input" id="q" name="q" value="{{ $query }}" placeholder="Имя или email">
        <button class="btn" type="submit">Найти</button>
    </div>
</form>

<div class="card">
    <div class="tableWrap">
        <table class="adminTable">
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Роль</th>
                    <th>Уровень</th>
                    <th>Прогресс</th>
                    <th>Точность</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    @php
                        $accuracy = $user->attempts_count > 0
                            ? round(($user->correct_attempts_count / $user->attempts_count) * 100)
                            : 0;
                    @endphp
                    <tr>
                        <td>
                            <b>{{ $user->name }}</b><br>
                            <span class="muted small">{{ $user->email }}</span>
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge">админ</span>
                            @elseif($user->role === \App\Models\User::ROLE_CONTENT_MANAGER)
                                <span class="badge">контент-менеджер</span><br>
                                <span class="muted small">курсов: {{ $user->content_courses_count }} • подписчиков: {{ $user->followers_count }}</span>
                            @else
                                <span class="badge">пользователь</span>
                            @endif
                        </td>
                        <td>LVL {{ $user->level }}<br><span class="muted small">✨ {{ $user->xp }} • 🪙 {{ $user->coins }}</span></td>
                        <td>{{ $user->completed_lessons_count }} уроков<br><span class="muted small">{{ $user->attempts_count }} попыток</span></td>
                        <td>{{ $accuracy }}%</td>
                        <td>
                            @if($user->is_blocked)
                                <span class="badge badge--bad">заблокирован</span>
                            @else
                                <span class="badge badge--ok">активен</span>
                            @endif
                        </td>
                        <td>
                            <div class="row">
                                <form method="POST" action="{{ route('admin.users.toggleBlock', $user) }}">
                                    @csrf
                                    <button class="btn btn--ghost" type="submit">{{ $user->is_blocked ? 'Разблокировать' : 'Блокировать' }}</button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.toggleAdmin', $user) }}">
                                    @csrf
                                    <button class="btn btn--ghost" type="submit">{{ $user->is_admin ? 'Снять админа' : 'Сделать админом' }}</button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.toggleContentManager', $user) }}">
                                    @csrf
                                    <button class="btn btn--ghost" type="submit">
                                        {{ $user->role === \App\Models\User::ROLE_CONTENT_MANAGER ? 'Снять контент-менеджера' : 'Сделать контент-менеджером' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Пользователи не найдены.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
