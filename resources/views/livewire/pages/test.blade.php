<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Illuminate\Validation\Rule; 
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    
}
?>

<x-test :xData="json_encode(['showModal' => false, 'showTitle' => false])"
    :events="[ 
    'open-test-modal' => 'showModal = true',
    'close-all-modal' => 'showModal = false',
    ]" 
    title="テスト">
    <x-slot:backButton>
        <x-back-Button/>
    </x-slot:backButton>
    <button x-on:click="showTitle = true">タイトル出るよ</button>
</x-test>