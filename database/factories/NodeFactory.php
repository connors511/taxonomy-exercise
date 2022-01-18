<?php

namespace Database\Factories;

use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

class NodeFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'parent_id' => Node::factory()->root(),
            'is_manager' => $this->faker->boolean(),
            'height' => fn ($attr) => ($attr['parent_id']?->height ?? -1) + 1,
            'metadata' => fn ($attr) => $attr['is_manager']
                ? ['department' => $this->faker->company]
                : ['language' => $this->faker->languageCode],
        ];
    }

    public function root()
    {
        return $this->state(function (array $attributes) {
            return [
                'parent_id' => null,
                'height' => 0,
            ];
        });
    }
}
