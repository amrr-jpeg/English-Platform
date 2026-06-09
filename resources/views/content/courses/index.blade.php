@extends('layouts.kids')

@section('content')
<div class="page-content">
    <section class="hero">
        <div>
            <span class="badge">📚 Курсы</span>
            <h1>Курсы от преподавателей</h1>
            <p class="muted">Выбирай курс от контент-менеджеров и проходи уроки.</p>
        </div>
    </section>

    <div class="grid">
        @forelse($courses as $course)
            <div class="card">
                <span class="badge">👤 {{ $course->manager?->name ?? 'Автор' }}</span>

                <h2>{{ $course->title }}</h2>

                <p class="muted">
                    {{ $course->description }}
                </p>

                <a href="{{ route('content.courses.show', $course) }}" class="btn">
                    Перейти к курсу
                </a>
            </div>
        @empty
            <div class="card">
                <h2>Пока нет курсов</h2>
                <p class="muted">Контент-менеджеры ещё не добавили курсы.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection