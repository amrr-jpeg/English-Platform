@extends('layouts.kids')

@php
    $isEdit = $mode === 'edit';
    $oldExercises = old('exercises');
    $rows = is_array($oldExercises) ? $oldExercises : $exerciseRows;
@endphp

@section('content')
<a class="link" href="{{ route('admin.lessons') }}">← К списку уроков</a>

<div class="adminPageTop">
    <div>
        <h1 class="h1">{{ $isEdit ? 'Редактировать урок' : 'Создать урок' }}</h1>
        <p class="muted">Новая логика: сначала заполняется урок, ниже добавляются упражнения без ручного JSON.</p>
    </div>
</div>

@if(session('success'))
    <div class="toast toast--ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="toast toast--bad">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="toast toast--bad">
        <b>Исправь ошибки:</b>
        <ul class="adminErrorList">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $isEdit ? route('admin.lessons.update', $lesson) : route('admin.lessons.store') }}" class="lessonBuilder">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <section class="card builderSection">
        <div class="builderSection__head">
            <div>
                <div class="badge">1 шаг</div>
                <h2 class="h2">Основная информация</h2>
            </div>
        </div>

        <div class="adminFormGrid">
            <div>
                <label class="label">Порядок урока</label>
                <input class="input" type="number" name="order" min="1" value="{{ old('order', $lesson->order) }}" required>
            </div>

            <div>
                <label class="label">Уровень</label>
                <select class="input" name="level" required>
                    @foreach(['A1','A2','B1','B2'] as $level)
                        <option value="{{ $level }}" @selected(old('level', $lesson->level ?? 'A1') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>

            <div class="adminFormGrid__wide">
                <label class="label">Название урока</label>
                <input class="input" name="title" value="{{ old('title', $lesson->title) }}" placeholder="Например: Animals and pets" required>
            </div>

            <div>
                <label class="label">Тема / категория</label>
                <input class="input" name="category" value="{{ old('category', $lesson->category) }}" placeholder="Vocabulary, Grammar, Speaking...">
            </div>

            <div>
                <label class="label">Публикация</label>
                <label class="adminSwitch">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $lesson->is_active ?? true))>
                    <span>Урок активен</span>
                </label>
            </div>

            <div class="adminFormGrid__wide">
                <label class="label">Краткое описание</label>
                <textarea class="input" name="description" rows="3" placeholder="Что ученик изучит в этом уроке?">{{ old('description', $lesson->description) }}</textarea>
            </div>

            <div class="adminFormGrid__wide">
                <label class="label">Теория урока</label>
                <textarea class="input adminTextareaLarge" name="theory" rows="7" placeholder="Объяснение темы, правила, примеры. Это увидит ученик перед упражнениями.">{{ old('theory', $lesson->theory) }}</textarea>
            </div>
        </div>
    </section>

    <section class="card builderSection">
        <div class="builderSection__head">
            <div>
                <div class="badge">2 шаг</div>
                <h2 class="h2">Упражнения</h2>
                <p class="muted small">Добавляй задания в нужном порядке. Поля меняются в зависимости от типа.</p>
            </div>
            <button class="btn" type="button" id="addExerciseBtn">+ Добавить упражнение</button>
        </div>

        <div id="exerciseBuilder" class="exerciseBuilder"></div>
    </section>

    <div class="builderSaveBar card">
        <div>
            <b>{{ $isEdit ? 'Сохранить изменения?' : 'Готово к созданию?' }}</b>
            <div class="muted small">После сохранения урок появится в списке. Если он активен, ученики увидят его на главной.</div>
        </div>
        <button class="btn" type="submit">{{ $isEdit ? 'Сохранить урок' : 'Создать урок' }}</button>
    </div>
</form>

