<?php

namespace App\Rules;

use App\Models\Node;
use Illuminate\Contracts\Validation\ImplicitRule;

class OnlyOneRootNodeRule implements ImplicitRule
{
    public function passes($attribute, $value)
    {
        return $value !== null || Node::count() === 0;
    }

    public function message()
    {
        return 'Only one root node allowed.';
    }
}
