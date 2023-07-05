<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SongRequest extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'creator_id',
        'artist_type_id', 'genre_id', 'song_type_id', 'language_id',
        'duration_id', 'purpose_id',
        'first_name', 'last_name', 'email',
        'request_status', 'page_status', 'approval_status',
        'sender', 'receiver',
        'user_story', 'estimate_date',
        'delivery_date', 'approved_at',
    ];

    protected $appends = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'creator_id'            => 'string',
        'artist_type_id'        => 'string',
        'genre_id'              => 'string',
        'song_type_id'          => 'string',
        'language_id'           => 'string',
        'duration_id'           => 'string',
        'purpose_id'            => 'string',
        'first_name'            => 'string',
        'last_name'             => 'string',
        'email'                 => 'string',
        'request_status'        => 'string',
        'page_status'           => 'string',
        // 'approval_status'       => '',
        'sender'                => 'string',
        'receiver'              => 'string',
        'user_story'            => 'string',
        'delivery_date'         => 'timestamp',
        'approved_at'           => 'timestamp',
        'estimate_date'         => 'integer',
    ];
}