<template id="exerciseTemplate">
    <article class="exerciseEditor card" data-exercise-card>
        <div class="exerciseEditor__top">
            <div>
                <div class="badge">Задание <span data-number></span></div>
                <h3 class="exerciseEditor__title" data-title>Новое упражнение</h3>
            </div>
            <div class="exerciseEditor__actions">
                <button class="btn btn--ghost" type="button" data-move-up>↑</button>
                <button class="btn btn--ghost" type="button" data-move-down>↓</button>
                <button class="btn btn--ghost" type="button" data-remove>Удалить</button>
            </div>
        </div>

        <div class="adminFormGrid">
            <div>
                <label class="label">Тип упражнения</label>
                <select class="input" data-field="type">
                    <option value="choice">Выбор ответа</option>
                    <option value="input">Ввод ответа</option>
                    <option value="scramble">Собрать слово</option>
                    <option value="pairs">Соединить пары</option>
                    <option value="syllables">Собрать слово из слогов</option>
                </select>
            </div>

            <div>
                <label class="label">Порядок</label>
                <input class="input" type="number" min="1" data-field="order">
            </div>

            <div class="adminFormGrid__wide">
                <label class="label">Вопрос</label>
                <input class="input" data-field="question" placeholder="Например: Choose the correct translation for cat">
            </div>

            <div data-common-answer>
                <label class="label">Правильный ответ</label>
                <input class="input" data-field="answer" placeholder="Например: cat">
            </div>

            <div>
                <label class="label">XP</label>
                <input class="input" type="number" min="0" data-field="xp_reward" value="10">
            </div>

            <div>
                <label class="label">Монеты</label>
                <input class="input" type="number" min="0" data-field="coin_reward" value="3">
            </div>

            <div class="adminFormGrid__wide typeBlock" data-block="choice">
                <label class="label">Варианты ответа</label>
                <textarea class="input" rows="4" data-field="options_text" placeholder="Каждый вариант с новой строки&#10;cat&#10;dog&#10;bird"></textarea>
                <div class="muted small">Правильный ответ должен совпадать с одним из вариантов.</div>
            </div>

            <div class="adminFormGrid__wide typeBlock" data-block="scramble">
                <label class="label">Буквы для перемешивания</label>
                <input class="input" data-field="letters_text" placeholder="Можно оставить пустым: буквы возьмутся из правильного ответа">
                <div class="muted small">Пример: c a t или C,A,T.</div>
            </div>

            <div class="adminFormGrid__wide typeBlock" data-block="pairs">
                <label class="label">Пары</label>
                <textarea class="input" rows="5" data-field="pairs_text" placeholder="Каждая пара с новой строки:&#10;cat=кот&#10;dog=собака&#10;bird=птица"></textarea>
                <div class="muted small">Ученику будут показаны слова слева и перемешанные варианты перевода справа.</div>
            </div>

            <div class="adminFormGrid__wide typeBlock" data-block="syllables">
                <label class="label">Слоги</label>
                <input class="input" data-field="syllables_text" placeholder="hel lo или po ta to">
                <div class="muted small">Итоговое слово укажи в поле «Правильный ответ».</div>
            </div>
        </div>
    </article>
</template>

<script>
const initialExercises = @json($rows, JSON_UNESCAPED_UNICODE);
const builder = document.getElementById('exerciseBuilder');
const template = document.getElementById('exerciseTemplate');
const addBtn = document.getElementById('addExerciseBtn');

function defaultExercise() {
    return {
        type: 'choice',
        question: '',
        answer: '',
        options_text: '',
        letters_text: '',
        pairs_text: '',
        syllables_text: '',
        xp_reward: 10,
        coin_reward: 3,
        order: builder.children.length + 1,
    };
}

function addExercise(data = {}) {
    const row = {...defaultExercise(), ...data};
    const node = template.content.firstElementChild.cloneNode(true);
    builder.appendChild(node);

    node.querySelectorAll('[data-field]').forEach(field => {
        const key = field.dataset.field;
        field.value = row[key] ?? '';
    });

    node.querySelector('[data-remove]').addEventListener('click', () => {
        node.remove();
        renumber();
    });

    node.querySelector('[data-move-up]').addEventListener('click', () => {
        const prev = node.previousElementSibling;
        if (prev) builder.insertBefore(node, prev);
        renumber();
    });

    node.querySelector('[data-move-down]').addEventListener('click', () => {
        const next = node.nextElementSibling;
        if (next) builder.insertBefore(next, node);
        renumber();
    });

    node.querySelector('[data-field="type"]').addEventListener('change', () => refreshType(node));
    node.querySelector('[data-field="question"]').addEventListener('input', () => refreshTitle(node));

    refreshType(node);
    refreshTitle(node);
    renumber();
}

function refreshTitle(card) {
    const question = card.querySelector('[data-field="question"]').value.trim();
    card.querySelector('[data-title]').textContent = question || 'Новое упражнение';
}

function refreshType(card) {
    const type = card.querySelector('[data-field="type"]').value;
    card.querySelectorAll('.typeBlock').forEach(block => {
        block.style.display = block.dataset.block === type ? '' : 'none';
    });

    const answer = card.querySelector('[data-common-answer]');
    answer.style.display = type === 'pairs' ? 'none' : '';
}

function renumber() {
    Array.from(builder.children).forEach((card, index) => {
        card.querySelector('[data-number]').textContent = index + 1;
        card.querySelectorAll('[data-field]').forEach(field => {
            const key = field.dataset.field;
            field.name = `exercises[${index}][${key}]`;
        });
        card.querySelector('[data-field="order"]').value = index + 1;
    });

    if (builder.children.length === 0) {
        builder.innerHTML = '<div class="emptyState card"><div class="emptyState__icon">🧩</div><h2 class="h2">Упражнений пока нет</h2><p class="muted">Нажми «Добавить упражнение», чтобы создать первое задание.</p></div>';
    } else {
        const empty = builder.querySelector('.emptyState');
        if (empty) empty.remove();
    }
}

addBtn.addEventListener('click', () => addExercise());

if (initialExercises.length > 0) {
    initialExercises.forEach(row => addExercise(row));
} else {
    addExercise({
        type: 'choice',
        question: 'Choose the correct translation',
        options_text: 'cat\ndog\nbird',
        answer: 'cat',
    });
}
</script>
@endsection
