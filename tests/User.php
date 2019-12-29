<?php
namespace Livijn\MultipleTokensAuth\Test;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Livijn\MultipleTokensAuth\Traits\HasApiTokens;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Authorizable, Authenticatable, HasApiTokens;

    protected $fillable = ['email'];
    public $timestamps = false;
    protected $table = 'users';
}
