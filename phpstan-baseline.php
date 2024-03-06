<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Function App\\\\Console\\\\failingFunction\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/FailCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\FailCommand\\:\\:__invoke\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/FailCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Hello\\:\\:test\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Hello.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Hello\\:\\:world\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Hello.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Console\\\\Test\\:\\:test\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Console\\\\Test\\:\\:\\$output is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Console/Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\FailController\\:\\:__invoke\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/FailController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:__construct\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:getCookies\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:getQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:post\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\RequestForValidationController\\:\\:resolveQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Controllers\\\\RequestForValidationController\\:\\:\\$query type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/RequestForValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Controllers\\\\ValidationController\\:\\:__invoke\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Controllers/ValidationController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:data\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:escape\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:extends\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:include\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:parseSlots\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:\\$extendsParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Books\\\\BookDetailView\\:\\:\\$rawParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/BookDetailView.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:find\\(\\) should return App\\\\Modules\\\\Books\\\\Models\\\\Author but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:new\\(\\) should return App\\\\Modules\\\\Books\\\\Models\\\\Author but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Author\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Author.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:find\\(\\) should return App\\\\Modules\\\\Books\\\\Models\\\\Book but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:new\\(\\) should return App\\\\Modules\\\\Books\\\\Models\\\\Book but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Book\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Book.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:find\\(\\) should return App\\\\Modules\\\\Books\\\\Models\\\\Chapter but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:new\\(\\) should return App\\\\Modules\\\\Books\\\\Models\\\\Chapter but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Models\\\\Chapter\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Models/Chapter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:__construct\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:getCookies\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:getQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:post\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:resolveQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Books\\\\Requests\\\\CreateBookRequest\\:\\:\\$query type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Books/Requests/CreateBookRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$view of method Tempest\\\\Http\\\\Response\\:\\:view\\(\\) expects Tempest\\\\View\\\\View, string given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormController.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:__construct\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:getCookies\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:getQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:post\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Form\\\\FormRequest\\:\\:resolveQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Form\\\\FormRequest\\:\\:\\$query type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Form/FormRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Home\\\\HomeView\\:\\:data\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Home\\\\HomeView\\:\\:escape\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Home\\\\HomeView\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Home\\\\HomeView\\:\\:extends\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Home\\\\HomeView\\:\\:include\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Home\\\\HomeView\\:\\:parseSlots\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Home\\\\HomeView\\:\\:\\$extendsParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Home\\\\HomeView\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Home\\\\HomeView\\:\\:\\$rawParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Home/HomeView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:__construct\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:getCookies\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:getQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:post\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:resolveQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Modules\\\\Posts\\\\PostRequest\\:\\:\\$query type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Modules/Posts/PostRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModel\\:\\:data\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModel\\:\\:escape\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModel\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModel\\:\\:extends\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModel\\:\\:include\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModel\\:\\:parseSlots\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Views\\\\ViewModel\\:\\:\\$extendsParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Views\\\\ViewModel\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Views\\\\ViewModel\\:\\:\\$rawParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModel.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModelWithResponseData\\:\\:data\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModelWithResponseData\\:\\:escape\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModelWithResponseData\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModelWithResponseData\\:\\:extends\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModelWithResponseData\\:\\:include\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Method App\\\\Views\\\\ViewModelWithResponseData\\:\\:parseSlots\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Views\\\\ViewModelWithResponseData\\:\\:\\$extendsParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Views\\\\ViewModelWithResponseData\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Property App\\\\Views\\\\ViewModelWithResponseData\\:\\:\\$rawParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/ViewModelWithResponseData.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\View\\\\GenericView\\:\\:\\$title\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/base.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\View\\\\GenericView\\:\\:\\$title\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/baseWithNamedSlot.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\View\\\\GenericView\\:\\:\\$prop\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/include-child.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\View\\\\GenericView\\:\\:\\$prop\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/include-parent.php',
];
$ignoreErrors[] = [
	'message' => '#^Variable \\$this might not be defined\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/app/Views/index.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\View\\\\GenericView\\:\\:\\$name\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/overview.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\View\\\\GenericView\\:\\:\\$property\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/app/Views/rawAndEscaping.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Application\\\\ConsoleApplication\\:\\:__construct\\(\\) has parameter \\$args with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Application/ConsoleApplication.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Application\\\\ConsoleApplication\\:\\:resolveParameters\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Application/ConsoleApplication.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Bootstraps\\\\DiscoveryLocationBootstrap\\:\\:addDiscoveryLocations\\(\\) has parameter \\$discoveredLocations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Bootstraps/DiscoveryLocationBootstrap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Bootstraps\\\\DiscoveryLocationBootstrap\\:\\:discoverAppNamespaces\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Bootstraps/DiscoveryLocationBootstrap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Bootstraps\\\\DiscoveryLocationBootstrap\\:\\:discoverInstalledPackageLocations\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Bootstraps/DiscoveryLocationBootstrap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Bootstraps\\\\DiscoveryLocationBootstrap\\:\\:loadJsonFile\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Bootstraps/DiscoveryLocationBootstrap.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Commands\\\\CommandBus\\:\\:getHistory\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/CommandBus.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Commands\\\\CommandHandler\\:\\:__unserialize\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/CommandHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Commands\\\\GenericCommandBus\\:\\:getHistory\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/GenericCommandBus.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Console\\\\Commands\\\\DiscoveryClearCommand\\:\\:\\$kernel is never read, only written\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/Commands/DiscoveryClearCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Console\\\\ConsoleCommand\\:\\:__unserialize\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/ConsoleCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Console\\\\ConsoleInput\\:\\:ask\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/ConsoleInput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Console\\\\GenericConsole\\:\\:ask\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/GenericConsole.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Console\\\\GenericConsoleInput\\:\\:ask\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/GenericConsoleInput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Console\\\\NullConsoleInput\\:\\:ask\\(\\) has parameter \\$options with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/NullConsoleInput.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Console/RenderConsoleCommand.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\Container\\:\\:addInitializer\\(\\) has parameter \\$initializerClass with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Container.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type TClassName is not subtype of native type object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Container.php',
];
$ignoreErrors[] = [
	'message' => '#^Match expression does not handle remaining values\\: \\(class\\-string\\<ReflectionClass\\>&literal\\-string\\)\\|\\(class\\-string\\<ReflectionFunction\\>&literal\\-string\\)\\|\\(class\\-string\\<ReflectionMethod\\>&literal\\-string\\)$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Container/Context.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\Context\\:\\:__construct\\(\\) has parameter \\$reflector with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Context.php',
];
$ignoreErrors[] = [
	'message' => '#^Match expression does not handle remaining value\\: class\\-string\\<ReflectionType\\>&literal\\-string$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Dependency.php',
];
$ignoreErrors[] = [
	'message' => '#^Match expression does not handle remaining values\\: \\(class\\-string\\<ReflectionClass\\>&literal\\-string\\)\\|\\(class\\-string\\<ReflectionParameter\\>&literal\\-string\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Dependency.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\Dependency\\:\\:__construct\\(\\) has parameter \\$reflector with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Dependency.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\Exceptions\\\\CannotInstantiateDependencyException\\:\\:__construct\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/Exceptions/CannotInstantiateDependencyException.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^If condition is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Match expression does not handle remaining values\\: \\(class\\-string\\<ReflectionType\\>&literal\\-string\\)\\|null$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:__construct\\(\\) has parameter \\$definitions with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:__construct\\(\\) has parameter \\$dynamicInitializers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:__construct\\(\\) has parameter \\$initializers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:__construct\\(\\) has parameter \\$singletons with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:addInitializer\\(\\) has parameter \\$initializerClass with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:autowireDependencies\\(\\) has parameter \\$parameters with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:call\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:getInitializers\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Container\\\\GenericContainer\\:\\:setInitializers\\(\\) has parameter \\$initializers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type for property Tempest\\\\Container\\\\GenericContainer\\:\\:\\$dynamicInitializers with type class\\-string\\<Tempest\\\\Container\\\\T\\> is incompatible with native type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc type for property Tempest\\\\Container\\\\GenericContainer\\:\\:\\$initializers with type class\\-string\\<Tempest\\\\Container\\\\T\\> is incompatible with native type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$type of method Tempest\\\\Container\\\\GenericContainer\\:\\:autowireObjectDependency\\(\\) expects ReflectionNamedType, ReflectionType given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$dynamicInitializers of method Tempest\\\\Container\\\\GenericContainer\\:\\:__construct\\(\\) has invalid type Tempest\\\\Container\\\\T\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$initializers of method Tempest\\\\Container\\\\GenericContainer\\:\\:__construct\\(\\) has invalid type Tempest\\\\Container\\\\T\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Container\\\\GenericContainer\\:\\:\\$dynamicInitializers has unknown class Tempest\\\\Container\\\\T as its type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Container\\\\GenericContainer\\:\\:\\$initializers has unknown class Tempest\\\\Container\\\\T as its type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Unreachable statement \\- code above always terminates\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Container/GenericContainer.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Database\\:\\:fetch\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Database\\:\\:fetchFirst\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Database.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\DatabaseConfig\\:\\:__construct\\(\\) has parameter \\$migrations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/DatabaseConfig.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\GenericDatabase\\:\\:fetch\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/GenericDatabase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\GenericDatabase\\:\\:fetchFirst\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/GenericDatabase.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\GenericDatabase\\:\\:resolveBindings\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/GenericDatabase.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:find\\(\\) should return Tempest\\\\Database\\\\Migrations\\\\Migration but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:new\\(\\) should return Tempest\\\\Database\\\\Migrations\\\\Migration but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Migrations\\\\Migration\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Migrations/Migration.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Query\\:\\:__construct\\(\\) has parameter \\$bindings with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Query\\:\\:fetch\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Database\\\\Query\\:\\:fetchFirst\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Database/Query.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\CommandBusDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/CommandBusDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\ConsoleCommandDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/ConsoleCommandDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\Discovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/Discovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\DiscoveryDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/DiscoveryDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\EventBusDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/EventBusDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\InitializerDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/InitializerDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\MigrationDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/MigrationDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Discovery\\\\RouteDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Discovery/RouteDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Events\\\\EventHandler\\:\\:__unserialize\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Events/EventHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Exceptions\\\\ConsoleExceptionHandler\\:\\:outputClassLine\\(\\) has parameter \\$line with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Exceptions\\\\ConsoleExceptionHandler\\:\\:outputDefaultLine\\(\\) has parameter \\$line with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Exceptions\\\\ConsoleExceptionHandler\\:\\:outputFunctionLine\\(\\) has parameter \\$line with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Exceptions\\\\ConsoleExceptionHandler\\:\\:outputLine\\(\\) has parameter \\$line with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Exceptions\\\\ConsoleExceptionHandler\\:\\:outputPath\\(\\) has parameter \\$line with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Exceptions\\\\ConsoleExceptionHandler\\:\\:outputPath\\(\\) is unused\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Unreachable statement \\- code above always terminates\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Exceptions/ConsoleExceptionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:__construct\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:getCookies\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:getQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:post\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRequest\\:\\:resolveQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\GenericRequest\\:\\:\\$query type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRequest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericResponse\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericResponse\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericResponse\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericResponse\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\GenericResponse\\:\\:\\$body type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\GenericResponse\\:\\:\\$headers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Http/GenericRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRouter\\:\\:resolveParams\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRouter\\:\\:toUri\\(\\) has parameter \\$action with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\GenericRouter\\:\\:toUri\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type T in call to method Tempest\\\\Mapper\\\\ObjectMapper\\:\\:to\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/GenericRouter.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Get\\:\\:__construct\\(\\) has parameter \\$middleware with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Get.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\MatchedRoute\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/MatchedRoute.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Post\\:\\:__construct\\(\\) has parameter \\$middleware with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Post.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Request\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Request\\:\\:getCookies\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Request\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Request\\:\\:getQuery\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Request.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Response\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Response\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Response.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Responses\\\\CreatedResponse\\:\\:__construct\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/CreatedResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Responses\\\\CreatedResponse\\:\\:__construct\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/CreatedResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Responses\\\\CreatedResponse\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/CreatedResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Responses\\\\CreatedResponse\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/CreatedResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\Responses\\\\CreatedResponse\\:\\:\\$body type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/CreatedResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\Responses\\\\CreatedResponse\\:\\:\\$headers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/CreatedResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Responses\\\\InvalidResponse\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/InvalidResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Responses\\\\InvalidResponse\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/InvalidResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\Responses\\\\InvalidResponse\\:\\:\\$body type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/InvalidResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\Responses\\\\InvalidResponse\\:\\:\\$headers type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Responses/InvalidResponse.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Route\\:\\:__unserialize\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Route.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$middleware of method Tempest\\\\Http\\\\Route\\:\\:__construct\\(\\) has invalid type Tempest\\\\Http\\\\MiddlewareClass\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Route.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Http\\\\Route\\:\\:\\$middleware has unknown class Tempest\\\\Http\\\\MiddlewareClass as its type\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Route.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Router\\:\\:toUri\\(\\) has parameter \\$action with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Router\\:\\:toUri\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Router.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Session\\\\FileSessionHandler\\:\\:getSession\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Session/FileSessionHandler.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Http\\\\Session\\\\Session\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Session/Session.php',
];
$ignoreErrors[] = [
	'message' => '#^Comparison operation "\\<" between 500\\|501\\|502\\|503\\|504\\|505\\|506\\|507\\|508\\|510\\|511 and 600 is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Status.php',
];
$ignoreErrors[] = [
	'message' => '#^Comparison operation "\\>\\=" between 100\\|101\\|102\\|103\\|200\\|201\\|202\\|203\\|204\\|205\\|206\\|207\\|208\\|226\\|300\\|301\\|302\\|303\\|304\\|305\\|306\\|307\\|308\\|400\\|401\\|402\\|403\\|404\\|405\\|406\\|407\\|408\\|409\\|410\\|411\\|412\\|413\\|414\\|415\\|416\\|417\\|418\\|421\\|422\\|423\\|424\\|425\\|426\\|428\\|429\\|431\\|451\\|500\\|501\\|502\\|503\\|504\\|505\\|506\\|507\\|508\\|510\\|511 and 100 is always true\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Status.php',
];
$ignoreErrors[] = [
	'message' => '#^Match expression does not handle remaining values\\: 306\\|418$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Http/Status.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$request of method Tempest\\\\HttpClient\\\\Driver\\\\Psr18Driver\\:\\:sendRequest\\(\\) expects Psr\\\\Http\\\\Message\\\\RequestInterface, Tempest\\\\Http\\\\Request given\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/Driver/Psr18Driver.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:delete\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:get\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:head\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:options\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:patch\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:post\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:put\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:send\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\GenericHttpClient\\:\\:trace\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/GenericHttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:delete\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:get\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:head\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:options\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:patch\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:post\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:put\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\HttpClient\\\\HttpClient\\:\\:trace\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/HttpClient/HttpClient.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/src/Mapper/ArrayToObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ArrayToObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to an undefined property Tempest\\\\ORM\\\\Model\\:\\:\\$id\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ModelToQueryMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ModelToQueryMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ModelToQueryMapper\\:\\:createQuery\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ModelToQueryMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ModelToQueryMapper\\:\\:fields\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ModelToQueryMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ModelToQueryMapper\\:\\:relations\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ModelToQueryMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ModelToQueryMapper\\:\\:updateQuery\\(\\) has parameter \\$fields with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ModelToQueryMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ObjectMapper\\:\\:collection\\(\\) has invalid return type Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ObjectMapper\\:\\:from\\(\\) has invalid return type Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ObjectMapper\\:\\:from\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\ObjectMapper\\:\\:map\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return contains generic type Tempest\\\\Mapper\\\\ObjectMapper\\<array\\<Tempest\\\\Mapper\\\\ClassType\\>\\> but class Tempest\\\\Mapper\\\\ObjectMapper is not generic\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return contains generic type Tempest\\\\Mapper\\\\ObjectMapper\\<object\\> but class Tempest\\\\Mapper\\\\ObjectMapper is not generic\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Unable to resolve the template type ClassName in call to method Tempest\\\\Mapper\\\\Mapper\\:\\:map\\(\\)$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/ObjectMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\QueryToModelMapper\\:\\:resolveData\\(\\) has parameter \\$data with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/QueryToModelMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Mapper\\\\QueryToModelMapper\\:\\:resolveData\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Mapper/QueryToModelMapper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\ORM\\\\Exceptions\\\\MissingValuesException\\:\\:__construct\\(\\) has parameter \\$missingValues with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/ORM/Exceptions/MissingValuesException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\ORM\\\\Model\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/ORM/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\ORM\\\\Model\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/ORM/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\ORM\\\\Model\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/ORM/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\ORM\\\\Model\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/ORM/Model.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:get\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:has\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:set\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:set\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:toArray\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:unwrap\\(\\) has parameter \\$array with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\ArrayHelper\\:\\:unwrap\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/ArrayHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\Reflection\\\\Attributes\\:\\:in\\(\\) has parameter \\$reflector with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/Reflection/Attributes.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Support\\\\Reflection\\\\Attributes\\:\\:resolveAttributes\\(\\) return type with generic class ReflectionAttribute does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/Reflection/Attributes.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type T\\|null is not subtype of native type object\\|null\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/Reflection/Attributes.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Support\\\\Reflection\\\\Attributes\\:\\:\\$reflector with generic class ReflectionClass does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Support/Reflection/Attributes.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:getErrorLines\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:getInfoLines\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:getLinesWithFormatting\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:getLinesWithoutFormatting\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:getSuccessLines\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:\\$errorLines type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:\\$infoLines type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:\\$lines type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Testing\\\\Console\\\\TestConsoleOutput\\:\\:\\$successLines type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Console/TestConsoleOutput.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\HttpRouterTester\\:\\:get\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/HttpRouterTester.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\HttpRouterTester\\:\\:makePsrRequest\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/HttpRouterTester.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\HttpRouterTester\\:\\:makePsrRequest\\(\\) has parameter \\$cookies with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/HttpRouterTester.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\HttpRouterTester\\:\\:makePsrRequest\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/HttpRouterTester.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\HttpRouterTester\\:\\:post\\(\\) has parameter \\$body with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/HttpRouterTester.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\HttpRouterTester\\:\\:post\\(\\) has parameter \\$headers with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/HttpRouterTester.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\TestResponseHelper\\:\\:getBody\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/TestResponseHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Testing\\\\Http\\\\TestResponseHelper\\:\\:getHeaders\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Testing/Http/TestResponseHelper.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:__construct\\(\\) has parameter \\$failingRules with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\Validation\\\\Exceptions\\\\ValidationException\\:\\:\\$failingRules type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Validation/Exceptions/ValidationException.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Validation\\\\Rules\\\\Password\\:\\:natural_language_join\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Validation/Rules/Password.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\Validation\\\\Rules\\\\Password\\:\\:natural_language_join\\(\\) has parameter \\$list with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/Validation/Rules/Password.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:__construct\\(\\) has parameter \\$params with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:data\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:escape\\(\\) has parameter \\$items with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:escape\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:extends\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:include\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\GenericView\\:\\:parseSlots\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\View\\\\GenericView\\:\\:\\$extendsParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\View\\\\GenericView\\:\\:\\$params type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Property Tempest\\\\View\\\\GenericView\\:\\:\\$rawParams type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/GenericView.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\View\\:\\:data\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\View\\:\\:extends\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tempest\\\\View\\\\View\\:\\:getErrorsFor\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/View/View.php',
];
$ignoreErrors[] = [
	'message' => '#^Function Tempest\\\\redirect\\(\\) has parameter \\$action with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/functions.php',
];
$ignoreErrors[] = [
	'message' => '#^Function Tempest\\\\redirect\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/functions.php',
];
$ignoreErrors[] = [
	'message' => '#^Function Tempest\\\\uri\\(\\) has parameter \\$action with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/functions.php',
];
$ignoreErrors[] = [
	'message' => '#^Function Tempest\\\\uri\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/functions.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return contains generic type Tempest\\\\Mapper\\\\ObjectMapper\\<object\\> but class Tempest\\\\Mapper\\\\ObjectMapper is not generic\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/functions.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @return with type TClassName is not subtype of native type object\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/src/functions.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Application\\\\ConsoleApplicationTest\\:\\:test_cli_application\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Application/ConsoleApplicationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Application\\\\ConsoleApplicationTest\\:\\:test_cli_application_flags\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Application/ConsoleApplicationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Application\\\\ConsoleApplicationTest\\:\\:test_cli_application_flags_defaults\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Application/ConsoleApplicationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Application\\\\ConsoleApplicationTest\\:\\:test_failing_command\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Application/ConsoleApplicationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Application\\\\ConsoleApplicationTest\\:\\:test_unhandled_command\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Application/ConsoleApplicationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Application\\\\HttpApplicationTest\\:\\:test_http_application_run\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Application/HttpApplicationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\CommandBus\\\\CommandBusTest\\:\\:test_command_bus_with_middleware\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/CommandBus/CommandBusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\CommandBus\\\\CommandBusTest\\:\\:test_command_handlers_are_auto_discovered\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/CommandBus/CommandBusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\CommandBus\\\\CommandBusTest\\:\\:test_unknown_handler_throws_exception\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/CommandBus/CommandBusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\Commands\\\\DiscoveryClearCommandTest\\:\\:test_it_clears_discovery_cache\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/Commands/DiscoveryClearCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\Commands\\\\DiscoveryStatusCommandTest\\:\\:test_discovery_status_command\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/Commands/DiscoveryStatusCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\Commands\\\\MigrateCommandTest\\:\\:test_migrate_command\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/Commands/MigrateCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\Commands\\\\RoutesCommandTest\\:\\:test_migrate_command\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/Commands/RoutesCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\ConsoleOutputInitializerTest\\:\\:test_in_console_application\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/ConsoleOutputInitializerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\ConsoleOutputInitializerTest\\:\\:test_in_http_application\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/ConsoleOutputInitializerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Console\\\\Fixtures\\\\MyDiscovery\\:\\:discover\\(\\) has parameter \\$class with generic class ReflectionClass but does not specify its types\\: T$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Console/Fixtures/MyDiscovery.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Database\\\\MigrationManagerTest\\:\\:test_migration\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Database/MigrationManagerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Events\\\\EventBusTest\\:\\:test_event_bus_with_middleware\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Events/EventBusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Events\\\\EventBusTest\\:\\:test_it_works\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Events/EventBusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Exceptions\\\\ConsoleExceptionHandlerTest\\:\\:test_exception\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Exceptions/ConsoleExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Exceptions\\\\HttpExceptionHandlerTest\\:\\:test_exception\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Exceptions/HttpExceptionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Http\\\\ResponseSenderInitializerTest\\:\\:test_response_sender_initializer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Http/ResponseSenderInitializerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Http\\\\ValidationResponseTest\\:\\:test_validation_errors_are_listed_in_the_response_body\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Http/ValidationResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Unreachable statement \\- code above always terminates\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Http/ValidationResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\HttpClient\\\\HttpClientDriverInitializerTest\\:\\:test_container_can_initialize_http_client_driver\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/HttpClient/HttpClientDriverInitializerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\HttpClient\\\\HttpClientInitializerTest\\:\\:test_container_can_initialize_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/HttpClient/HttpClientInitializerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:find\\(\\) should return Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:new\\(\\) should return Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryA\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryA.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:find\\(\\) should return Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:new\\(\\) should return Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\Fixtures\\\\ObjectFactoryWithValidation\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/Fixtures/ObjectFactoryWithValidation.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$author on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$books on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 4,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$id on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$name on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$prop on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$title on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_caster_on_field\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_collection\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_object_from_class_string\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_object_from_existing_object\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_object_with_has_many_relation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_object_with_map_to\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_object_with_missing_values_throws_exception\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_make_object_with_one_to_one_relation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_single_with_query\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\MapperTest\\:\\:test_validation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/MapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\PsrRequestToRequestMapperTest\\:\\:test_can_map\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/PsrRequestToRequestMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\PsrRequestToRequestMapperTest\\:\\:test_generic_request_is_used_when_interface_is_passed\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/PsrRequestToRequestMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\PsrRequestToRequestMapperTest\\:\\:test_map_with\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/PsrRequestToRequestMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Mapper\\\\PsrRequestToRequestMapperTest\\:\\:test_map_with_with_missing_data\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Mapper/PsrRequestToRequestMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:getName\\(\\)\\.$#',
	'count' => 2,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to an undefined method ReflectionType\\:\\:isBuiltin\\(\\)\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method execute\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:all\\(\\) return type has no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:create\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:find\\(\\) has parameter \\$relations with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:find\\(\\) should return Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:new\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:new\\(\\) should return Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo but returns array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Foo\\:\\:update\\(\\) has parameter \\$params with no type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Foo.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\IsModelTest\\:\\:test_complex_query\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/IsModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\IsModelTest\\:\\:test_create_and_update_model\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/IsModelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot access property \\$bindings on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 5,
	'path' => __DIR__ . '/tests/Integration/ORM/Mappers/QueryMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Cannot call method getSql\\(\\) on array\\|Tempest\\\\Mapper\\\\ClassType\\.$#',
	'count' => 3,
	'path' => __DIR__ . '/tests/Integration/ORM/Mappers/QueryMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Mappers\\\\QueryMapperTest\\:\\:test_create_query\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Mappers/QueryMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Mappers\\\\QueryMapperTest\\:\\:test_create_query_with_nested_relation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Mappers/QueryMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\ORM\\\\Mappers\\\\QueryMapperTest\\:\\:test_update_query\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/ORM/Mappers/QueryMapperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RequestTest\\:\\:test_custom_request_test\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RequestTest\\:\\:test_custom_request_test_with_nested_validation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RequestTest\\:\\:test_custom_request_test_with_validation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RequestTest\\:\\:test_from_factory\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RequestTest\\:\\:test_generic_request_can_map_to_custom_request\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RequestTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RouterTest\\:\\:test_dispatch\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RouterTest\\:\\:test_dispatch_with_parameter\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RouterTest\\:\\:test_generate_uri\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RouterTest\\:\\:test_middleware\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RouterTest\\:\\:test_route_binding\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Route\\\\RouterTest\\:\\:test_with_view\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Route/RouterTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\HttpRouterTesterIntegrationTest\\:\\:test_get_requests\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/HttpRouterTesterIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\HttpRouterTesterIntegrationTest\\:\\:test_get_requests_failure\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/HttpRouterTesterIntegrationTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:provide_assert_status_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:provide_assert_status_fails_when_status_does_not_match_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_has_header\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_has_header_failure\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_header_value_equals\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_header_value_equals_failure\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_not_found\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_not_found_fails_with_okay_response\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_ok\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_ok_fails_with_not_okay_response\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_redirect\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_redirect_to\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_redirect_without_3xx_status_code\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_redirect_without_location_header\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_status\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_assert_status_fails_when_status_does_not_match\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\Testing\\\\Http\\\\TestResponseHelperTest\\:\\:test_get_response\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/Testing/Http/TestResponseHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_extends_parameters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_include_parameters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_named_slots\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_raw_and_escaping\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_render\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_render_with_view_model\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_view_model_with_response_data\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Integration\\\\View\\\\ViewTest\\:\\:test_with_view_function\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Integration/View/ViewTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Console\\\\Fixtures\\\\MyConsole\\:\\:handle\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Console/Fixtures/MyConsole.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Console\\\\RenderConsoleCommandTest\\:\\:test_render\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Console/RenderConsoleCommandTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_arrays_are_automatically_created\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_builtin_defaults_are_used\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_call_tries_to_transform_unmatched_values\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_get_with_autowire\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_get_with_definition\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_get_with_initializer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_initialize_with_can_initializer\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_intersection_initializers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_optional_types_resolve_to_null\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_singleton\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_singleton_initializers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_union_initializers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\ContainerTest\\:\\:test_union_types_iterate_to_resolution\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/ContainerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\Exceptions\\\\CannotAutowireExceptionTest\\:\\:test_autowire_without_exception\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/Exceptions/CannotAutowireExceptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\Exceptions\\\\CircularDependencyExceptionTest\\:\\:test_circular_dependency_as_a_child_test\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/Exceptions/CircularDependencyExceptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\Exceptions\\\\CircularDependencyExceptionTest\\:\\:test_circular_dependency_test\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/Exceptions/CircularDependencyExceptionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Container\\\\Fixtures\\\\BuiltinArrayClass\\:\\:__construct\\(\\) has parameter \\$anArray with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Container/Fixtures/BuiltinArrayClass.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\GenericResponseSenderTest\\:\\:test_sending\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/GenericResponseSenderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\GenericResponseSenderTest\\:\\:test_sending_of_array_to_json\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/GenericResponseSenderTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Responses\\\\CreatedResponseTest\\:\\:test_created_response\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Responses/CreatedResponseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\ArraySessionHandlerTest\\:\\:test_destroying_a_session\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/ArraySessionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\ArraySessionHandlerTest\\:\\:test_garbage_collection\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/ArraySessionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\ArraySessionHandlerTest\\:\\:test_open_and_close\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/ArraySessionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\ArraySessionHandlerTest\\:\\:test_opening_expired_session\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/ArraySessionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\ArraySessionHandlerTest\\:\\:test_opening_saved_session\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/ArraySessionHandlerTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\SessionTest\\:\\:test_exception_is_thrown_if_session_was_started_by_php\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\SessionTest\\:\\:test_getting_all_session_values\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\SessionTest\\:\\:test_getting_and_setting_session_keys\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\SessionTest\\:\\:test_getting_and_setting_session_keys_with_dot_notation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\Session\\\\SessionTest\\:\\:test_starting_session\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/Session/SessionTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\StatusTest\\:\\:provide_status_code_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/StatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Http\\\\StatusTest\\:\\:test_status_code\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Http/StatusTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_delete_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_get_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_get_with_headers_proxies_to_http_client_with_headers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_head_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_options_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_patch_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_post_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_put_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_send_request_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\HttpClient\\\\GenericHttpClientTest\\:\\:test_trace_proxies_to_http_client\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/HttpClient/GenericHttpClientTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\KernelTest\\:\\:test_discovery\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/KernelTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_getting_array_value_with_dot_notation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_getting_array_value_with_single_key\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_getting_non_existent_value_with_dot_notation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_has_key_with_dot_notation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_has_key_with_single_key\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_setting_array_value_with_dot_notation\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_setting_array_value_with_single_key\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_to_array_with_nested_property\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_unwrap_nested_key\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_unwrap_nested_key_multiple_items\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_unwrap_real\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_unwrap_several_items\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Support\\\\ArrayHelperTest\\:\\:test_unwrap_single_key\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Support/ArrayHelperTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\AlphaNumericTest\\:\\:test_alphanumeric\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/AlphaNumericTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\AlphaTest\\:\\:test_alpha\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/AlphaTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\BetweenTest\\:\\:test_between\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/BetweenTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\BooleanTest\\:\\:test_boolean\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/BooleanTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\BooleanTest\\:\\:test_boolean_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/BooleanTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\CountTest\\:\\:provide_count_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/CountTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\CountTest\\:\\:provide_returns_the_proper_message_based_on_min_and_max_arguments_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/CountTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\CountTest\\:\\:test_count\\(\\) has parameter \\$stringToTest with no value type specified in iterable type array\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/CountTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\DateTimeFormatTest\\:\\:test_datetime_format\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/DateTimeFormatTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\DateTimeFormatTest\\:\\:test_datetime_format_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/DateTimeFormatTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\DateTimeFormatTest\\:\\:test_datetime_format_with_different_format\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/DateTimeFormatTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\DateTimeFormatTest\\:\\:test_datetime_format_with_integer_value\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/DateTimeFormatTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\DoesNotEndWithTest\\:\\:provide_rule_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/DoesNotEndWithTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\DoesNotStartWithTest\\:\\:provide_rule_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/DoesNotStartWithTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\EmailTest\\:\\:test_email\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/EmailTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\EndsWithTest\\:\\:test_ends_with\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/EndsWithTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\EnumTest\\:\\:test_enum_has_to_exist\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/EnumTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\EnumTest\\:\\:test_validating_backed_enums\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/EnumTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\EnumTest\\:\\:test_validating_enums\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/EnumTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPTest\\:\\:test_ip_address\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPTest\\:\\:test_messages\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv4Test\\:\\:test_ip_address_without_private_range\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv4Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv4Test\\:\\:test_ip_address_without_reserved_range\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv4Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv4Test\\:\\:test_ipv4_address\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv4Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv4Test\\:\\:test_messages\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv4Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv6Test\\:\\:test_ip_address_without_private_range\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv6Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv6Test\\:\\:test_ip_address_without_reserved_range\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv6Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv6Test\\:\\:test_ipv6_address\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv6Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\IPv6Test\\:\\:test_messages\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/IPv6Test.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\LengthTest\\:\\:provide_length_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/LengthTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\LengthTest\\:\\:provide_returns_the_proper_message_based_on_min_and_max_arguments_cases\\(\\) return type has no value type specified in iterable type iterable\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/LengthTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\LowercaseTest\\:\\:test_lowercase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/LowercaseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\MACAddressTest\\:\\:test_ip_address\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/MACAddressTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\NotEmptyTest\\:\\:test_not_empty\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/NotEmptyTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_defaults\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_invalid_input\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_letters\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_minimum\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_mixed_case\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_numbers\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PasswordTest\\:\\:test_symbols\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PasswordTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\PhoneNumberTest\\:\\:test_phone_number\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/PhoneNumberTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\RegexTest\\:\\:test_regex_rule\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/RegexTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\ShouldBeFalseTest\\:\\:test_should_be_false\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/ShouldBeFalseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\ShouldBeFalseTest\\:\\:test_should_be_false_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/ShouldBeFalseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\ShouldBeTrueTest\\:\\:test_should_be_true\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/ShouldBeTrueTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\ShouldBeTrueTest\\:\\:test_should_be_true_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/ShouldBeTrueTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\StartsWithTest\\:\\:test_starts_with\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/StartsWithTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimeTest\\:\\:test_military_time\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimeTest\\:\\:test_time\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimeTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimestampTest\\:\\:test_timestamp\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimestampTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimestampTest\\:\\:test_timestamp_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimestampTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimezoneTest\\:\\:test_timezone\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimezoneTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimezoneTest\\:\\:test_timezone_with_country_code\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimezoneTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\TimezoneTest\\:\\:test_timezone_with_group\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/TimezoneTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UlidTest\\:\\:test_ulid\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UlidTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UppercaseTest\\:\\:test_uppercase\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UppercaseTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UrlTest\\:\\:test_url\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UrlTest\\:\\:test_url_message\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UrlTest\\:\\:test_url_with_integer_value\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UrlTest\\:\\:test_url_with_restricted_protocols\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UrlTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\Rules\\\\UuidTest\\:\\:test_uuid\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/Rules/UuidTest.php',
];
$ignoreErrors[] = [
	'message' => '#^Method Tests\\\\Tempest\\\\Unit\\\\Validation\\\\ValidatorTest\\:\\:test_validator\\(\\) has no return type specified\\.$#',
	'count' => 1,
	'path' => __DIR__ . '/tests/Unit/Validation/ValidatorTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
