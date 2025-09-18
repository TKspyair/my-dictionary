<?php

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Word;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On; 

new #[Layout('layouts.words-app')] class extends Component 
{
    //======================================================================
    // Property(プロパティ)
    //======================================================================
    
    /** wordColl() : Wordコレクションを格納
     * #[Computed]の動作タイミング
     * - 1 プロパティにアクセスがあったとき($this->wordCollの記載のある処理があった時)
     * - 2 依存するプロパティに変更があったとき(今回は依存するプロパティがないため機能しない)
     * > 解決策として、#On[]により、イベントを受け取り、wordCollを更新する
    */
    #[Computed]
    #[On('update-words')] 
    public function wordColl()
    {
        return Auth::user()->words()->orderBy('created_at', 'desc')->get();
    }
    
    //======================================================================
    // method(メソッド)
    //======================================================================

    /**　sendWordInstance(Word $word)　
     * - 語句リストの語句名をクリック時に実行
     * - クリックした語句名のモデルインスタンスをwords.detail-editに送信
     */
    public function sendWordInstance(Word $word): void
    {
        $this->dispatch('send-word-instance', word: $word)
        ->to('pages.words.detail-edit');
    }
}; ?>

<!--語句リスト-->
<section>
    <ul class="list-group">
        @foreach ($this->wordColl as $word)
            <li class="list-group-item d-flex justify-content-between align-items-center" wire:key="{{ $word->id }}">
                <button wire:click="sendWordInstance({{ $word }})"
                    class="btn btn-link text-dark border-0 p-0 mb-0 text-decoration-none" >
                    {{ $word->word_name }}
                </button> 
            </li>
        @endforeach
    </ul>
</section>

