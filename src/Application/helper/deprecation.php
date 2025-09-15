<?php
trait Deprecation
{
    /**
     * Trigger a deprecation notice.
     *
     * @param string $method  The old method/function name
     * @param string $version The version since deprecated
     * @param string|null $alternative The replacement method/function (if any)
     */
    protected function deprecated(string $method, string $version, string $alternative = null): void
    {
        $message = sprintf(
            "%s() is deprecated since version %s",
            $method,
            $version
        );

        if ($alternative) {
            $message .= sprintf(", use %s() instead.", $alternative);
        }

        trigger_error($message, E_USER_DEPRECATED);
    }
}
?>