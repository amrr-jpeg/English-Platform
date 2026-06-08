<?php

$dir = __DIR__ . '/public/images/memory';

if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$items = [
    'cat.svg' => '🐱',
    'dog.svg' => '🐶',
    'apple.svg' => '🍎',
    'book.svg' => '📚',
    'car.svg' => '🚗',
    'sun.svg' => '☀️',
    'tree.svg' => '🌳',
    'house.svg' => '🏠',

    'teacher.svg' => '👨‍🏫',
    'school.svg' => '🏫',
    'window.svg' => '🪟',
    'family.svg' => '👨‍👩‍👧',
    'breakfast.svg' => '🍳',
    'friend.svg' => '🤝',
    'music.svg' => '🎵',
    'phone.svg' => '📱',

    'knowledge.svg' => '🧠',
    'environment.svg' => '🌍',
    'responsibility.svg' => '🛡️',
    'achievement.svg' => '🏆',
    'development.svg' => '📈',
    'education.svg' => '🎓',
    'technology.svg' => '💻',
    'communication.svg' => '💬',
];

foreach ($items as $file => $emoji) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300">
    <rect width="100%" height="100%" rx="40" fill="#0f172a"/>
    <text x="50%" y="55%" font-size="120" text-anchor="middle">{$emoji}</text>
</svg>
SVG;

    file_put_contents($dir . '/' . $file, $svg);
}

echo "Images created!";