<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNodeRequest;
use App\Http\Requests\UpdateNodeRequest;
use App\Http\Resources\NodeCollectionResource;
use App\Http\Resources\NodeResource;
use App\Models\Node;

class NodeController extends Controller
{
    public function index()
    {
        return new NodeCollectionResource(Node::all());
    }

    public function store(StoreNodeRequest $request)
    {
        return new NodeResource(
            Node::create($request->validated())->refresh()
        );
    }

    public function show(Node $node)
    {
        return new NodeResource($node->load('children'));
    }

    public function update(UpdateNodeRequest $request, Node $node)
    {
        $node->update($request->validated());

        return new NodeResource($node);
    }

    public function destroy(Node $node)
    {
        $node->delete();

        return response()->noContent();
    }
}
