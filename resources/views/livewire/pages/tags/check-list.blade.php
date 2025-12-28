<?php

use App\Models\Tag;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout; 
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.words-app')] class extends Component 
{

//======================================================================
// プロパティ
//======================================================================

    //ユーザーのもつ全てのTagコレクション
    public $tags;

    //チェックしたタグのid
    public $selectedTagIds = [];

//======================================================================
// 初期化・更新
//======================================================================
    // 初期読み込み時に実行
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
// メソッド
//======================================================================
    //-----------------------------------------------------
    // CRUD機能
    //-----------------------------------------------------
        # $selectedTagIdsの値が追加・削除(更新)されると、そのデータを配列で他ファイルに渡す
        public function updatedSelectedTagIds()
        {
            $this->dispatch('return-selected-tag-ids', selectedTagIds: $this->selectedTagIds);

            /** 関連ファイル 
             * words.create
             * words.detail
            */
        }

    //-----------------------------------------------------
    // 語句詳細・編集機能専用(words.detail)
    //-----------------------------------------------------
        # 渡されたタグのidを受け取る
        #[On('send-selected-tag-ids')]
        public function setSelectedTagIds(array $selectedTagIds)
        {
            //受け取ったidを格納
            $this->selectedTagIds = $selectedTagIds;

            /** 関連ファイル
             * words.detail
             * words.
            */
        }
};
?>

<section x-data="{ showModal: false }" 
    x-on:open-tags-check-list.window="showModal = true">

    <!-- モーダル部 -->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    
                    <!-- ヘッダー部 -->
                    <header class="modal-header d-flex align-items-center p-2">
                        
                        <!--戻るボタン-->
                        <x-back-button/>

                        <h5 class="modal-title mb-0">タグ</h5>

                    </header>

                    <!-- ボディ部 -->
                    <div class="modal-body">

                        <!-- チェックリスト -->
                        <article>

                            @foreach ($this->tags as $tag)
                                <div class="form-check" wire:key="{{ $tag->id }}">
                                    <!-- 選択したタグのidをselectedTagIdsに格納する -->
                                    <!-- value属性で選択したタグのidを値とし、wire:model.liveでselectedTagIdsに即時同期する -->
                                    <input type="checkbox" wire:model.live="selectedTagIds" 
                                        name="selectedTagIds[]" value="{{ $tag->id }}" id="{{ $tag->id }}"
                                        class="form-check-input">
                                    
                                    <label for="{{ $tag->id }}" class="form-check-label">
                                        {{ $tag->tag_name }}
                                    </label>
                                </div>
                            @endforeach

                        </article>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>