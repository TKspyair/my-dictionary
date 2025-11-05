<?php

/** PHPDoc
 *  ファイルの機能: menu/indexで表示されるタグ名をクリックすると、そのタグ名に紐づいた語句を表示する機能を扱うファイルです。
*/
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Tag;
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

    # 選択したtag/indexで表示されたタグのインスタンスを格納する
    public $selectedTag;

    # 選択されたタグに紐づく語句のコレクション
    public $words;

    public function mount()
    {
        $this->selectedTag = new Tag();
        $this->words = collect();
    }

    //======================================================================
    // method(メソッド)
    //======================================================================

    //　選択されたタグに紐づく語句の取得
    #[On('send-selected-tag-instance')]
    public function fetchSelectedWords(Tag $tag): void // FIXME: モーダルが開いてから、画面に語句が表示される速度が遅い
    {
        $this->selectedTag = $tag;
        $this->words = $this->selectedTag->words;
    }

    public function clear()
    {
        $this->selectedTag = new Tag();
        $this->words = collect();
    }

}; ?>

<section class="container-fluid" x-data="{ showModal: false }" 
    x-on:open-tag-words-show-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal= false">

    <!--モーダル部分-->
    <div x-show="showModal">
        {{-- d-blockでmodal(display:none)を打ち消し、モーダルを常時表示する　※実際の表示切替は「x-show」が担う --}}
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <!--ヘッダー-->
                    <header class="modal-header d-flex align-items-center p-2">

                        <!--戻るボタン-->
                        <x-back-button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas" 
                            wire:click="clear"/>

                        <h5 class="modal-title mb-0">{{ $this->selectedTag->tag_name }}</h5>
                    </header>

                    <!-- ボディ -->
                    <div class="modal-body">

                        <!-- タグに紐づけられた語句一覧 -->
                        <article>
                            <ul class="list-group">
                                @foreach ($this->words as $word)
                                    <li 
                                        wire:key="{{ $word->id }}"
                                        class="list-group-item d-flex justify-content-between align-items-center">
                                        <button wire:click="sendWordInstance({{ $word }})"
                                            class="btn btn-link text-dark border-0 p-0 mb-0 text-decoration-none">
                                            {{ $word->word_name }}
                                        </button>
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

