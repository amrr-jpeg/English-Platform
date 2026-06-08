@php
    $avatarClass = $avatarClass ?? 'shopAvatar';
    $showCharacterInfo = $showCharacterInfo ?? false;

    $skin = $user->skin ?? 'blue';
    $hat = $user->equipped_hat ?? null;
    $accessory = $user->equipped_accessory ?? null;
    $effect = $user->equipped_effect ?? null;
    $background = $user->profile_background ?? null;
    $frame = $user->profile_frame ?? null;

    $skinName = [
        'blue' => 'Синий ученик',
        'pink' => 'Розовый стиль',
        'green' => 'Зелёный герой',
    ][$skin] ?? 'Синий ученик';

    $classes = trim(implode(' ', array_filter([
        $avatarClass,
        'customAvatarView',
        'realCharacterView',
        'realCharacterView--' . $skin,
        'buddy--' . $skin,
        $background,
        $frame,
        $effect ? 'has-effect has-' . $effect : null,
        $hat ? 'has-hat has-' . $hat : null,
        $accessory ? 'has-accessory has-' . $accessory : null,
    ])));
@endphp

<div class="{{ $classes }}">
    <div class="realCharacterStage" aria-label="Текущий образ персонажа">
        @if($effect)
            <div class="realCharacterEffect realCharacterEffect--{{ $effect }}" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
        @endif

        <div class="realCharacterShadow" aria-hidden="true"></div>

        <div class="realCharacter realCharacter--{{ $skin }}">
            @if($hat)
                <div class="realHat realHat--{{ $hat }}" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>
            @endif

            <div class="realCharacter__head">
                <div class="realCharacter__hair"></div>
                <div class="realCharacter__ear realCharacter__ear--left"></div>
                <div class="realCharacter__ear realCharacter__ear--right"></div>
                <div class="realCharacter__eyes"><span></span><span></span></div>
                <div class="realCharacter__nose"></div>
                <div class="realCharacter__mouth"></div>
                <div class="realCharacter__cheek realCharacter__cheek--left"></div>
                <div class="realCharacter__cheek realCharacter__cheek--right"></div>

                @if($accessory === 'acc_glasses')
                    <div class="realAccessory realAccessory--glasses" aria-hidden="true">
                        <span></span><span></span><i></i>
                    </div>
                @endif
            </div>

            <div class="realCharacter__neck"></div>

            <div class="realCharacter__body">
                <div class="realCharacter__collar realCharacter__collar--left"></div>
                <div class="realCharacter__collar realCharacter__collar--right"></div>
                <div class="realCharacter__shirtMark"></div>

                @if($accessory === 'acc_star')
                    <div class="realAccessory realAccessory--star" aria-hidden="true"></div>
                @endif
            </div>

            <div class="realCharacter__arm realCharacter__arm--left"></div>
            <div class="realCharacter__arm realCharacter__arm--right"></div>
            <div class="realCharacter__leg realCharacter__leg--left"></div>
            <div class="realCharacter__leg realCharacter__leg--right"></div>

            @if($accessory === 'acc_fire')
                <div class="realAccessory realAccessory--fire" aria-hidden="true">
                    <span></span><span></span><span></span>
                </div>
            @endif
        </div>
    </div>

    @if($showCharacterInfo)
        <div class="buddy__name">LVL {{ $user->level }}</div>
        <div class="buddy__skin">Текущий стиль: <b>{{ $skinName }}</b></div>
        <div class="buddy__mini">XP: {{ $user->xp }} • Монеты: {{ $user->coins }}</div>
    @endif
</div>
