<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tag;
use App\Models\Word;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new #[Layout('layouts.words-app')] class extends Component {
    //======================================================================
    // Property(プロパティ)
    //======================================================================

    public $tags;

    # 親コンポーネントから初期値が渡される
    /** 親コンポーネント
     * words.create
     * words.edit
    */
    public array $selectedTagIds = [];

    //======================================================================
    // 初期化
    //======================================================================
    public function mount()
    {
        $this->loadTags();
    }

    // タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTags(): void
    {
        $this->tags = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //======================================================================
    // method(メソッド)
    //======================================================================

    # tags.select-tagから最新のタグ選択状態を受け取る
    #[On('send-tag-ids-for-select-tag')]
    public function setSelectedTagIds(array $selectedTagIds): void
    {
        $this->selectedTagIds = $selectedTagIds;
    }

    public function sendSelectedTagIds(): void
    {
        $this->dispatch('send-tag-ids-for-parents', selectedTagIds: $this->selectedTagIds);
    }
}; ?>

<!-- タグ選択モーダル -->
<section class="container-md" x-data="{ showModal: false }" 
    x-on:open-tags-select-tag.window="showModal = true"
    x-on:close-tags-select-tag.window="showModal = false">

    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <!-- ヘッダー部 -->
                    <header class="modal-header d-flex align-items-center p-2">
                        <!--戻るボタン-->
                        <button type="button" class="btn btn-link text-dark border-0 p-0 m-2"
                            wire:click="sendSelectedTagIds"
                            x-on:click="showModal = false">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </button>

                        <h5 class="modal-title mb-0">タグ</h5>
                    </header>

                    <!-- ボディ部 -->
                    <div class="modal-body">
                        <!-- タグ選択リスト -->
                        @foreach ($this->tags as $tag)
                            <div class="form-check" wire:key=" tags-select-tag-{{ $tag->id }} ">
                                <input type="checkbox" class="form-check-input" 
                                    name="selectedTagIds[]" value="{{ $tag->id }}" id=" tags-select-tag-{{ $tag->id }} "
                                    wire:model.live="selectedTagIds">

                                <label for=" tags-select-tag-{{ $tag->id }} " class="form-check-label">
                                    {{ $tag->tag_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
</section>
