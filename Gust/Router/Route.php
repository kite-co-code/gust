<?php

namespace Gust\Router;

use Gust\Router;

class Route
{
    protected string $type;

    protected string $pattern;

    /** @var string|callable */
    protected mixed $handler;

    protected ?string $role = null;

    protected ?string $template = null;

    protected array $slots = [];

    protected array $middleware = [];

    protected ?string $name = null;

    /** Handler instance for class-based handlers, created once per request. */
    protected ?object $instance = null;

    protected bool $prepared = false;

    /** @var array<string, string> Named {param} values captured when an owned route matches. */
    protected array $params = [];

    /** @param string|callable $handler */
    public function __construct(string $type, string $pattern, mixed $handler)
    {
        $this->type = $type;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public function withPage(string $role): static
    {
        $this->role = $role;
        Router::ensurePage($role);

        return $this;
    }

    /** @deprecated Use withPage() instead */
    public function withContent(string $role): static
    {
        return $this->withPage($role);
    }

    /**
     * Remove the backing page association for this route.
     * Only needed after calling withPage() to undo it — e.g. when a route
     * conditionally needs no page wrapper.
     */
    public function noContent(): static
    {
        $this->role = null;

        return $this;
    }

    public function withSlot(string $name, mixed $renderer): static
    {
        $this->slots[$name] = $renderer;

        return $this;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function middleware(callable $fn): static
    {
        $this->middleware[] = $fn;

        return $this;
    }

    public function template(string $name): static
    {
        $this->template = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /** @return string|callable */
    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function getTemplate(): ?string
    {
        if ($this->template) {
            return \locate_template($this->template.'.php') ?: null;
        }

        return $this->resolveDefaultTemplate();
    }

    protected function resolveDefaultTemplate(): ?string
    {
        if ($this->type !== 'decorated') {
            return \locate_template('page.php') ?: \locate_template('index.php') ?: null;
        }

        $parts = explode(':', $this->pattern);
        $type = $parts[0];
        $name = $parts[1] ?? null;

        $templates = match ($type) {
            'post_type' => ["archive-{$name}.php", 'archive.php'],
            'taxonomy' => ["taxonomy-{$name}.php", 'taxonomy.php', 'archive.php'],
            'archive' => $name === 'post' ? ['home.php', 'index.php'] : ["archive-{$name}.php"],
            'search' => ['search.php', 'index.php'],
            '404' => ['404.php', 'index.php'],
            default => ['index.php'],
        };

        foreach ($templates as $t) {
            if ($path = \locate_template($t)) {
                return $path;
            }
        }

        return \locate_template('index.php') ?: null;
    }

    /**
     * Run middleware and the handler's prepare(). Runs once per request:
     * later calls do nothing.
     */
    public function prepare(): void
    {
        if ($this->prepared) {
            return;
        }

        $this->prepared = true;

        // Run middleware
        foreach ($this->middleware as $fn) {
            call_user_func($fn, $this);
        }

        // Call handler's prepare if class-based
        $instance = $this->getHandlerInstance();
        if ($instance && method_exists($instance, 'prepare')) {
            $instance->prepare();
        }
    }

    /**
     * The handler object for a class-based handler. It's created on first use
     * and shared by prepare() and handle(), so state set in prepare() is kept.
     */
    public function getHandlerInstance(): ?object
    {
        if ($this->instance === null && is_string($this->handler) && class_exists($this->handler)) {
            $this->instance = new $this->handler;
        }

        return $this->instance;
    }

    /**
     * @param  array<string, string>  $params
     */
    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Named {param} values captured from the request path (owned routes only).
     *
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    public function getSlot(string $name): ?callable
    {
        return $this->slots[$name] ?? null;
    }

    public function getSlots(): array
    {
        return $this->slots;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        if ($this->type === 'owned') {
            return \home_url($this->pattern);
        }

        if ($this->pattern === 'search') {
            return \home_url('?s=');
        }

        return $this->getPublicUrl() ?? \home_url();
    }

    /**
     * The route's own URL, or null when it has no page of its own to send
     * visitors to: search, 404, taxonomy archives (which need a term) and
     * owned routes with {param} placeholders.
     */
    public function getPublicUrl(): ?string
    {
        if ($this->type === 'owned') {
            return str_contains($this->pattern, '{') ? null : \home_url($this->pattern);
        }

        // Decorated routes - resolve from WP
        $parts = explode(':', $this->pattern);
        $type = $parts[0];
        $name = $parts[1] ?? null;

        $url = match ($type) {
            'post_type' => \get_post_type_archive_link($name),
            // get_permalink(0) would return the global post's link, so guard it.
            'archive' => $name === 'post'
                ? ((int) \get_option('page_for_posts') ? \get_permalink((int) \get_option('page_for_posts')) : null)
                : \get_post_type_archive_link($name),
            default => null,
        };

        return $url ?: null;
    }
}
