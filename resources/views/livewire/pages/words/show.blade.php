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
    public string $word_name = '';
    public string $description = '';

    // イベントを受け取り、モーダルを表示
    #[On('showWordDetail')]
    public function showWordDetail(int $id): void
    {
        //wordsテーブルから指定のIDをもつレコードを取得する
        $this->editingWord = Auth::user()->words()->find($id);

        //上記で取得したレコードから、指定のカラムを取得
        $this->word_name = $this->editingWord->word_name;
        $this->description = $this->editingWord->description;

        // Alpine.jsにモーダルを開くイベントとデータを送信
        $this->dispatch('open-word-detail-modal');
    }

    //フォーム送信時の処理
    public function wordUpdate(): void
    {
        $validated = $this->validate([
            'word_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        //新しい入力値をwordsテーブルに挿入
        $this->editingWord->update([
            'word_name' => $this->word_name,
            'description' => $this->description,
        ]);

        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('wordsUpdated');
    }

    //削除ボタンを押すと実行
    public function wordDelete(): void
    {
        // 現在編集中の語句をDBから削除
        $this->editingWord->delete();
        //フォーム内の記述もクリア
        $this->reset();

        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('wordsUpdated');
    }
}; ?>

<!--ロジックからイベントとデータを受信-->
<div class="container-lg" x-data="{ showModal: false }" x-on:open-word-detail-modal="showModal = true;">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <!--x-bind:属性名="条件式"-->
        <!--showModalがtrueの場合、modalクラスの「display: none」をd-blockで打ち消す　※x-showはbootstrapのmodalクラスと競合してしまうため不使用-->
        <!--「tabindex="-1"」モーダルが非表示時、要素がキーボードのタブ移動でフォーカスされないようにします。-->

        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="wordUpdate">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!--モーダルヘッダー-->
                    <div class="modal-header">
                        <!--閉じるボタン-->
                        <button type="button" class="btn-close" x-on:click="showModal = false"
                            aria-label="Close"></button>
                    </div>

                    <!-- モーダルボディ -->
                    <div class="modal-body">

                        <!--語句名フィールド-->
                        <div class="mb-3">
                            <label for="word_name" class="form-label">語句</label>
                            <input id="word_name" name="word_name" type="text" class="form-control"
                                wire:model="word_name" required>
                            <!--form-control:フォーム入力欄に一貫したスタイルとレイアウトを適用するBootstrapクラス-->
                            <!--wire: model="プロパティ"：フロント側のプロパティの状態をLivewireコンポーネント(サーバー側)にリアルタイムに同期する-->
                            @error('word_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!--語句説明フィールド-->
                        <div class="mb-3">
                            <label for="description" class="form-label">説明</label>
                            <textarea id="description" name="description" class="form-control" wire:model="description" rows="3" required></textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!--保存ボタン-->
                    <div class="modal-footer d-flex justify-content-between ">
                        <button type="button" class="btn btn-danger" wire:click="wordDelete"
                            wire:confirm="本当にこの投稿を削除しますか？"x-on:click="showModal = false">削除</button>
                        <button type="submit" class="btn btn-primary" x-on:click="showModal = false">更新</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
