<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Models\Word;
use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    //======================================================================
    // プロパティ
    //======================================================================
    public string $wordName = '';

    public string $wordDescription = '';

    # 選択されたタグのコレクション
    public $selectedTags;

    //======================================================================
    // 初期化
    //======================================================================

    public function mount()
    {
        #プロパティの宣言時には空のコレクションを入れられないので、ここで代入
        $this->selectedTags = collect();
    }
    //======================================================================
    // バリデーション
    //======================================================================
    # バリデーションルール
    public function rules()
    {
        return [
            'wordName' => ['required', 'string', 'max:255', Rule::unique('words', 'word_name')],
            'wordDescription' => ['string'],
        ];
    }

    # エラーメッセージ
    public function messages()
    {
        return [
            'wordName.required' => '語句は必須です。',
            'wordName.string' => '語句は文字列で入力してください。',
            'wordName.max' => '語句は255文字以内で入力してください。',
            'wordName.unique' => 'この語句は既に使用されています。',
            'wordDescription.string' => '説明は文字列で入力してください。',
        ];
    }

    //======================================================================
    // メソッド
    //======================================================================

    //-----------------------------------------------------
    // CRUD機能
    //-----------------------------------------------------
    # 語句データをDBに登録する
    public function createWord(): void
    {
        $validated = $this->validate();

        $this->dispatch('close-all-modal');

        $word = Word::create([
            'word_name' => $validated['wordName'],
            'description' => $validated['wordDescription'],
            'user_id' => Auth::id(),
        ]);

        //attouch()によりtag_idとword_idが自動的に結び付けられ、中間テーブルに挿入される
        $word->tags()->attach($this->selectedTags);
        // tags() : Wordモデルのクラスメソッド。
        /*attach() :
            中間テーブルへのレコード挿入: 中間テーブルに、関連付けに必要な外部キーのペア（例：word_idとtag_id）を自動的に挿入
            IDの自動取得: $word->tags()->attach(...)のように呼び出された際に、呼び出し元のモデル（この場合は $word）のIDを自動的に取得
            */

        $this->clearForm();

        $this->dispatch('update-words');
    }

    # フォームをクリア
    public function clearForm()
    {
        $this->reset(['wordName', 'wordDescription', 'selectedTags']);
        $this->resetValidation();
        //resetValidation() : livewireのメソッド、バリデーションのエラーメッセージをクリアする
        //変数とプロパティの違い：プロパティはクラスやオブジェクトに属する変数、変数はクラスやオブジェクトに属さない、データを保持するもの
        //関数とメソッドの違い：メソッドはクラスやオブジェクトに属する、関数はクラスやオブジェクトに属さない、処理を行うもの
        //reset()はプロパティに対して動作するメソッドなので、'プロパティ名'を引数に渡す
    }

    //-----------------------------------------------------
    // タグ選択専用　tags.check-list
    //-----------------------------------------------------
    # 選択したタグのidを渡される
    #[On('send-selected-tag-ids')]
    public function loadSelectedTags(array $selectedTagIds)
    {
        //引数のタグidをもつタグコレクションを取得
        $this->selectedTags = Tag::whereIn('id', $selectedTagIds)->get();
    }
}; ?>



<div class="container-md" x-data="{ showModal: false }" x-on:open-words-create-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <div x-show="showModal">

        <!-- モーダル部 -->
        <div class="modal d-block" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">

                <div class="modal-content">

                    <!-- ヘッダー部 -->
                    <header class="modal-header d-flex justify-content-between align-items-center p-2">

                        <div class="d-flex align-items-center">
                            <!--戻るボタン-->
                            <x-back-button wire:click="clearForm" />

                            <h5 class="modal-title mb-0">新規作成</h5>
                        </div>

                        <!-- タグ選択ボタン -->
                        <div>
                            <button type="button" x-on:click="$dispatch('open-tags-check-list')">
                                タグ選択
                            </button>

                            @livewire('pages.tags.check-list')
                        </div>
                    </header>

                    <!-- ボディ部 -->
                    {{-- 
                    * mx-2 mb-2: 入力フィールドが画面端まで広がらないように制限
                    * d-flex flex-grow-1 w-100: flex-grow-1で縦方向に要素を広げ、w-100で横方向にも広げる
                    --}}
                    <div class="modal-body d-flex flex-grow-1 mx-2 mb-2">
                        <form id="create-word-form" class="d-flex flex-column w-100 " wire:submit.prevent="createWord">

                            <!-- 語句名フィールド -->
                            <div class="position-relative">
                                <x-form-input wire:model="wordName" />
                            </div>

                            <!-- 説明フィールド -->
                            <div class="position-relative d-flex flex-grow-1 mt-5">
                                <x-form-textarea wire:model="wordDescription"/>
                            </div>
                        </form>
                    </div>

                    <!-- 作成ボタン -->
                    {{-- 
                    ** form要素外にあるinput要素やsubmit属性をもつbutton要素はform要素と連動しないが、以下の属性を使用すると関連付けれる
                    * form="form要素のid": 、form属性を使用することで任意のform要素に関連付けれる
                    --}}
                    <div class="modal-footer d-flex justify-content-center align-items-center m-3 p-0">
                        <x-submit-button form="create-word-form">
                            <span>作成</span>
                        </x-submit-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
