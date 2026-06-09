@php
    $route = Route::currentRouteName();

    $isActive = function (array $patterns) use ($route) {
        foreach ($patterns as $pattern) {
            if ($route === $pattern || str_starts_with($route ?? '', $pattern . '.')) {
                return true;
            }
        }

        return false;
    };

    $displayName = auth()->user()->name ?? 'U';
    $initial = auth()->check()
        ? (function_exists('mb_substr') ? mb_strtoupper(mb_substr($displayName, 0, 1)) : strtoupper(substr($displayName, 0, 1)))
        : '🙂';
@endphp

<header class="modernTopbar" data-modern-nav>
    <div class="modernTopbar__shell">
        <a class="brand" href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" aria-label="English Platform — главная">
            <span class="brand__logo">🎓</span>
            <span class="brand__text">
                <span class="brand__title">English Platform</span>
                <span class="brand__subtitle">учим английский играя</span>
            </span>
        </a>

        <button class="navToggle" type="button" data-nav-toggle aria-controls="primaryNav" aria-expanded="false" aria-label="Открыть меню">
            <span class="navToggle__icon"></span>
        </button>

        <nav class="modernNav" id="primaryNav" aria-label="Главная навигация">

{{-- МОИ КУРСЫ ДЛЯ КОНТЕНТ-МЕНЕДЖЕРОВ --}}
@if(auth()->check() && auth()->user()->isContentManager())
    <a href="{{ route('manager.courses.index') }}"
       class="navDirectLink {{ request()->routeIs('manager.*') ? 'active' : '' }}">
        🧩 Мои курсы
    </a>
@endif
            <div class="navGroup {{ $isActive(['dashboard', 'lessons', 'exam', 'mistakes', 'travel']) ? 'is-current' : '' }}" data-nav-group>
                <button class="navGroup__button {{ $isActive(['dashboard', 'lessons', 'exam', 'mistakes', 'travel']) ? 'active' : '' }}" type="button" data-nav-group-button aria-expanded="false">
                    <span>📚 Учёба</span>
                    <span class="navGroup__chevron">⌄</span>
                </button>

 {{-- КУРСЫ ДЛЯ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ --}}
@if(auth()->check())
    <a href="{{ route('content.courses.index') }}"
       class="navDirectLink {{ request()->routeIs('content.courses.*') ? 'active' : '' }}">
        📚 Курсы
    </a>
