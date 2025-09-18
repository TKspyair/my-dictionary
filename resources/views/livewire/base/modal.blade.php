<?php

use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new #[Layout('layouts.words-app')] class extends Component 
{
    
}; ?>


<div class="container-fluid" x-data="" 
    x-on:close-all-modal.window="showModal= false"
    >

    <!--モーダル部分-->
    <div x-show="showModal">
        <div class="modal d-block" tabindex="-1"> <!-- d-blockでmodal(display:none)を打ち消す -->
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">

                    <!--ヘッダー-->
                    <div class="modal-header d-flex align-items-center p-2">

                        <!--戻るボタン-->
                        <x-back-button data-bs-toggle="offcanvas" data-bs-target="#menu-index-offcanvas"/>

                        <h5 class="modal-title mb-0">タグの編集</h5>
                    </div>

                    <!-- ボディ -->
                    <div class="modal-body">

                            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>