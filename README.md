# CraftMini Framework

**CraftMini** is a minimalist PHP framework designed for small projects, learning, or as a foundation for personal framework development.

## Features

- **Routing**: Flexible routing system, supporting groups, middleware, named routes, RESTful and API routes.
- **View Engine**: Supports multiple view engines, default is PHP, easily extendable to Blade, Twig, etc.
- **Session & Flash**: Manage sessions, convenient flash messages for one-time notifications.
- **Database Layer**: 
  - Supports multiple database systems (MySQL, SQLite).
  - Supports multiple drivers (mysqli, sqlite3, PDO).
  - Adapter pattern for connections and queries.
  - Query Builder generates dynamic SQL, separated from the adapter.
  - Record Mapper CRUD for each table.
- **Error & Exception Handling**: Error reporting, logging, runtime, parsing, separate exceptions.
- **Helper**: Many utility functions for debugging, var_dump, helper function.
- **Environment Configuration**: Read environment variables from `.env`, supported by [TinyEnv](https://github.com/datahihi1/tiny-env.git).
- **Security**: Supports secure sessions, maintenance mode, security headers, tokens generation (easily extendable).

## Basic Usage

**Routing:**
```php
$router = new Craft\Application\Router();
$router->get('/', [App\Controller\HomeController::class, 'index']);
$router->runInstance();
```

**Controller:**

```php
class HomeController extends Controller {
    public function index() {
        return $this->render('home', ['message' => 'Hello!']);
    }
}
```

**Database:**
```php
$test = new User();
$allUsers = $test->all(); // Get all users with Mapper
```

**View:**
```php
echo Craft\Application\View::render('home', ['message' => 'Hello!']);
```

**Session & Flash:**
```php
Craft\Application\Session::flash('msg', 'Success!'); // Set flash message
echo Craft\Application\Session::getFlash('msg'); // Get and clear flash message
```

## System Requirements
- PHP >= 7.1
- ext-json (For API routes)
- [TinyEnv](https://github.com/datahihi1/tiny-env.git)
- [Composer](https://getcomposer.org/)
## Installation
1. Clone the repository:

```bash
composer install
```

2. With Composer, you can create a new project:

```bash
composer create-project craftphp/mini-skeleton your-project-name
```

## Contribution
Fork, create a pull request or open an issue to contribute ideas or report bugs.
CraftMini is suitable for those who want to learn how to build a PHP framework from scratch or as a foundation for small projects, demos, and experimenting with new ideas.