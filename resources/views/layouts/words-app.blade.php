<!DOCTYPE html>
<!--Laravelの.envファイルの言語設定を反映-->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!--文字コード　UTF-8は様々な文字を正しく表示できる-->
    <meta charset="UTF-8">
    <!--デバイスごとの表示領域の設定-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--リファラー(ユーザーがどこから現在のウェブページにアクセスしてきたかを示す情報、HTTPヘッダにある)の脆弱性を防ぐ-->
    <meta name="referrer" content="strict-origin-when-cross-origin" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <!--Google Fontsとの接続を事前に確立し、フォントの読み込みを高速化するための設定-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!--Noto Sans JP 日本語の表示に適し、フォントの太さ[400(標準),500(中)]、幅広い用途に使えるフォント-->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Bootstrap icon-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Scripts -->
    @vite(['resources/js/app.js','resources/css/app.css'])
</head>

{{-- 
* 100svh: アドレスバーが表示されているときの最小の高さ
* 最小の高さ: CSSにおいて最も狭い状態の高さのこと、これ以上画面が狭くなることがなくなるため、レイアウトの安定性が高まる
* 100vh: 古いブラウザなどで100svhが読み込めないとき用
* 
* containerクラスは子ファイルで設定する
> ページごとに最適な幅を設定するため 
--}}
<body>
    <!-- フラッシュメッセージ機能 -->
    <div class="position-fixed top-0 start-50 translate-middle-x mt-3 z-3" style="width: 95%;" 
        x-data="{ showFlashMessage: false, message: '', type: '' }"
        x-show="showFlashMessage"
        x-init="
        {{-- フラッシュメッセージイベントを受け取る --}}
        window.addEventListener('flash-message', e => {
            message = e.detail.message;
            type = e.detail.type
            showFlashMessage = true;

            {{-- 5秒後にフラッシュメッセージを非表示にする --}}
            setTimeout(() => showFlashMessage = false, 3000); 
    })">
    
        <!-- フラッシュメッセージ表示部 -->
        <span class="d-flex justify-content-center alert p-2" x-text="message" 
            x-bind:class="{ 
                'alert-success': type === 'success',
                'alert-danger' : type === 'error',
                'alert-info'   : type === 'info',
                'alert-dark'   : type === 'dark',
                'alert-warning': type === 'warning'}"
            x-on:click="showFlashMessage = false">
        </span>  
    </div>
    {{ $slot }}
</body>

</html>
