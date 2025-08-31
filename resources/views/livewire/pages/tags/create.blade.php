<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tag;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On; 
use Livewire\Volt\Component;


new #[Layout('layouts.words-app')] class extends Component 
{
    
    //タグテーブルのオブジェクト
    public $tags;

    public $tag_name;

    // 編集中のタグID
    public ?int $editingTagId = null; //「?int」ヌル許容型 : 整数もしくはnull
    
    // 編集用の入力フィールド
    public string $editingTagName = '';          

    
    //初期読み込み時に実行
    public function mount()
    {
        $this->loadTags();
    }

    //　タグ一覧の更新
    #[On('tagListUpdate')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //タグ作成時の処理
    public function tagCreate(): void
    {
        //$this->validate();

        //  バリデーション成功後にAlpine.jsの変数を更新してフォームを閉じる
        $this->dispatch('end-tag-create');

        //入力値(tag_name)とuser_idををtagsテーブルに挿入
        Tag::create([
            'tag_name' => $this->tag_name,
            'user_id' => Auth::id(),
        ]);

        // 入力値をリセット
        $this->reset('tag_name');

        //タグ一覧の更新
        $this->dispatch('tagListUpdate');
    }

    // タグ作成のキャンセル
    public function cancelCreate(): void
    {
        $this->reset(['tag_name']);
    }

    
    //編集モードへ切り替え
    public function switchEdit(int $tagId): void
    {
        $this->editingTagId = $tagId;
        $tag = $this->tags->find($tagId);
        $this->editingTagName = $tag->tag_name;
    }

    // 編集内容の保存
    public function tagUpdate(): void
    {
        $validated = $this->validate([
            'editingTagName' => ['required', 'string', 'max:255'],
        ]);

        $tag = Auth::user()->tags()->find($this->editingTagId);
        //「find()」 : 主キー（通常はid）に基づいて単一のレコードを取得する ※get() : 条件に合致する全てのレコードを取得
        if ($tag) {
            $tag->update(['tag_name' => $this->editingTagName]);
            $this->reset(['editingTagId', 'editingTagName']);
            $this->dispatch('tagListUpdate');
        }
    }

    // 編集のキャンセル
    public function cancelEdit(): void
    {
        $this->reset(['editingTagId', 'editingTagName']);
    }

    // 削除処理の実行
    #[On('deleteTag')]
    public function deleteTag(int $tagId): void
    {
        $tag = Auth::user()->tags()->find($tagId);
        if ($tag) {
            $tag->delete();
            $this->dispatch('tagListUpdate');
        }
    }

}; ?>

<div class="container-fluid" x-data="{ tagCreateMode: false, showTagModal: false }" x-on:open-tag-modal.window="showTagModal =true">

    <!--モーダル部分-->
    <div x-bind:class="{ 'modal': true, 'd-block': showTagModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">

                <!--ヘッダー-->
                <div class="modal-header d-flex align-items-center">
                    <!--戻るボタン-->
                    <!--戻るボタン-->
                    <span data-bs-dismiss="modal" x-on:click="showTagModal = false">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </span>

                    <h5 class="modal-title mb-0">タグの編集</h5>
                </div>

                <!-- ボディ -->
                <div class="modal-body">

                    <!--タグ新規作成-->
                    <div>
                        <!--通常モード-->
                        <div x-show="!tagCreateMode" x-on:click="tagCreateMode = true">
                            <!-- ＋マークと文字 -->
                            <span class="d-flex align-items-center flex-grow-1">
                                <i class="bi bi-plus-lg me-2"></i>新しいタグを作成
                            </span>
                        </div>

                        <!--タグ作成モード-->
                        <div x-show="tagCreateMode" class="align-items-center input-group mb-3">
                            <!-- 作成キャンセルボタン -->
                            <span x-on:click="tagCreateMode = false; $wire.cancelCreate();" class="me-2">
                                <i class="bi bi-x-lg"></i>
                            </span>
                            <!-- 入力フォーム -->
                            <input type="text" name="tag_name"
                                class="form-control"
                                wire:model.live="tag_name" wire:keydown.enter="tagCreate"
                                x-on:end-tag-create.window="tagCreateMode =false" x-init="$el.focus()"
                                placeholder="新しいタグを作成" required>
                        </div>
                    </div>

                    <!--タグ一覧-->
                    <ul class="list-group list-group-flush">
                        @foreach ($this->tags as $tag)
                            <li class="list-group-item">
                                <!--編集モード表示-->
                                @if ($this->editingTagId === $tag->id)
                                    <div class="align-items-center input-group mb-3">
                                        <!--クリックで削除(deleteTag()を実行)-->
                                        <span class=" p-0" wire:click="deleteTag({{ $tag->id }})"
                                            wire:confirm="本当に削除しますか？">
                                            <i class="bi bi-trash me-2"></i>
                                        </span>
                                        <!--入力フォーム(決定でデータ更新)-->
                                        <input type="text" class="form-control me-2" wire:model="editingTagName"
                                            wire:keydown.enter="tagUpdate" x-init="$el.focus()">
                                        <!--編集確定-->
                                        <span wire:click="tagUpdate">
                                            <i class="bi bi-check2"></i>
                                        </span>
                                    </div>
                                    <!--一覧モード表示-->
                                @else
                                    <!--クリックで編集モードに切り替え-->
                                    <span class="d-flex align-items-center flex-grow-1"
                                        wire:click="switchEdit({{ $tag->id }})">
                                        <i class="mb-0 text-dark text-decoration-none"></i>{{ $tag->tag_name }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
