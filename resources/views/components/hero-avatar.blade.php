@props([
    'user',
    'size' => 'normal',
    'showLevel' => false,
])

@php
    $skin = $user->skin ?? 'blue';
    $hat = $user->equipped_hat ?? null;
    $accessory = $user->equipped_accessory ?? null;
    $effect = $user->equipped_effect ?? null;
    $background = $user->profile_background ?? null;
    $frame = $user->profile_frame ?? null;
@endphp

<div {{ $attributes->merge([
    'class' => trim(implode(' ', [
        'heroAvatar',
        'heroAvatar--' . $size,
        'heroAvatar--' . $skin,
        $background,
        $frame,
        $effect ? 'heroAvatar--' . $effect : null,
    ])),
]) }}>
    <div class="heroAvatar__scene" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="heroAvatar__body">
        <div class="heroAvatar__shine"></div>

        <div class="heroAvatar__eyes">
            <span></span>
            <span></span>
        </div>

        <div class="heroAvatar__mouth"></div>

        @if($hat)
            <div class="heroAvatar__hat heroAvatar__hat--{{ $hat }}" aria-hidden="true"></div>
        @endif

        @if($accessory)
            <div class="heroAvatar__accessory heroAvatar__accessory--{{ $accessory }}" aria-hidden="true"></div>
        @endif
    </div>

    @if($showLevel)
        <div class="heroAvatar__level">LVL {{ $user->level }}</div>
    @endif
</div>
