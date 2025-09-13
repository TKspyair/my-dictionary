<?php

use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component
{
    //Tagテーブルのコレクション
    public $tagColl;

    //新規作成中のTagインスタンスのプロパティ
    public $tagName;

    // 編集中のTagインスタンスのプロパティ
    public int $editingTagId = 0;
    public string $editingTagName = '';

    // バリデーションルール
    public function rules()
    {
        return [
            'tagName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'tag_name'), // 新規作成時のuniqueルール
            ],
            'editingTagName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags', 'tag_name')->ignore($this->editingTagId),
                // ignore(int id) : 引数に指定されたidをユニーク制約の対象から外す
            ],
        ];
    }

    //　エラーメッセージ
    public function messages()
    {
        return [
            // tagNameプロパティ
            'tagName.required' => 'タグ名は必須です。',
            'tagName.string' => 'タグ名は文字列で入力してください。',
            'tagName.max' => 'タグ名は255文字以内で入力してください。',
            'tagName.unique' => 'このタグ名は既に使用されています。',

            // editingTagNameプロパティ
            'editingTagName.required' => 'タグ名は必須です。',
            'editingTagName.string' => 'タグ名は文字列で入力してください。',
            'editingTagName.max' => 'タグ名は255文字以内で入力してください。',
            'editingTagName.unique' => 'このタグ名は既に使用されています。',
        ];
    }

    //初期読み込み時に実行
    public function mount()
    {
        $this->loadTags();
    }

    //　タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tagColl = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //タグ作成時の処理
    public function createTag(): void
    {
        $validated = $this->validateOnly('tagName');

        // バリデーション成功後にタグ作成モードを終了
        $this->dispatch('end-tag-create-mode');

        Tag::create([
            'tag_name' => $validated['tagName'],
            'user_id' => Auth::id(),
        ]);

        //フォームクリア
        $this->clearCreateForm();

        //タグ一覧の更新
        $this->dispatch('update-tag-list');
    }

    // 登録フォームをクリア
    public function clearCreateForm()
    {
        $this->resetValidation();
        $this->reset('tagName');
    }

    //編集モードへ切り替え
    public function switchEdit(int $tagId): void
    {
        //idからTagインスタンスを取得
        $editingTag = $this->tagColl->find($tagId);

        if ($editingTag) {
            $this->editingTagId = $editingTag->id;
            $this->editingTagName = $editingTag->tag_name;
        }
    }

    // 編集内容の保存
    public function updateTag(): void
    {
        if ($this->editingTagId) {
            $validated = $this->validateOnly('editingTagName');

            // find()で見つけたTagインスタンスに対してupdate()を実行
            Tag::find($this->editingTagId)->update([
                'tag_name' => $validated['editingTagName'],
            ]);
            /** update()を使用する理由
             * save() : モデル(インスタンス)の現在の状態をデータベースに保存　例 [インスタンス]->save();
             * update() : 引数で渡された配列の内容でデータベースを更新する　例 [インスタンス]->update(['カラム名'　=> 値])
             */
            $this->clearEditForm();
            $this->dispatch('update-tag-list');
        }
    }

    // 編集中のタグをクリア
    public function clearEditForm()
    {
        $this->resetValidation();
        $this->reset('editingTagId', 'editingTagName');
    }

    // 削除処理の実行
    public function deleteTag(int $tagId): void
    {
        // ログイン中のユーザーのタグの中から、IDが一致するものを削除
        Auth::user()->tags()->where('id', $tagId)->delete();

        $this->dispatch('update-tag-list');
    }
}; ?>

<div class="container-fluid" x-data="{ tagCreateMode: false, showModal: false }"
    x-on:open-tags-create-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal= false"
    x-on:end-tag-create-mode="tagCreateMode = false"
    ">

    <!--モーダル部分-->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1"> <!-- d-blockでmodal(display:none)を打ち消す -->
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <!--ヘッダー-->
                    <div class="modal-header d-flex align-items-center">

                        <!--戻るボタン-->
                        <x-back-button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas"/>

                        <h5 class="modal-title mb-0">タグの編集</h5>
                    </div>

                    <!-- ボディ -->
                    <div class="modal-body">

                            {{-- タグ作成欄 --}}
                            <div>
                                {{-- 通常モード --}}
                                <div x-show="!tagCreateMode" x-on:click="tagCreateMode = true; $nextTick(() => $refs.tagName.focus())"> 
                                    <span>
                                        <i class="bi bi-plus-lg me-2"></i>新しいタグを作成
                                    </span>
                                </div>
                                <!--
                                - x-init : 要素がDOMに最初に描画されたときに一度だけコードを実行  例　ページ読み込み時の初期設定
                                    ※　x-showで要素の表示・非表示が切り替わっても、再度実行されることはありません。

                                - $nextTick : Alpine.jsがDOMの更新を完了した直後にコードを実行します。
                                　※　$nextTickは関数であり、実行したい処理をコールバック関数として渡す必要があります
                                -->
                                {{-- タグ作成モード --}}
                                <div x-show="tagCreateMode"> <!-- モード切替を一番上の要素にすることで、子要素のd-flexなどに影響されないようにする -->
                                    <div class="d-flex align-items-center has-validation">

                                        {{-- 作成キャンセルボタン --}}
                                        <span x-on:click="tagCreateMode = false" wire:click="clearCreateForm"
                                            class="me-3">
                                            <i class="bi bi-x-lg"></i>
                                        </span>

                                        {{-- 新規タグ名フィールド --}}
                                        <x-form-input wire:model="tagName" 
                                            wire:click="createTag" wire:keydown.enter="createTag" 
                                            x-ref="tagName"/>
                                    </div>
                                </div>

                            </div>

                            <!-- タグ一覧 -->
                            <ul class="list-group list-group-flush">
                                @foreach ($this->tagColl as $tag)
                                    <li class="list-group-item d-flex align-items-center">
                                        {{-- 編集中のタグが存在すれば表示 --}}
                                        @if ($this->editingTagId === $tag->id)
                                            {{-- 編集モード --}}
                                            <div class="input-group flex-grow-1">
                                                {{-- タグ削除ボタン --}}
                                                <x-trash-button wire:click="deleteTag({{ $tag->id }})" />

                                                {{-- 編集タグフィールド --}}
                                                <x-form-input wire:model="editingTagName" wire:keydown.enter="updateTag"
                                                />

                                                {{-- 確定ボタン --}}
                                                <x-confirm-button wire:click="updateTag" />
                                            </div>
                                        @else
                                            {{-- 一覧モード表示
                                    - クリックするごとに編集中のタグが更新される
                                    --}}
                                            <div wire:click="switchEdit({{ $tag->id }})">
                                                <span class="d-flex align-items-center flex-grow-1">
                                                    <i class="bi bi-tag text-decoration-none me-2"></i>
                                                    {{ $tag->tag_name }}
                                                </span>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

