<?php

use App\Models\Tag;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout; //#[Layout('layouts.words-app')]の使用
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.words-app')] class extends Component 
{
    //ユーザーのもつ全てのタグのコレクション
    public $tags = null;

    //チェックしたタグのid
    public $checkedTagIds = [];

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

    //words.detail-and-editより
    #[On('edit-checked-word-tag-ids')]
    public function editCheckedTags(array $checkedwordTagIds)
    {
        //受け取ったidを格納
        $this->checkedTagIds = $checkedwordTagIds;
    }

    //$checkedTagIdsの更新時に実行 配列で他に渡す
    public function updatedCheckedTagIds()
    {
        $this->dispatch('return-checked-tag-ids', checkedTagIds: $this->checkedTagIds);
    }
};
?>

<div x-data="{ showCheckList: false }" x-on:open-tags-check-list.window="showCheckList = true">
    <div x-bind:class="{ 'modal': true, 'd-block': showCheckList }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <!-- ヘッダー -->
                <div class="modal-header d-flex align-items-center">
                    <!--戻るボタン-->
                    <span x-on:click="showCheckList = false" data-bs-dismiss="modal" class="p-0 me-3">
                        <i class="bi bi-arrow-left fs-4"></i>
                    </span>

                    <h5 class="modal-title mb-0">タグ</h5>
                </div>

                <!-- ボディ -->
                <div class="modal-body">

                    <!-- チェックリスト -->
                    <div>
                        @foreach ($this->tags as $tag)
                            <div class="form-check">
                                <!-- value属性でチェック時にチェックしたタグのIDをwire:modelに渡す -->
                                <input class="form-check-input" type="checkbox" name="checkedTagIds[]"
                                    value="{{ $tag->id }}" id="{{ $tag->id }}"
                                    wire:model.live="checkedTagIds">
                                <label class="form-check-label" for="{{ $tag->id }}">
                                    {{ $tag->tag_name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