@endif

                <div class="navDropdown">
                    @if(Route::has('dashboard'))
                        <a href="{{ route('dashboard') }}" class="navDropdown__item mainMenu__link {{ $route === 'dashboard' || str_starts_with($route ?? '', 'lessons') ? 'active' : '' }}">
                            <span class="navDropdown__icon">📖</span>
                            <span>
                                <span class="navDropdown__title">Уроки</span>
                                <span class="navDropdown__text">темы, задания и прогресс</span>
                            </span>
                        </a>
                    @endif

                    @if(Route::has('exam.intro'))
                        <a href="{{ route('exam.intro') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'exam') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🧠</span>
                            <span>
                                <span class="navDropdown__title">Экзамен</span>
                                <span class="navDropdown__text">итоговая проверка знаний</span>
                            </span>
                        </a>
                    @endif

                    @if(Route::has('travel.index'))
                        <a href="{{ route('travel.index') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'travel') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🌍</span>
                            <span>
                                <span class="navDropdown__title">Travel English</span>
                                <span class="navDropdown__text">английский для путешествий</span>
                            </span>
                        </a>
                    @endif

                    @if(Route::has('mistakes.index'))
                        <a href="{{ route('mistakes.index') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'mistakes') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🛠️</span>
                            <span>
                                <span class="navDropdown__title">Работа над ошибками</span>
                                <span class="navDropdown__text">повтори сложные задания</span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="navGroup {{ $isActive(['games', 'chests', 'shop']) ? 'is-current' : '' }}" data-nav-group>
                <button class="navGroup__button {{ $isActive(['games', 'chests', 'shop']) ? 'active' : '' }}" type="button" data-nav-group-button aria-expanded="false">
                    <span>🎮 Игры</span>
                    <span class="navGroup__chevron">⌄</span>
                </button>

                <div class="navDropdown">
                    @if(Route::has('games.index'))
                        <a href="{{ route('games.index') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'games') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🎯</span>
                            <span>
                                <span class="navDropdown__title">Мини-игры</span>
                                <span class="navDropdown__text">слова, картинки и память</span>
                            </span>
                        </a>
                    @endif

                    @if(Route::has('chests.index'))
                        <a href="{{ route('chests.index') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'chests') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🎁</span>
                            <span>
                                <span class="navDropdown__title">Сундуки</span>
                                <span class="navDropdown__text">получай случайные награды</span>
                            </span>
                        </a>
                    @endif

                    @if(Route::has('shop'))
                        <a href="{{ route('shop') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'shop') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🛒</span>
                            <span>
                                <span class="navDropdown__title">Магазин</span>
                                <span class="navDropdown__text">образы и предметы героя</span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            <div class="navGroup {{ $isActive(['achievements', 'stats', 'profile']) ? 'is-current' : '' }}" data-nav-group>
                <button class="navGroup__button {{ $isActive(['achievements', 'stats', 'profile']) ? 'active' : '' }}" type="button" data-nav-group-button aria-expanded="false">
                    <span>🏆 Прогресс</span>
                    <span class="navGroup__chevron">⌄</span>
                </button>

                <div class="navDropdown">
                    @if(Route::has('achievements'))
                        <a href="{{ route('achievements') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'achievements') ? 'active' : '' }}">
                            <span class="navDropdown__icon">🏅</span>
                            <span>
                                <span class="navDropdown__title">Достижения</span>
                                <span class="navDropdown__text">открытые награды ученика</span>
                            </span>
                        </a>
                    @endif

                    @if(Route::has('stats.index'))
                        <a href="{{ route('stats.index') }}" class="navDropdown__item mainMenu__link {{ str_starts_with($route ?? '', 'stats') ? 'active' : '' }}">
                            <span class="navDropdown__icon">📊</span>
                            <span>
                                <span class="navDropdown__title">Статистика</span>
                                <span class="navDropdown__text">XP, серия и активность</span>
                            </span>
                        </a>
                    @endif

                </div>
            </div>

          

            @auth
                @if((auth()->user()->is_admin ?? false) && Route::has('admin.index'))
                    <a href="{{ route('admin.index') }}" class="navDirectLink mainMenu__link {{ str_starts_with($route ?? '', 'admin') ? 'active' : '' }}">
                        ⚙️ Админ
                    </a>
                @endif
            @endauth
        </nav>

        <div class="modernUserArea">
            @auth
                <div class="userStats" aria-label="Статистика пользователя">
                    <span title="Уровень">⭐ {{ auth()->user()->level ?? 1 }}</span>
                    <span title="Опыт">✨ {{ auth()->user()->xp ?? 0 }}</span>
                    <span title="Монеты">🪙 {{ auth()->user()->coins ?? 0 }}</span>
                    <span title="Серия дней">🔥 {{ auth()->user()->streak ?? 0 }}</span>
                </div>

                <div class="profileMenu" data-profile-menu>
                    <button class="profileTrigger" type="button" data-profile-toggle aria-expanded="false">
                        <span class="profileAvatarMini">{{ $initial }}</span>
                        <span class="userName">{{ auth()->user()->name }}</span>
                        <span class="navGroup__chevron">⌄</span>
                    </button>

                    <div class="profileDropdown">
                        <div class="profileDropdown__head">
                            <div class="profileDropdown__title">{{ auth()->user()->name }}</div>
                            <div class="profileDropdown__text">Продолжай серию и собирай награды ✨</div>
                        </div>

                        @if(Route::has('profile.page'))
                            <a class="profileDropdown__item" href="{{ route('profile.page') }}">
                                <span class="profileDropdown__icon">👤</span>
                                <span>
                                    <span class="profileDropdown__title">Мой профиль</span>
                                    <span class="profileDropdown__text">персонаж и прогресс</span>
                                </span>
                            </a>
                        @endif

                        @if(Route::has('profile.edit'))
                            <a class="profileDropdown__item" href="{{ route('profile.edit') }}">
                                <span class="profileDropdown__icon">⚙️</span>
                                <span>
                                    <span class="profileDropdown__title">Настройки</span>
                                    <span class="profileDropdown__text">данные аккаунта</span>
                                </span>
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                <span class="profileDropdown__icon">🚪</span>
                                <span>
                                    <span class="profileDropdown__title">Выйти</span>
                                    <span class="profileDropdown__text">завершить занятие</span>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            @guest
                <div class="authButtons">
                    @if(Route::has('login'))
                        <a class="miniBtn" href="{{ route('login') }}">Вход</a>
                    @endif

                    @if(Route::has('register'))
                        <a class="miniBtn miniBtn--primary" href="{{ route('register') }}">Регистрация</a>
                    @endif
                </div>
            @endguest
        </div>
    </div>
</header>
