<?php

namespace Botble\Kids\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class KidsParentRequest extends Request
{
    public function rules(): array
    {
        $parentId = $this->route('parent');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('kids_parents', 'username')->ignore($parentId)],
            'password' => [$parentId ? 'nullable' : 'required', 'string', 'min:6'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
