# multiple-tokens-auth

[![Latest Version on Packagist](https://img.shields.io/packagist/v/livijn/multiple-tokens-auth.svg?style=flat-square)](https://packagist.org/packages/livijn/multiple-tokens-auth)
[![Total Downloads](https://img.shields.io/packagist/dt/livijn/multiple-tokens-auth.svg?style=flat-square)](https://packagist.org/packages/livijn/multiple-tokens-auth)

Adds the ability to use multiple tokens for the auth:api middleware. Useful if you want to allow a user to be logged in to your e.g. SPA, iOS app and android app at the same time. The default token driver only allows one token per user. 

## Install
1. Install the package with composer:
    ```
    composer require livijn/multiple-tokens-auth
    ```

2. Publish the migrations:
    ```
    php artisan vendor:publish --provider="Livijn\MultipleTokensAuth\MultipleTokensAuthServiceProvider"
    ```

3. Run the migrations:
    ```
    php artisan migrate
    ```

4. Set the api guard driver to `multiple-tokens` in the file `config/auth.php`:
    ```    
    'guards' => [
        // ...
    
        'api' => [
            'driver'   => 'multiple-tokens', // <- Change this FROM token TO multiple-tokens
            
            // ...
        ],
    ],
    ```
   
5. Add the `HasApiTokens` trait to your User model.
   ``` 
   class User extends Authenticatable
   {
       use Notifiable, HasApiTokens;
   
       // ...
   } 
   ```

## Usage
You can use this the same way as you would use the [default Laravel token based API authorization](https://laravel.com/docs/master/api-authentication). This package also supports [hashing](https://laravel.com/docs/master/api-authentication#hashing-tokens).

### Sign in
When a user logs in, you should create a new api token by using the `generateApiToken` method.
```
$user = User::first();
$token = $user->generateApiToken(); // returns ltBKMC8zwnshLcrVh9W07IGuifysDqkyWRt6Z5szYJOrh1mnNPValkAtETj0vtPJdsfDQa4E3Yx0N3QU
```

### Sign out
When you want to log out a user, you can use the `logout` method on the Auth facade. This will delete the token that was used for the current request.
```
auth()->logout();
// or
Auth::logout();
```

### Purging tokens
To delete all tokens connected to a user, use the `purgeApiTokens` method.
```
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
