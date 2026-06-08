<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name'=>'Шапка волшебника','type'=>'hat','price'=>60,'emoji'=>'🧙‍♂️'],
            ['name'=>'Кепка чемпиона','type'=>'hat','price'=>40,'emoji'=>'🧢'],
            ['name'=>'Очки умника','type'=>'glasses','price'=>50,'emoji'=>'👓'],
            ['name'=>'Супер-очки','type'=>'glasses','price'=>80,'emoji'=>'🕶️'],
        ];

        foreach ($items as $it) {
            Item::updateOrCreate(
                ['name' => $it['name'], 'type' => $it['type']], // уникальность
                ['price' => $it['price'], 'emoji' => $it['emoji']]
            );
        }
    }
}
