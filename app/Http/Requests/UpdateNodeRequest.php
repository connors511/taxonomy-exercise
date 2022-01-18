<?php

namespace App\Http\Requests;

use App\Models\Node;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNodeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'parent_id' => ['nullable', Rule::exists(Node::class, 'id')],
        ];
    }
}
