<?php

namespace openWebX\openTraits;

use BadMethodCallException;
use RuntimeException;
use Throwable;

trait MagicVariables
{
    // Für statische Magic-Properties
    private static array $staticStore = [];

    /** Dynamische Setter: $obj->foo = 'bar'; */
    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    /** Dynamischer Getter: $obj->foo; */
    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    /**
     * Magic-Aufrufe wie $obj->setFoo('bar') / $obj->getFoo()
     */
    public function __call(string $name, array $arguments): mixed
    {
        $action = substr($name, 0, 3);
        $prop   = lcfirst(substr($name, 3));

        return match ($action) {
            'set' => $prop !== ''
                ? $this->__set($prop, $arguments[0])
                : throw new BadMethodCallException("Method {$name} invalid"),

            'get' => $prop !== ''
                ? $this->__get($prop)
                : throw new BadMethodCallException("Method {$name} invalid"),

            default => throw new BadMethodCallException("Method {$name} not found"),
        };
    }

    /**
     * Statische Magic-Aufrufe wie ClassName::setFoo('bar') / ClassName::getFoo()
     * speichert/liest Werte aus einem internen statischen Array.
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $action = substr($name, 0, 3);
        $prop   = lcfirst(substr($name, 3));

        try {
            return match ($action) {
                'set' => $prop !== ''
                    ? static::$staticStore[$prop] = $arguments[0]
                    : throw new BadMethodCallException("Static method {$name} invalid"),

                'get' => $prop !== ''
                    ? static::$staticStore[$prop] ?? null
                    : throw new BadMethodCallException("Static method {$name} invalid"),

                default => throw new BadMethodCallException("Static method {$name} not found"),
            };
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Error in static call {$name}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /** isset($obj->foo) */
    public function __isset(string $name): bool
    {
        return isset($this->$name);
    }

    /** unset($obj->foo) */
    public function __unset(string $name): void
    {
        unset($this->$name);
    }
}
