<?php

namespace App\Http\Requests;

use App\Models\Node;
use App\Rules\OnlyOneRootNodeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreNodeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'is_manager' => ['required', 'boolean'],
            'parent_id' => ['nullable', Rule::exists(Node::class, 'id'), new OnlyOneRootNodeRule],
            'department' => ['required_if:is_manager,true', 'string', 'max:50'],
            'language' => ['required_if:is_manager,false', 'string', 'max:50'],
        ];
    }

    public function validated()
    {
        $metadata = ['department', 'language'];
        $data = parent::validated();

        return Arr::except($data, $metadata) + [
            'metadata' => $data[ $data['is_manager'] ? 'department' : 'language' ],
        ];
    }
}
