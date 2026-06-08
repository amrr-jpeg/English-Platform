@php
    use App\Http\Controllers\Admin\LessonAdminController;

    $isEdit = $exercise !== null;

    $type = old('type', $exercise->type ?? 'choice');
    $question = old('question', $exercise->question ?? '');
    $order = old('order', $exercise->order ?? ($lesson->exercises->count() + 1));
    $xp = old('xp_reward', $exercise->xp_reward ?? 10);
    $coins = old('coin_reward', $exercise->coin_reward ?? 3);
    $answer = old('answer', $exercise->answer ?? '');

    $optionsText = old('options_text', $isEdit ? LessonAdminController::exerciseToFormText($exercise, 'options') : '');
    $chipsText = old('chips_text', $isEdit ? LessonAdminController::exerciseToFormText($exercise, 'chips') : '');
    $pairsText = old('pairs_text', $isEdit ? LessonAdminController::exerciseToFormText($exercise, 'pairs') : '');
    $cardsText = old('cards_text', $isEdit ? LessonAdminController::exerciseToFormText($exercise, 'cards') : '');
    $listeningText = old('listening_text', $isEdit ? LessonAdminController::exerciseToFormText($exercise, 'listening_text') : '');
@endphp

<form method="POST" action="{{ $action }}" class="lessonBuilder visualExerciseForm" data-exercise-form>
    @csrf

    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="adminFormGrid">
        <div>
            <label class="label">Тип упражнения</label>
            <select class="input" name="type" data-exercise-type required>
                <option value="choice" {{ $type === 'choice' ? 'selected' : '' }}>Выбор ответа</option>
                <option value="input" {{ $type === 'input' ? 'selected' : '' }}>Ввод ответа</option>
                <option value="drag_word" {{ $type === 'drag_word' ? 'selected' : '' }}>Drag & Drop: слово</option>
                <option value="drag_sentence" {{ $type === 'drag_sentence' ? 'selected' : '' }}>Drag & Drop: предложение</option>
                <option value="flashcards" {{ $type === 'flashcards' ? 'selected' : '' }}>Карточки</option>
                <option value="pairs" {{ $type === 'pairs' ? 'selected' : '' }}>Пары</option>
                <option value="syllables" {{ $type === 'syllables' ? 'selected' : '' }}>Слоги</option>
                <option value="scramble" {{ $type === 'scramble' ? 'selected' : '' }}>Scramble</option>
                <option value="listening" {{ $type === 'listening' ? 'selected' : '' }}>Аудирование</option>
            </select>
        </div>

        <div>
            <label class="label">Порядок</label>
            <input class="input" type="number" name="order" value="{{ $order }}" min="1" required>
        </div>

        <div class="adminFormGrid__wide">
            <label class="label">Вопрос / инструкция</label>
            <input class="input" name="question" value="{{ $question }}" placeholder="Например: Собери слово apple" required>
        </div>

        <div>
            <label class="label">XP</label>
            <input class="input" type="number" name="xp_reward" value="{{ $xp }}" min="0" required>
        </div>

        <div>
            <label class="label">Монеты</label>
            <input class="input" type="number" name="coin_reward" value="{{ $coins }}" min="0" required>
        </div>

        <div class="adminFormGrid__wide" data-type-field data-types="choice input scramble drag_word drag_sentence syllables listening">
            <label class="label">Правильный ответ</label>
            <input class="input" name="answer" value="{{ $answer }}" placeholder="Например: apple или I like English">
        </div>


        <div class="adminFormGrid__wide" data-type-field data-types="listening">
            <label class="label">Текст для озвучивания</label>
            <input
                class="input"
                name="listening_text"
                value="{{ $listeningText }}"
                placeholder="Например: I have a cat"
            >
            <div class="muted small">Этот текст будет произнесён браузером. Ученик его не видит, только слушает.</div>
        </div>

        <div class="adminFormGrid__wide" data-type-field data-types="choice listening">
            <label class="label">Варианты ответа</label>
            <textarea class="input" name="options_text" rows="5" placeholder="Apple&#10;Book&#10;Dog&#10;Cat">{{ $optionsText }}</textarea>
            <div class="muted small">Каждый вариант с новой строки. Для аудирования варианты можно не указывать — тогда будет поле ввода.</div>
        </div>

        <div class="adminFormGrid__wide" data-type-field data-types="drag_word drag_sentence syllables scramble">
            <label class="label">Элементы для перетаскивания</label>
            <textarea class="input" name="chips_text" rows="4" placeholder="a p p l e или I like English">{{ $chipsText }}</textarea>
            <div class="muted small">Можно через пробел, запятую или новую строку.</div>
        </div>

        <div class="adminFormGrid__wide" data-type-field data-types="pairs">
            <label class="label">Пары</label>
            <textarea class="input" name="pairs_text" rows="6" placeholder="Cat=Кот&#10;Dog=Собака">{{ $pairsText }}</textarea>
        </div>

        <div class="adminFormGrid__wide" data-type-field data-types="flashcards">
            <label class="label">Карточки</label>
            <textarea class="input" name="cards_text" rows="7" placeholder="Cat=Кот&#10;Dog=Собака">{{ $cardsText }}</textarea>
        </div>

        <div class="adminFormGrid__wide">
            <div class="miniPreview" data-live-preview></div>
        </div>
    </div>

    <button class="btn" type="submit">{{ $buttonText }}</button>
</form>