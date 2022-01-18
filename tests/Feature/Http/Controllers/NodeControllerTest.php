<?php

use App\Models\Node;

test('it returns all nodes', function () {
    $nodes = Node::factory()->count(3)->create();

    $response = $this->get(route('nodes.index'));

    $response->assertStatus(200)->assertJsonCount($nodes->count(), 'data');
});

test('it returns a single node', function () {
    $node = Node::factory()->create();

    $response = $this->get(route('nodes.show', $node));

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $node->id]);
});

test('throws on unknown node', function () {
    $response = $this->get(route('nodes.show', '::unknown-id::'));

    $response->assertStatus(404);
});
