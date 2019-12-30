<?php
namespace Livijn\MultipleTokensAuth\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'expired_at' => 'datetime'
    ];

    protected $primaryKey = 'token';

    public $incrementing = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('multiple-tokens-auth.table'));
    }

    public function shouldExtendLife()
    {
        if ($this->hasExpired()) {
            return false;
        }

        return $this->expired_at->isBefore(
            now()->addDays(config('multiple-tokens-auth.token.extend_life_at'))
        );
    }

    public function hasExpired()
    {
        return $this->expired_at->isPast();
    }

    public function scopeWhereHasExpired(Builder $query)
    {
        return $query->whereDate('expired_at', '<', now());
    }

    public function scopeWhereHasNotExpired(Builder $query)
    {
        return $query->whereDate('expired_at', '>=', now());
    }
}
