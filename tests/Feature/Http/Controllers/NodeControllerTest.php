<?php

use App\Models\Node;

test('it returns all nodes', function () {
    $nodes = Node::factory()
        ->count(3)
        ->sequence(fn ($sequence) => ['parent_id' => $sequence->index === 0 ? null : 1])
        ->create();

    $response = $this->getJson(route('nodes.index'));

    $response->assertOk()
        ->assertJsonCount($nodes->count(), 'data');
});

test('it returns a single node', function () {
    $node = Node::factory()->root()->create();

    $response = $this->getJson(route('nodes.show', $node));

    $response->assertOk()
        ->assertJsonFragment(['id' => $node->id]);
});

test('it returns a single node with children', function () {
    $nodes = Node::factory()
        ->count(3)
        ->sequence(fn ($sequence) => ['parent_id' => $sequence->index === 0 ? null : 1])
        ->create();

    $response = $this->getJson(route('nodes.show', $nodes->first()->id));

    $response->assertOk()
        ->assertJsonCount(2, 'data.children');
});

test('throws on unknown node', function () {
    $response = $this->getJson(route('nodes.show', '::unknown-id::'));

    $response->assertNotFound();
});

test('it creates a node', function () {
    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
        'language' => 'PHP',
    ]);

    $response->assertCreated();
    $this->assertDatabaseCount(Node::class, 1);
});

test('language is required for non-managers', function () {
    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
    ]);

    $response->assertJsonValidationErrorFor('language');
});

test('it creates a manager node', function () {
    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => true,
        'department' => 'Development',
    ]);

    $response->assertCreated();
    $this->assertDatabaseCount(Node::class, 1);
});

test('department is required for managers', function () {
    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => true,
    ]);

    $response->assertJsonValidationErrorFor('department');
});

test('node defaults to root', function () {
    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
        'language' => 'PHP',
    ]);

    $response->assertJsonFragment(['parent_id' => null, 'height' => 0]);
});

test('only one root can exist', function () {
    Node::factory()->root()->create();

    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
        'language' => 'PHP',
    ]);

    $response->assertJsonValidationErrorFor('parent_id');
});

test('it sets parent id', function () {
    $root = Node::factory()->root()->create();

    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
        'language' => 'PHP',
        'parent_id' => $root->id,
    ]);

    $response->assertJsonFragment(['parent_id' => $root->id]);
});

test('it sets height', function () {
    $root = Node::factory()->root()->create();

    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
        'language' => 'PHP',
        'parent_id' => $root->id,
    ]);

    $response->assertJsonFragment(['height' => 1]);
});

test('it sets nested height', function () {
    $node = Node::factory()->create();

    $response = $this->postJson(route('nodes.store'), [
        'name' => '::node-name::',
        'is_manager' => false,
        'language' => 'PHP',
        'parent_id' => $node->id,
    ]);

    $response->assertJsonFragment(['height' => 2]);
});

test('it can update parent id', function () {
    [$root, $a, $b] = Node::factory()
        ->count(3)
        ->sequence(fn ($sequence) => ['parent_id' => $sequence->index === 0 ? null : 1])
        ->create()
        ->all();

    $response = $this->putJson(route('nodes.update', $b), [
        'parent_id' => $a->id,
    ]);

    $response->assertJsonFragment(['parent_id' => $a->id, 'height' => 2]);
});

test('updating parent id cascades height calculation', function () {
    [$root, $a, $b, $c] = Node::factory()
        ->count(4)
        ->sequence(fn ($sequence) => ['parent_id' => $sequence->index === 0 ? null : 1])
        ->create()
        ->all();

    // $root -> [ [$a -> $b], $c ]
    $this->putJson(route('nodes.update', $b), [
        'parent_id' => $a->id,
    ]);

    // $root -> $c -> $a -> $b
    $response = $this->putJson(route('nodes.update', $a), [
        'parent_id' => $c->id,
    ]);

    expect($c)->toHaveKey('height', 1);
    expect($a->refresh())->toHaveKey('height', 2)->toHaveKey('parent_id', $c->id);
    expect($b->refresh())->toHaveKey('height', 3)->toHaveKey('parent_id', $a->id);
});

test('it deletes node', function () {
    $node = Node::factory()->create();

    $response = $this->deleteJson(route('nodes.destroy', $node));

    $response->assertNoContent();
    $this->assertDatabaseMissing(Node::class, ['id' => $node->id]);
});

test('deleting node updates children with new parent', function () {
    [$root, $a, $b] = Node::factory()
        ->count(3)
        ->sequence(fn ($sequence) => ['parent_id' => $sequence->index === 0 ? null : 1])
        ->create()
        ->all();

    // $root -> $a -> $b
    $this->putJson(route('nodes.update', $b), [
        'parent_id' => $a->id,
    ]);
    $this->deleteJson(route('nodes.destroy', $a));

    expect($b->refresh())->toHaveKey('parent_id', $root->id);
});
