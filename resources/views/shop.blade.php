@extends('layouts.kids')

@section('content')
<div class="shopPage">
    <a class="link" href="{{ route('dashboard') }}">← Назад</a>

    <div class="shopHero">
        <div>
            <h1 class="h1">Магазин</h1>
            <p class="muted">
                Покупай предметы один раз, выбирай их и украшай персонажа.
            </p>
        </div>

        <div class="shopWallet">
            <div class="shopWallet__coins">🪙 {{ $user->coins }}</div>
            <div class="muted">твой баланс</div>
        </div>
    </div>

    @if(session('success'))
        <div class="toast toast--ok">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="toast toast--bad">{{ session('error') }}</div>
    @endif

    @if(session('info'))
        <div class="toast">{{ session('info') }}</div>
    @endif

    <div class="shopLayout">
        <aside class="shopPreview">
            <div class="shopPreview__head">
                <span class="badge">🎨 Персонаж</span>
                <h2 class="h2">Твой образ</h2>
            </div>

            @include('partials.player-avatar', [
                'user' => $user,
                'avatarClass' => 'shopAvatar',
                'faceClass' => 'shopAvatar__face',
            ])

            <div class="shopEquippedList">
                <div>
                    <span>Головной убор</span>
                    <b>{{ $equippedItems['hat']['name'] ?? 'не выбран' }}</b>
                </div>

                <div>
                    <span>Аксессуар</span>
                    <b>{{ $equippedItems['accessory']['name'] ?? 'не выбран' }}</b>
                </div>

                <div>
                    <span>Эффект</span>
                    <b>{{ $equippedItems['effect']['name'] ?? 'не выбран' }}</b>
                </div>

                <div>
                    <span>Фон</span>
                    <b>{{ $equippedItems['background']['name'] ?? 'не выбран' }}</b>
                </div>

                <div>
                    <span>Рамка</span>
                    <b>{{ $equippedItems['frame']['name'] ?? 'не выбрана' }}</b>
                </div>
            </div>
        </aside>

        <div class="shopContent">
            @foreach($items as $type => $group)
                <section class="shopSection">
                    <div class="shopSection__head">
                        <div>
                            <h2 class="h2">
                                @if($type === 'hat')
                                    🎩 Головные уборы
                                @elseif($type === 'accessory')
                                    ⭐ Аксессуары
                                @elseif($type === 'effect')
                                    ✨ Эффекты
                                @elseif($type === 'background')
                                    🌄 Фоны профиля
                                @elseif($type === 'frame')
                                    🖼️ Рамки
                                @else
                                    🛍️ Предметы
                                @endif
                            </h2>
                            <p class="muted small">Выбери предмет, чтобы собрать красивый образ ученика.</p>
                        </div>
                    </div>

                    <div class="shopGrid">
                        @foreach($group as $item)
                            @php
                                $isOwned = in_array($item['key'], $owned);

                                $isEquipped =
                                    ($type === 'hat' && $user->equipped_hat === $item['key']) ||
                                    ($type === 'accessory' && $user->equipped_accessory === $item['key']) ||
                                    ($type === 'effect' && $user->equipped_effect === $item['key']) ||
                                    ($type === 'background' && $user->profile_background === $item['key']) ||
                                    ($type === 'frame' && $user->profile_frame === $item['key']);
                            @endphp

                            <article class="shopItem {{ $isEquipped ? 'shopItem--equipped' : '' }}">
                                <div class="shopItem__icon">
                                    {{ $item['icon'] }}
                                </div>

                                <div class="shopItem__body">
                                    <h3>{{ $item['name'] }}</h3>

                                    <p>{{ $item['description'] }}</p>

                                    <div class="shopItem__meta">
                                        @if($isEquipped)
                                            <span class="badge badge--ok">Выбрано</span>
                                        @elseif($isOwned)
                                            <span class="badge">Куплено</span>
                                        @else
                                            <span class="badge">Цена: {{ $item['price'] }} 🪙</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="shopItem__actions">
                                    @if(!$isOwned)
                                        <form method="POST" action="{{ route('shop.buy') }}">
                                            @csrf

                                            <input type="hidden" name="item_key" value="{{ $item['key'] }}">

                                            <button class="btn" type="submit">
                                                Купить
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('shop.equip') }}">
                                            @csrf

                                            <input type="hidden" name="item_key" value="{{ $item['key'] }}">

                                            <button class="btn {{ $isEquipped ? 'btn--ghost' : '' }}" type="submit">
                                                {{ $isEquipped ? 'Снять' : 'Выбрать' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
@endsection
