<?php

declare(strict_types=1);

namespace Laminas\Filter;

use Laminas\Stdlib\StringUtils;
use Traversable;

use function array_key_exists;
use function array_map;
use function gettype;
use function is_array;
use function is_object;
use function is_scalar;
use function is_string;
use function method_exists;
use function sprintf;
use function str_replace;
use function ucwords;

/**
 * @template TOptions of array
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * Filter options
     *
     * @var TOptions
     */
    protected $options = [];

    /**
     * @deprecated Since 2.1.0
     *
     * @return bool
     */
    public static function hasPcreUnicodeSupport()
    {
        return StringUtils::hasPcreUnicodeSupport();
    }

    /**
     * @param  TOptions|iterable $options
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                is_object($options) ? $options::class : gettype($options)
            ));
        }

        foreach ($options as $key => $value) {
            $setter = is_string($key)
                ? 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)))
                : null;
            if ($setter && method_exists($this, $setter)) {
                $this->{$setter}($value);
            } elseif (is_string($key) && array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'The option "%s" does not have a matching %s setter method or options[%s] array key',
                        $key,
                        (string) $setter,
                        $key
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Retrieve options representing object state
     *
     * @return TOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Invoke filter as a command
     *
     * Proxies to {@link filter()}
     *
     * @param  mixed $value
     * @throws Exception\ExceptionInterface If filtering $value is impossible.
     * @return mixed
     */
    public function __invoke($value)
    {
        return $this->filter($value);
    }

    /**
     * @param  mixed $options
     * @return bool
     */
    protected static function isOptions($options)
    {
        return is_array($options) || $options instanceof Traversable;
    }

    /**
     * @internal
     *
     * @param  mixed $value
     * @return mixed
     */
    protected static function applyFilterOnlyToStringableValuesAndStringableArrayValues($value, callable $callback)
    {
        if (! is_array($value)) {
            if (! is_scalar($value)) {
                return $value;
            }
            return $callback((string) $value);
        }
        return $callback(array_map(static fn($item) => is_scalar($item) ? (string) $item : $item, $value));
    }
}
