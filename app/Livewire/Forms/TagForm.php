<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tag;



class TagForm extends Form
{
    #[Validate([
        'required', 
        'string', 
        'max:255',
        'unique:tags,tag_name,NULL,Auth::id()' => 'そのタグ名は既に存在します。'
    ])]
    public $tag_name = '';
}
?>