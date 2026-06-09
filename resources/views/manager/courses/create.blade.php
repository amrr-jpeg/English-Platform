@extends('layouts.kids')

@section('content')
<div class="adminPageTop">
    <div>
        <h1 class="h1">Создать курс</h1>
        <p class="muted">Создай курс, а потом добавь в него уроки и упражнения.</p>
    </div>
    <a class="btn btn--ghost" href="{{ route('manager.courses.index') }}">Назад</a>
</div>

@if($errors->any())
    <div class="toast toast--bad">Проверь поля формы.</div>
@endif

<form class="card stack" method="POST" action="{{ route('manager.courses.store') }}">
    @csrf

    <div>
        <label class="label">Название курса</label>
        <input class="input" name="title" value="{{ old('title') }}" required placeholder="Например: Английский для путешествий">
        @error('title') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="label">Описание</label>
        <textarea class="input" name="description" placeholder="Что ученик изучит в этом курсе?">{{ old('description') }}</textarea>
        @error('description') <div class="form-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="label">Сложность</label>
        <select name="level" class="input">
            <option value="easy" @selected(old('level') === 'easy')>Лёгкий</option>
            <option value="medium" @selected(old('level') === 'medium')>Средний</option>
            <option value="hard" @selected(old('level') === 'hard')>Сложный</option>
        </select>
    </div>

    <label class="check">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published'))>
        Опубликовать курс сразу
    </label>

    <button class="btn" type="submit">Создать курс</button>
</form>
@endsection
