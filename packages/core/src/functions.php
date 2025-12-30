<?php

declare(strict_types=1);

namespace Tempest {
    use Closure;
    use Stringable;
    use Tempest\Core\Composer;
    use Tempest\Core\DeferredTasks;
    use Tempest\Core\EnvironmentVariableValidationFailed;
    use Tempest\Core\Kernel;
    use Tempest\Intl\Translator;
    use Tempest\Support\Namespace\PathCouldNotBeMappedToNamespace;
    use Tempest\Validation\Rule;
    use Tempest\Validation\Validator;

    use function Tempest\Support\Namespace\to_psr4_namespace;
    use function Tempest\Support\Path\to_absolute_path;

    /**
     * Creates an absolute path scoped to the root of the project.
     */
    function root_path(Stringable|string ...$parts): string
    {
        return to_absolute_path(get(Kernel::class)->root, ...$parts);
    }

    /**
     * Creates an absolute path scoped to the main directory of the project.
     */
    function src_path(Stringable|string ...$parts): string
    {
        return root_path(get(Composer::class)->mainNamespace->path, ...$parts);
    }

    /**
     * Creates an absolute path scoped to the framework's internal storage directory.
     */
    function internal_storage_path(Stringable|string ...$parts): string
    {
        return to_absolute_path(get(Kernel::class)->internalStorage, ...$parts);
    }

    /**
     * Converts the given path to a registered namespace. The path is expected to be absolute, or relative to the root of the project.
     *
     * @throws PathCouldNotBeMappedToNamespace If the path cannot be mapped to registered namespace
     */
    function registered_namespace(Stringable|string ...$parts): string
    {
        return to_psr4_namespace(get(Composer::class)->namespaces, root_path(...$parts), root: root_path());
    }

    /**
     * Converts the given path to the main namespace. The path is expected to be absolute, or relative to the root of the project.
     *
     * @throws PathCouldNotBeMappedToNamespace If the path cannot be mapped to the main namespace
     */
    function src_namespace(Stringable|string ...$parts): string
    {
        return to_psr4_namespace(get(Composer::class)->mainNamespace, root_path(...$parts), root: root_path());
    }

    /**
     * Retrieves the given `$key` from the environment variables. If `$key` is not defined, `$default` is returned instead.
     *
     * @param Rule[] $rules Optional validation rules for the value of this environment variable. If one of the rules don't pass, an exception is thrown, preventing the application from booting.
     */
    function env(string $key, mixed $default = null, array $rules = []): mixed
    {
        $value = getenv($key);
        $value = match (is_string($value) ? mb_strtolower($value) : $value) {
            'true' => true,
            'false' => false,
            false, 'null', '' => $default,
            default => $value,
        };

        if ($rules === [] || ! class_exists(Validator::class) || ! interface_exists(Translator::class)) {
            return $value;
        }

        $validator = get(Validator::class);
        $failures = $validator->validateValue($value, $rules);

        if ($failures === []) {
            return $value;
        }

        throw new EnvironmentVariableValidationFailed(
            name: $key,
            value: $value,
            failingRules: $failures,
            validator: $validator,
        );
    }

    /**
     * Defer a task, will be run after a request has been sent or a command has executed
     */
    function defer(Closure $closure): void
    {
        get(DeferredTasks::class)->add($closure);
    }
}
