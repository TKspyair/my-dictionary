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
    public string $wordName = '';

    public string $wordDescription = '';

    public $checkedTags = null;

    // バリデーションルール
    public function rules()
    {
        return [
            'wordName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('words', 'word_name'),
            ],
            'wordDescription' => [
                'required',
                'string',
            ],
        ];
    }

    // エラーメッセージ
    public function messages()
    {
        return [
            'wordName.required' => '語句は必須です。',
            'wordName.string' => '語句は文字列で入力してください。',
            'wordName.max' => '語句は255文字以内で入力してください。',
            'wordName.unique' => 'この語句は既に使用されています。',
            'wordDescription.required' => '説明は必須です。',
            'wordDescription.string' => '説明は文字列で入力してください。',
        ];
    }

    //tags.check-listから引数を渡される
    #[On('send-checked-tag-ids')]
    public function loadCheckedTags(array $checkedTagIds)
    {
        //引数のタグidをもつタグコレクションを取得
        $this->checkedTag = Tag::whereIn('id', $checkedTagIds)->get();
    }

    //フォーム送信時の処理
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
        $word->tags()->attach($this->checkedTags);
        // tags() : Wordモデルのクラスメソッド。
        /*attach() :
        中間テーブルへのレコード挿入: 中間テーブルに、関連付けに必要な外部キーのペア（例：word_idとtag_id）を自動的に挿入
        IDの自動取得: $word->tags()->attach(...)のように呼び出された際に、呼び出し元のモデル（この場合は $word）のIDを自動的に取得
        */

        $this->clearForm();

        $this->dispatch('update-words');
    }

    // フォームをクリア
    public function clearForm()
    {
        $this->resetValidation(); 
        $this->reset(['wordName', 'wordDescription']);
        //resetValidation() : livewireのメソッド、バリデーションのエラーメッセージをクリアする
        //変数とプロパティの違い：プロパティはクラスやオブジェクトに属する変数、変数はクラスやオブジェクトに属さない、データを保持するもの
        //関数とメソッドの違い：メソッドはクラスやオブジェクトに属する、関数はクラスやオブジェクトに属さない、処理を行うもの
        //reset()はプロパティに対して動作するメソッドなので、'プロパティ名'を引数に渡す
    }
}; ?>



<div class="container-fluid" x-data="{ showModal: false }" 
    x-on:open-words-create-modal.window="showModal = true"
    x-on:close-all-modal.window="showModal = false">

    <!-- モーダル本体 -->
    <div x-bind:class="{ 'modal': true, 'd-block': showModal }" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <form wire:submit.prevent="createWord">
                    <!--「.prevent」：ブラウザのデフォルトのフォーム送信を無効化し、ページのリロードをなくす-->

                    <!-- ヘッダー -->
                    <div class="modal-header d-flex align-items-center p-2">

                        <!--戻るボタン-->
                        <x-back-button wire:click="clearForm"/>

                        <h5 class="modal-title mb-0">新規作成</h5>
                    </div>

                    <!-- ボディ -->
                    <div class="modal-body">
                        
                        <!-- 語句名フィールド -->
                        <x-form-input wire:model="wordName" class="fs-5 fw-bold" placeholder="語句"/>

                        <!-- 語句説明フィールド -->
                        <x-form-textarea wire:model="wordDescription" placeholder="説明"/>

                        <!-- タグ選択 -->
                        <div>
                            <span x-on:click="$dispatch('open-tags-check-list')">
                                タグ選択
                            </span>

                            @if ($this->checkedTags)
                                <div class="d-flex">
                                    @foreach ($this->checkedTags as $checkedTag)
                                        <span class="badge bg-secondary me-1" wire:key="{{ $checkedTag->id }}">
                                            {{ $checkedTag->tag_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!--保存ボタン-->
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary">保存</button>
                    </div>
                </form>
            </div>
        </div>
        @livewire('pages.tags.check-list')
    </div>
</div>
