@extends('layouts.kids')

@section('content')
<a class="link" href="{{ route('admin.lessons') }}">← Назад к урокам</a>

<div class="adminPageTop">
    <div>
        <h1 class="h1">Конструктор урока</h1>
        <p class="muted">Создание, редактирование и предпросмотр без ручного JSON.</p>
    </div>

    <div class="row">
        <a class="btn btn--ghost" href="{{ route('admin.lessons.preview', $lesson) }}">👁 Предпросмотр</a>
        <a class="btn btn--ghost" href="{{ route('dashboard') }}">На сайт</a>
    </div>
</div>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif

<div class="adminBuilderLayout">
    <section class="card builderSection">
        <div class="builderSection__head">
            <div>
                <h2 class="h2">Настройки урока</h2>
                <p class="muted">Эти данные сразу отображаются на сайте.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" class="lessonBuilder">
            @csrf
            @method('PUT')

            <div class="adminFormGrid">
                <div>
                    <label class="label">Название</label>
                    <input class="input" name="title" value="{{ old('title', $lesson->title) }}" required>
                </div>

                <div>
                    <label class="label">Порядок</label>
                    <input class="input" type="number" name="order" value="{{ old('order', $lesson->order) }}" min="1" required>
                </div>

                <div>
                    <label class="label">Уровень</label>
                    <select class="input" name="level" required>
                        @foreach(['A1', 'A2', 'B1', 'B2', 'easy', 'medium', 'hard'] as $level)
                            <option value="{{ $level }}" @selected(old('level', $lesson->level ?? 'A1') === $level)>
                                {{ $level }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Категория</label>
                    <input
                        class="input"
                        name="category"
                        value="{{ old('category', $lesson->category) }}"
                        placeholder="Например: Grammar или Travel English"
                    >
                </div>

                <div class="adminFormGrid__wide">
                    <label class="label">Описание</label>
                    <textarea class="input" name="description" rows="4">{{ old('description', $lesson->description) }}</textarea>
                </div>

                <div class="adminFormGrid__wide">
                    <label class="label">Теория урока</label>
                    <textarea
                        class="input adminTextareaLarge"
                        name="theory"
                        rows="7"
                        placeholder="Правила, объяснение темы, примеры для ученика"
                    >{{ old('theory', $lesson->theory) }}</textarea>
                </div>

                <div class="adminFormGrid__wide">
                    <label class="adminSwitch">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $lesson->is_active))>
                        Урок активен и виден пользователям
                    </label>
                </div>
            </div>

            <button class="btn" type="submit">Сохранить урок</button>
        </form>
    </section>

    <section class="card builderSection">
        <div class="builderSection__head">
            <div>
                <h2 class="h2">Добавить упражнение</h2>
                <p class="muted">Выбери тип — появятся только нужные поля.</p>
            </div>
        </div>

        @include('admin.exercises.partials.form', [
            'mode' => 'create',
            'action' => route('admin.exercises.store', $lesson),
            'exercise' => null,
            'method' => 'POST',
            'buttonText' => 'Добавить упражнение',
        ])
    </section>
</div>

<section class="card builderSection" style="margin-top:18px;">
    <div class="builderSection__head">
        <div>
            <h2 class="h2">Упражнения урока</h2>
            <p class="muted">Редактируй прямо здесь. JSON больше не нужен.</p>
        </div>

        <div class="badge">{{ $lesson->exercises->count() }} заданий</div>
    </div>

    <div class="exerciseTimeline">
        @forelse($lesson->exercises as $exercise)
            <article class="exerciseAdminCard">
                <div class="exerciseAdminCard__top">
                    <div>
                        <div class="badge">#{{ $exercise->order }} • {{ $exercise->type }}</div>

                        <h3>{{ $exercise->question }}</h3>

                        <p class="muted small">
                            Ответ:
                            <b>{{ $exercise->answer ?: '—' }}</b>
                        </p>
                    </div>

                    <div class="rewards">
                        <span class="pill">✨ {{ $exercise->xp_reward }}</span>
                        <span class="pill">🪙 {{ $exercise->coin_reward }}</span>
                    </div>
                </div>

                <details class="inlineEditor">
                    <summary class="btn btn--ghost">✏️ Редактировать</summary>

                    <div class="inlineEditor__body">
                        @include('admin.exercises.partials.form', [
                            'mode' => 'edit-' . $exercise->id,
                            'action' => route('admin.exercises.update', $exercise),
                            'exercise' => $exercise,
                            'method' => 'PUT',
                            'buttonText' => 'Сохранить изменения',
                        ])

                        <form
                            method="POST"
                            action="{{ route('admin.exercises.delete', $exercise) }}"
                            onsubmit="return confirm('Удалить это упражнение?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="btn btn--danger" type="submit">Удалить упражнение</button>
                        </form>
                    </div>
                </details>
            </article>
        @empty
            <div class="emptyState">
                <div class="emptyState__icon">🧠</div>
                <div class="h2">Пока нет упражнений</div>
                <div class="muted">Добавь первое упражнение через форму выше.</div>
            </div>
        @endforelse
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-exercise-form]').forEach((form) => {
        const select = form.querySelector('[data-exercise-type]');
        const fields = form.querySelectorAll('[data-type-field]');
        const preview = form.querySelector('[data-live-preview]');

        const updateFields = () => {
            const type = select.value;

            fields.forEach((field) => {
                const types = (field.dataset.types || '').split(' ');
                field.style.display = types.includes(type) ? '' : 'none';
            });

            updatePreview();
        };

        const updatePreview = () => {
            if (!preview) {
                return;
            }

            const type = select.value;
            const question = form.querySelector('[name="question"]')?.value || 'Текст задания';
            const answer = form.querySelector('[name="answer"]')?.value || 'answer';

            preview.innerHTML = `
                <div class="miniPreview__badge">${type}</div>
                <div class="miniPreview__question">${escapeHtml(question)}</div>
                <div class="miniPreview__answer">Правильный ответ: ${escapeHtml(answer)}</div>
            `;
        };

        form.addEventListener('input', updatePreview);
        select.addEventListener('change', updateFields);

        updateFields();
    });
});

function escapeHtml(text) {
    return String(text).replace(/[&<>"']/g, (m) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[m]));
}
</script>
@endsection