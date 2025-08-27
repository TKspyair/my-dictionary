<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    public string $word_name = '';
    public string $description = '';

    //フォーム送信時の処理
    public function word_create(): void
    {
        $validated = $this->validate([
            'word_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        //フォームの入力値とuser_idををwordsテーブルに挿入
        Word::create([
            'word_name' => $this->word_name,
            'description' => $this->description,
            'user_id' => Auth::id(),
        ]);

        // フォームの入力値をリセット
        $this->reset(['word_name', 'description']);

        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('wordsUpdated');
    }
}; ?>

<div class="container-fluid" x-data="{ showModal: false }" x-on:open-word-create-modal.window="showModal = true">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="word_create" x-on:submit="showModal = false">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!--モーダルヘッダー-->
                    <div class="modal-header">
                        <h5 class="modal-title">新しい語句を追加</h5>

                        <!--閉じるボタン-->
                        <button type="button" class="btn-close" x-on:click="showModal = false"aria-label="Close"></button>
                    </div>

                    <!-- モーダルボディ -->
                    <div class="modal-body">

                        <!--語句名フィールド-->
                        <div class="mb-3">
                            <label for="word_name" class="form-label">語句</label>
                            <input id="word_name" name="word_name" type="text" class="form-control" wire:model="word_name" required>
                            @error('word_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!--語句説明フィールド-->
                        <div class="mb-3">
                            <label for="description" class="form-label">説明</label>
                            <textarea id="description" name="description" class="form-control" wire:model="description" rows="15" required></textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!--保存ボタン-->
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
