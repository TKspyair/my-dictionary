<?php

use App\Models\Tag;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.words-app')] class extends Component 
{
    //ユーザーのもつ全てのタグのコレクション
    public $tagColl;

    //チェックしたタグのid
    public $checkedTagIds = [];

    //初期読み込み時に実行
    public function mount()
    {
        $this->loadTagColl();
    }

    //　タグ一覧の更新
    #[On('update-tag-list')]
    public function loadTagColl(): void
    {
        $this->tagColl = Auth::user()->tags()->orderBy('created_at', 'desc')->get();
    }

    //words.detail-and-editより
    #[On('send-checked-tag-ids')]
    public function setCheckedTagIds(array $checkedwordTagIds)
    {
        //受け取ったidを格納
        $this->checkedTagIds = $checkedwordTagIds;
    }

    //$checkedTagIdsの更新時に実行 配列で他に渡す
    public function updatedCheckedTagIds()
    {
        $this->dispatch('send-checked-tag-ids', checkedTagIds: $this->checkedTagIds);
    }
};
?>

<section x-data="{ showCheckList: false }" x-on:open-tags-check-list.window="showCheckList = true">

    <div x-show="showCheckList">
        <div class="modal d-block'" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <!-- ヘッダー -->
                    <header class="modal-header d-flex align-items-center p-2">
                        <!--戻るボタン-->
                        <button x-on:click="showCheckList = false" class="btn btn-link text-dark border-0 p-0 me-3">
                            <i class="bi bi-arrow-left fs-4"></i>
                        </button>

                        <h5 class="modal-title mb-0">タグ</h5>
                    </header>

                    <!-- ボディ -->
                    <div class="modal-body">

                        <!-- チェックリスト -->
                        <article>
                            @foreach ($this->tagColl as $tag)
                                <div class="form-check" wire:key="{{ $tag->id }}">
                                    <!-- value属性でチェック時にチェックしたタグのIDをwire:modelに渡す -->
                                    <input type="checkbox" wire:model.live="checkedTagIds" 
                                        name="checkedTagIds[]" value="{{ $tag->id }}" id="{{ $tag->id }}"
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
