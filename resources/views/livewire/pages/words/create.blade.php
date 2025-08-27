<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    public string $word_name = '';
    public string $description = '';

    //フォーム送信時の処理
    public function word_create(): void
    {
        $validated = $this->validate([
            'word_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        //フォームの入力値とuser_idををwordsテーブルに挿入
        Word::create([
            'word_name' => $this->word_name,
            'description' => $this->description,
            'user_id' => Auth::id(),
        ]);

        // フォームの入力値をリセット
        $this->reset(['word_name', 'description']);

        // wordsテーブルの更新イベントを渡す(words/indexへ)
        $this->dispatch('wordsUpdated');
    }
}; ?>


<!--dispatch('')のイベントの受け取り-->
<div class="container-fluid" x-data="{ showModal: false }" >
    <!--
    dispatch : 派遣する、送り出す
    Livewireの$this->dispatch('')は、DOM上のLivewireコンポーネントのルート要素に指定のイベントを送信する
    -->

    <!--モーダル開くボタン-->
    <button x-on:click="showModal = true"
        class="fab-button btn btn-primary rounded-circle shadow-lg position-fixed bottom-0 end-0 m-4">
        <i class="fas fa-plus"></i><!--「＋」マークの表示-->
    </button>

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <!--x-bind:属性名="条件式"-->
        <!--showModalがtrueの場合、modalクラスの「display: none」をd-blockで打ち消す　※x-showはbootstrapのmodalクラスと競合してしまうため不使用-->
        <!--「tabindex="-1"」モーダルが非表示時、要素がキーボードのタブ移動でフォーカスされないようにします。-->

        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="word_create" x-on:submit="showModal = false">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!--モーダルヘッダー-->
                    <div class="modal-header">
                        <h5 class="modal-title">新しい語句を追加</h5>

                        <!--閉じるボタン-->
                        <button type="button" class="btn-close" x-on:click="showModal = false"aria-label="Close"></button>
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
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
