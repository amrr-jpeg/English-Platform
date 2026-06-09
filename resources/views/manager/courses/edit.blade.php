@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">{{ $course->title }}</h1>
        <p class="muted">Редактирование курса, уроков и упражнений.</p>
    </div>
    <div class="row">
        <a class="btn btn--ghost" href="{{ route('manager.courses.index') }}">Мои курсы</a>
        @if($course->is_published)
            <a class="btn" href="{{ route('content.courses.show', $course) }}">Открыть курс</a>
        @endif
    </div>
</div>

@if(session('success')) <div class="toast toast--ok">{{ session('success') }}</div> @endif
@if(session('error')) <div class="toast toast--bad">{{ session('error') }}</div> @endif
@if($errors->any()) <div class="toast toast--bad">Проверь форму. Одно из полей заполнено неправильно.</div> @endif

<div class="card stack">
    <h2>Настройки курса</h2>
    <form class="stack" method="POST" action="{{ route('manager.courses.update', $course) }}">
        @csrf
        @method('PUT')

        <div>
            <label class="label">Название</label>
            <input class="input" name="title" value="{{ old('title', $course->title) }}" required>
        </div>

        <div>
            <label class="label">Описание</label>
            <textarea class="input" name="description">{{ old('description', $course->description) }}</textarea>
        </div>

        <div>
            <label class="label">Сложность</label>
            <select class="input" name="level">
                <option value="easy" @selected(old('level', $course->level) === 'easy')>Лёгкий</option>
                <option value="medium" @selected(old('level', $course->level) === 'medium')>Средний</option>
                <option value="hard" @selected(old('level', $course->level) === 'hard')>Сложный</option>
            </select>
        </div>

        <label class="check">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published))>
            Курс опубликован
        </label>

        <button class="btn" type="submit">Сохранить курс</button>
    </form>
</div>

<div class="card stack">
    <h2>Добавить урок</h2>
    <form class="stack" method="POST" action="{{ route('manager.courses.lessons.store', $course) }}">
        @csrf
        <div class="adminFormGrid">
            <div>
                <label class="label">Порядок</label>
                <input class="input" type="number" name="order" value="{{ old('order', $course->lessons->count() + 1) }}" min="1" required>
            </div>
            <div>
                <label class="label">Сложность</label>
                <select class="input" name="level">
                    <option value="easy">Лёгкий</option>
                    <option value="medium">Средний</option>
                    <option value="hard">Сложный</option>
                </select>
            </div>
        </div>
        <div>
            <label class="label">Название урока</label>
            <input class="input" name="title" required placeholder="Например: Фразы в аэропорту">
        </div>
        <div>
            <label class="label">Описание</label>
            <textarea class="input" name="description"></textarea>
        </div>
        <div>
            <label class="label">Теория</label>
            <textarea class="input" name="theory" placeholder="Текст урока, объяснения, примеры"></textarea>
        </div>
        <label class="check"><input type="checkbox" name="is_active" value="1" checked> Урок активен</label>
        <button class="btn" type="submit">Добавить урок</button>
    </form>
</div>

<div class="stack">
    @forelse($course->lessons as $lesson)
        <div class="card stack">
            <div class="exerciseAdminCard__top">
                <div>
                    <span class="badge">Урок #{{ $lesson->order }}</span>
                    <h2>{{ $lesson->title }}</h2>
                    <p class="muted">{{ $lesson->description }}</p>
                </div>
                <form method="POST" action="{{ route('manager.lessons.destroy', $lesson) }}" onsubmit="return confirm('Удалить урок?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn--danger" type="submit">Удалить урок</button>
                </form>
            </div>

            <details class="inlineEditor">
                <summary class="btn btn--ghost">Редактировать урок</summary>
                <form class="inlineEditor__body" method="POST" action="{{ route('manager.lessons.update', $lesson) }}">
                    @csrf
                    @method('PUT')
                    <div class="adminFormGrid">
                        <div><label class="label">Порядок</label><input class="input" type="number" name="order" value="{{ $lesson->order }}" required></div>
                        <div>
                            <label class="label">Сложность</label>
                            <select class="input" name="level">
                                <option value="easy" @selected($lesson->level === 'easy')>Лёгкий</option>
                                <option value="medium" @selected($lesson->level === 'medium')>Средний</option>
                                <option value="hard" @selected($lesson->level === 'hard')>Сложный</option>
                            </select>
                        </div>
                    </div>
                    <div><label class="label">Название</label><input class="input" name="title" value="{{ $lesson->title }}" required></div>
                    <div><label class="label">Описание</label><textarea class="input" name="description">{{ $lesson->description }}</textarea></div>
                    <div><label class="label">Теория</label><textarea class="input" name="theory">{{ $lesson->theory }}</textarea></div>
                    <label class="check"><input type="checkbox" name="is_active" value="1" @checked($lesson->is_active)> Урок активен</label>
                    <button class="btn" type="submit">Сохранить урок</button>
                </form>
            </details>

            <h3>Упражнения</h3>
            <div class="stack">
                @foreach($lesson->exercises as $exercise)
                    <div class="row card">
                        <div style="flex:1;">
                            <b>#{{ $exercise->order }} — {{ $exercise->question }}</b><br>
                            <span class="muted small">Тип: {{ $exercise->type }} • ответ: {{ $exercise->answer }}</span>
                        </div>
                        <form method="POST" action="{{ route('manager.exercises.destroy', $exercise) }}" onsubmit="return confirm('Удалить упражнение?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn--danger" type="submit">Удалить</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <details class="inlineEditor">
                <summary class="btn">Добавить упражнение</summary>
                <form class="inlineEditor__body" method="POST" action="{{ route('manager.lessons.exercises.store', $lesson) }}">
                    @csrf
                    <div class="adminFormGrid">
                        <div>
                            <label class="label">Тип</label>
                            <select class="input" name="type">
                                <option value="choice">Выбор ответа</option>
                                <option value="input">Ввод ответа</option>
                                <option value="scramble">Собрать слово</option>
                                <option value="drag_sentence">Собрать предложение</option>
                                <option value="listening">Аудирование</option>
                                <option value="pairs">Пары</option>
                            </select>
                        </div>
                        <div><label class="label">Порядок</label><input class="input" type="number" name="order" value="{{ $lesson->exercises->count() + 1 }}" required></div>
                        <div><label class="label">XP</label><input class="input" type="number" name="xp_reward" value="10" min="0" required></div>
                        <div><label class="label">Монеты</label><input class="input" type="number" name="coin_reward" value="2" min="0" required></div>
                    </div>
                    <div><label class="label">Вопрос</label><input class="input" name="question" required></div>
                    <div><label class="label">Правильный ответ</label><input class="input" name="answer"></div>
                    <div><label class="label">Варианты ответа для типа “Выбор ответа”, каждый с новой строки</label><textarea class="input" name="options_text"></textarea></div>
                    <div><label class="label">Пары для типа “Пары”: english=русский, каждая пара с новой строки</label><textarea class="input" name="pairs_text"></textarea></div>
                    <div><label class="label">Текст для аудирования</label><input class="input" name="listening_text"></div>
                    <button class="btn" type="submit">Добавить упражнение</button>
                </form>
            </details>
        </div>
    @empty
        <div class="emptyState card">
            <h2>Уроков пока нет</h2>
            <p class="muted">Добавь первый урок выше.</p>
        </div>
    @endforelse
</div>
@endsection
