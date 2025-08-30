<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\Word;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; // #[On('wordsUpdated')]の使用
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    public $editingWord = null;

    #[On('showWordDetail')]
    public function showWordDetail(Word $word): void
    {
        //指定のWordモデルインスタンスを格納
        $this->editingWord = $word;

        // Alpine.jsにモーダルを開くイベントとデータを送信
        $this->dispatch('open-word-detail-modal');
    }

    //フォーム送信時の処理
    public function wordUpdate(): void
    {
        //新しい入力値をwordsテーブルに挿入
        $this->editingWord->save();

        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('wordsUpdated');
    }

    //削除ボタンを押すと実行
    public function wordDelete(): void
    {
        // 現在編集中の語句をDBから削除
        $this->editingWord->delete();

        // wordsテーブルの更新イベントを渡す
        $this->dispatch('wordsUpdated');
    }
}; ?>

<div class="container-lg" x-data="{ showModal: false }" x-on:open-word-detail-modal="showModal = true;">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="wordUpdate">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!-- ヘッダー -->
                    <div class="modal-header">
                        <!-- モーダルを閉じる -->
                        <button type="button" class="btn-close" x-on:click="showModal = false"></button>
                    </div>
                    
                    <!-- ボディ -->
                    <div class="modal-body">
                        <!--語句名フィールド-->
                        <div class="mb-3">
                            <label for="word_name" class="form-label">語句</label>
                            <input id="word_name" name="word_name" type="text" class="form-control"
                                wire:model="editingWord.word_name" required>
                        </div>

                        <!--語句説明フィールド-->
                        <div class="mb-3">
                            <label for="description" class="form-label">説明</label>
                            <textarea id="description" name="description" class="form-control" wire:model="editingWord.description" rows="15" required></textarea>
                        </div>

                        <!-- タグ一覧 -->
                        <!-- タグチェックリストモーダル -->
                        <div>
                            <span>タグ</span>
                        </div>
                        <div class="d-flex">
                            @if($editingWord)
                                @foreach($editingWord->tags as $editingTag)
                                    <span>
                                        {{$editingTag->tag_name}}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- フッター -->
                    <div class="modal-footer d-flex justify-content-between ">
                        <!-- 削除ボタン -->
                        <button type="button" class="btn btn-danger" wire:click="wordDelete"
                            wire:confirm="本当にこの投稿を削除しますか？"x-on:click="showModal = false">削除</button>
                        
                            <!--保存ボタン-->
                        <button type="submit" class="btn btn-primary" x-on:click="showModal = false">更新</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
