<!-- welcome.blade.php 22行目に挿入されるナビゲーションバー -->

<div class="sm:fixed sm:top-0 sm:right-0 p-6 text-end z-10">
    @auth
        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" wire:navigate>Dashboard</a>
    @else
        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" wire:navigate>Log in</a>

        <a href="{{ route('register') }}" class="ms-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500" wire:navigate>Register</a>
    
    @endauth

    <!--
    「@ auth」: ユーザーが認証済みの場合、内部の処理を実行する
    「wire:navigate」 : リンクをクリックした際に、非同期的にコンテンツを読み込む 
    -->
</div>
