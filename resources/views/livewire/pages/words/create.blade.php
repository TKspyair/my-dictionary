<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    public string $wordName = '';
    public string $wordDescription = '';
    public $checkedTags = null;

    //tags.check-listから引数を渡される
    #[On('return-checked-tag-ids')]
    public function loadCheckedTags(array $checkedTagIds)
    {
        //引数のタグidをもつタグコレクションを取得
        $this->checkedTags = Tag::whereIn('id', $checkedTagIds)->get();
    }

    //フォーム送信時の処理
    public function createWord(): void
    {
        //Wordモデルに新しいレコードを挿入し、インスタンス化
        $word = Word::create([
            'word_name' => $this->wordName,
            'description' => $this->wordDescription,
            'user_id' => Auth::id(),
        ]);

        //attouch()によりtag_idとword_idが自動的に結び付けられ、中間テーブルに挿入される
        $word->tags()->attach($this->checkedTags);
        // tags() : Wordモデルのクラスメソッド。
        /*attach() :
        中間テーブルへのレコード挿入: 中間テーブルに、関連付けに必要な外部キーのペア（例：word_idとtag_id）を自動的に挿入
        IDの自動取得: $word->tags()->attach(...)のように呼び出された際に、呼び出し元のモデル（この場合は $word）のIDを自動的に取得
        */

        $this->reset(['wordName', 'wordDescription']);

        $this->dispatch('wordsUpdated');
    }
}; ?>

<div class="container-fluid" x-data="{ showCreateWordModal: false }" 
    x-on:open-create-word-modal.window="showCreateWordModal = true">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showCreateWordModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="createWord" x-on:submit="showCreateWordModal = false">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!-- モーダルボディ -->
                    <div class="modal-body">

                        <!--閉じるボタン-->
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn-close" x-on:click="showCreateWordModal = false"></button>
                        </div>

                        <!-- 語句フィールド -->
                        <div>
                            <input type="text" class="form-control border-0 fs-5 fw-bold"
                                placeholder="語句" wire:model="wordName" required>
                        </div>

                        <hr class="m-0 p-0">

                        <!-- 説明フィールド -->
                        <div>
                            <textarea  wire:model="wordDescription"
                                class="form-control border-0" rows="15" placeholder="説明" required></textarea>
                        </div>

                        <!-- タグ選択 -->
                        <div>
                            <span x-on:click="$dispatch('open-tag-check-list')">
                                タグ選択
                            </span>

                            @if ($this->checkedTags)
                                <div class="d-flex">
                                    @foreach ($this->checkedTags as $checkedTag)
                                        <span class="badge bg-secondary me-1">
                                            {{ $checkedTag->tag_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!--保存ボタン-->
                    <div class="modal-footer d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
        @livewire('pages.tags.check-list')
    </div>
</div>
