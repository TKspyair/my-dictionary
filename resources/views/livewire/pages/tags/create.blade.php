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
    public $tags;

    //新規作成中のTagインスタンスのプロパティ
    public $newTagName;

    // 編集中のTagインスタンスのプロパティ
    public int $editingTagId = 0;
    public string $editingTagName = '';

    // バリデーションルール
    public function rules()
    {
        return [
            'newTagName' => [
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
            'newTagName.required' => 'タグ名は必須です。',
            'newTagName.string' => 'タグ名は文字列で入力してください。',
            'newTagName.max' => 'タグ名は255文字以内で入力してください。',
            'newTagName.unique' => 'このタグ名は既に使用されています。',

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
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //タグ作成時の処理
    public function createTag(): void
    {
        $validated = $this->validateOnly('newTagName');

        // バリデーション成功後にタグ作成モードを終了
        $this->dispatch('end-tag-create-mode');

        Tag::create([
            'tag_name' => $validated['newTagName'],
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
        $this->reset('newTagName');
    }

    //編集モードへ切り替え
    public function switchEdit(int $tagId): void
    {
        //idからTagインスタンスを取得
        $editingTag = $this->tags->find($tagId);

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

<section class="container-fluid" x-data="{ tagCreateMode: false, showModal: false }" x-on:open-tags-create-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal= false" x-on:end-tag-create-mode="tagCreateMode = false">

    <!--モーダル部分-->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1"> <!-- d-blockでmodal(display:none)を打ち消す -->
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <!-- ヘッダー部 -->
                    <header class="modal-header d-flex align-items-center p-2">

                        <!-- 戻るボタン -->
                        <x-back-button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas" />

                        <h5 class="modal-title mb-0">タグの編集</h5>
                    </header>

                    <!-- ボディ部 -->
                    <div class="modal-body bg-white">

                        {{-- タグ作成欄 --}}
                        <article>
                            {{-- 通常モード --}}
                            <div x-show="!tagCreateMode" x-on:click="tagCreateMode = true">
                                <span>
                                    <i class="bi bi-plus-lg me-2"></i>新しいタグを作成
                                </span>
                            </div>

                            {{-- タグ作成モード --}}
                            <div x-show="tagCreateMode; $nextTick(() => $refs.newTagName.focus());"> 
                                <div class="d-flex align-items-center has-validation">

                                    {{-- 作成キャンセルボタン --}}
                                    <button x-on:click="tagCreateMode = false" wire:click="clearCreateForm"
                                        class="btn btn-link text-dark border-0 p-0 me-3 fs-6">
                                        <i class="bi bi-x-lg"></i>
                                    </button>

                                    {{-- 新規タグ名フィールド --}}
                                    <x-form-input class="fs-6" wire:model="newTagName" wire:click="createTag"
                                        wire:keydown.enter="createTag" x-ref="newTagName" />
                                </div>
                            </div>
                        </article>

                        <!-- タグ一覧 -->
                        <article class="mt-3">
                            <ul class="list-group list-group-flush">
                                @foreach ($this->tags as $tag)
                                    <li class="list-group-item d-flex align-items-center border-0 p-0 my-2" wire:key="{{ $tag->id }}">
                                        {{-- 編集中のタグが存在すれば表示 --}}
                                        @if ($this->editingTagId === $tag->id)
                                            {{-- 編集モード --}}
                                            <div class="d-flex align-items-center input-group flex-grow-1">
                                                {{-- タグ削除ボタン --}}
                                                <x-trash-button wire:click="deleteTag({{ $tag->id }})" />

                                                {{-- 編集タグフィールド --}}
                                                <x-form-input class="fs-6" x-init="$el.focus()"
                                                    wire:model="editingTagName"
                                                    wire:keydown.enter="updateTag" />

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
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
