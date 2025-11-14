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
            'wordName' => ['string', 'max:255',],
            'wordDescription' => ['string'],
        ];
    }

    # エラーメッセージ
    public function messages()
    {
        return [
            'wordName.string' => '語句は文字列で入力してください。',
            'wordName.max' => '語句名は255文字以内で入力してください。',
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
    public function createWord(): void {
        
        # 語句名と説明どちらも未入力なら登録処理を中断する
        if(empty($this->wordName) && empty($this->wordDescription)) {
            
            $this->clearForm();

            #  空のメッセージを削除したというメッセージを表示するイベントの発火
            /**
             * message: フラッシュメッセージの内容
             * type: フラッシュメッセージの色指定(例: info →　青) 
            */
            $this->dispatch('flash-message', message: '空のメモを削除しました', type: 'dark');
            
            return;
        }

        #　説明が記述されているが語句名が空の場合、語句名を「未入力」とする
        if(empty($this->wordName) && !empty($this->wordDescription)) {
            $this->wordName = '未入力';
        }

        $validated = $this->validate();

        $this->dispatch('close-all-modal');

        $word = Word::create([
            'word_name' => $validated['wordName'],
            'description' => $validated['wordDescription'],
            'user_id' => Auth::id(),
        ]);

        /** 
         * attach() :
         * 1 中間テーブルへのレコード挿入: 中間テーブルに、関連付けに必要な外部キーのペア（例：word_idとtag_id）を自動的に挿入
         * 2 IDの自動取得: $word->tags()->attach(...)のように呼び出された際に、呼び出し元のモデル（この場合は $word）のIDを自動的に取得
         * attouch()によりtag_idとword_idが自動的に結び付けられ、中間テーブルに挿入される
         */
        $word->tags()->attach($this->selectedTags);

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
                            <!-- 戻るボタン ※フォーム送信機能をもつ-->
                            {{-- 
                            ** form要素外にあるinput要素やsubmit属性をもつbutton要素はform要素と連動しないが、以下の属性を使用すると関連付けれる
                            * form="form要素のid": 、form属性を使用することで任意のform要素に関連付けれる
                            --}}
                            <x-back-button form="create-word-form" wire:click="createWord"/>

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

                </div>
            </div>
        </div>
    </div>
</div>
