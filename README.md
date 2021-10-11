# CiAuth

after ``composer request anwarqasem/ci_auth``

## Instalation & Configuration

### JWT Token Settings

Add in you ``.env`` file:

```dotenv
#--------------------------------------------------------------------
# JWT Token
#--------------------------------------------------------------------
JWT_SECRET=<change:some_random_words_or_chars>
JWT_ISS=<change:Issuer>
JWT_AUD=<change:Audience>
JWT_SUB=<change:Subject>

```

### Filters ``app/Config/Filters.php``

Find ``$aliases`` array and add ``'is_logged_in' => AuthLibrary::class,``. On a new CI installation it should looks
something like this:

```injectablephp
 public $aliases = [
        'csrf'     => CSRF::class,
        'toolbar'  => DebugToolbar::class,
        'honeypot' => Honeypot::class,
        'is_logged_in' => AuthLibrary::class,
    ];
```

Find ``$globals`` array and add ``'is_logged_in' => [ 'except' => [ 'auth/*', 'home/*', '/' ] ],``. On a new CI installation it should looks
something like this:

```injectablephp
public $globals = [
        'before' => [
            'honeypot',
            'csrf',
            'is_logged_in' => [
                'except' => [
                    'auth/*',
                    'home/*',
                    '/'
                ]
            ],
        ],
        'after' => [
            'toolbar',
            'honeypot',
        ],
    ];
```

