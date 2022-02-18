# Important
This was released before Laravel Sanctum. I would recommend using [Laravel Sanctum](https://laravel.com/docs/master/sanctum) instead.

# multiple-tokens-auth
[![Latest Version on Packagist](https://img.shields.io/packagist/v/livijn/multiple-tokens-auth.svg?style=flat-square)](https://packagist.org/packages/livijn/multiple-tokens-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/livijn/multiple-tokens-auth.svg?style=flat-square)](https://packagist.org/packages/livijn/multiple-tokens-auth)

Adds the ability to use multiple tokens for the auth:api middleware. Useful if you want to allow a user to be logged in to your e.g. SPA, iOS app and android app at the same time. The default token driver only allows one token per user. 

It is possible to end up with a large table when using multiple tokens per user. Therefor we set an expiration date on the tokens. If possible, you should add the `PurgeExpiredApiTokensJob` to your Schedule as the *Step 6* describes. If not, you should somehow take care of the expired tokens.

You may take a look at the example app [multiple-tokens-auth-testapp](https://github.com/Livijn/multiple-tokens-auth-testapp).

## Install
1. Install the package with composer:
    ```bash
    composer require livijn/multiple-tokens-auth
    ```

2. Publish the `multiple-tokens-auth.php` config & migrations:
    ```bash
    php artisan vendor:publish --provider="Livijn\MultipleTokensAuth\MultipleTokensAuthServiceProvider"
    ```
   > By default, the migration is shipped with the field `user_id` that has `unsignedBigInteger`. This needs to be manually changed if you use `uuid` in your User model.

3. Run the migrations:
    ```bash
    php artisan migrate
    ```

4. Set the api guard driver to `multiple-tokens` in the file `config/auth.php`:
    ```php    
    'guards' => [
        // ...
    
        'api' => [
            'driver'   => 'multiple-tokens', // <- Change this FROM token TO multiple-tokens
            
            // ...
        ],
    ],
    ```
   
5. Add the `HasApiTokens` trait to your User model.
   ```php 
   class User extends Authenticatable
   {
       use Notifiable, HasApiTokens;
   
       // ...
   } 
   ```
   
6. *(Optional)* Add the `PurgeExpiredApiTokensJob` to your Schedule at `Console/Kernel.php`.
   ```php
   protected function schedule(Schedule $schedule)
   {
       $schedule->job(PurgeExpiredApiTokensJob::class)->dailyAt('01:00');
   }
   ```

## Usage
You can use this the same way as you would use the [default Laravel token based API authorization](https://laravel.com/docs/master/api-authentication). This package also supports [hashing](https://laravel.com/docs/master/api-authentication#hashing-tokens).

### Sign in
When a user logs in, you should create a new api token by using the `generateApiToken` method.
```php
$user = User::first();
$token = $user->generateApiToken(); // returns ltBKMC8zwnshLcrVh9W07IGuifysDqkyWRt6Z5szYJOrh1mnNPValkAtETj0vtPJdsfDQa4E3Yx0N3QU
```

### Sign out
When you want to log out a user, you can use the `logout` method on the Auth facade. This will delete the token that was used for the current request.
```php
auth()->logout();
// or
Auth::logout();
```

### Purging tokens
To delete all tokens connected to a user, use the `purgeApiTokens` method.
```php
$user = User::first();
$user->purgeApiTokens();
```

## Testing
Run the tests with:

```bash
vendor/bin/phpunit
```

## Credits

- [Fredrik Livijn](https://github.com/livijn)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
