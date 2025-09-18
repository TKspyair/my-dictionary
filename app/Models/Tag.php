<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'tag_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function words()
    {
        return $this->belongsToMany(Word::class, 'word_tag', 'tag_id', 'word_id');
        #$this->belongsToMany(関連づけるモデルクラス, '中間テーブル名', '関連付ける元のモデルの外部キー',関連付け先のモデルの外部キー )
    }
}
