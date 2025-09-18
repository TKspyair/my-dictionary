<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

class VoltServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        #
    }



    /*
    Livewire Voltコンポーネントを2つの異なるディレクトリからロードできるようにすることです。

resources/views/livewire: Livewireのデフォルトのコンポーネントディレクトリ

resources/views/pages: 追加で登録されたVoltコンポーネントディレクトリ

pagesディレクトリにフルページコンポーネントを配置し、livewireディレクトリに部分的なコンポーネントを配置するなど、ファイルの管理を整理できます。
    */
    public function boot(): void
    {
        Volt::mount([
            config('livewire.view_path', resource_path('views/livewire')),
            resource_path('views/pages'),
        ]);
        # Volt::mount([...]) : Voltコンポーネントが含まれるディレクトリを登録し、Livewireは、ここで指定されたディレクトリ内にあるBladeファイルをVoltコンポーネントとして扱う
        # config('config配下の取得したい値への相対パス', デフォルト値): ここではconfig/livewire/view_pathの値を取得
        # resource_path(...) :  指定したパスをVoltコンポーネントを読み込むためのパスとして追加登録する
    }
}
