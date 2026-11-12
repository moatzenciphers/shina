<?php
/** Normalize only the reviewed function-to-method renames against the original baseline. */
if (PHP_SAPI !== 'cli' || ! defined('ABSPATH')) {
    exit;
}

$theme_test_method_map = json_decode(file_get_contents(__DIR__ . '/method-map.json'), true);
$theme_test_template_map = json_decode(file_get_contents(__DIR__ . '/template-map.json'), true);

function theme_test_legacy_callback($value)
{
    global $theme_test_method_map;
    if ($value instanceof Closure) {
        return 'Closure';
    }
    if (is_array($value) && count($value) === 2 && isset($value[0], $value[1]) && is_string($value[1]) && (is_string($value[0]) || is_object($value[0]))) {
        $class = is_object($value[0]) ? get_class($value[0]) : $value[0];
        foreach ($theme_test_method_map as $old => $method) {
            if ($method === array($class, $value[1])) {
                return $old;
            }
        }
        if (in_array($class . '::' . $value[1], array(
            'Edemchinim\\Theme\\Assets::enqueueLinks',
            'Edemchinim\\Content\\Services::registerFields',
            'Edemchinim\\Content\\Pages::registerSettings',
        ), true)) {
            return 'Closure';
        }
        if (is_object($value[0])) {
            return $class . '::' . $value[1];
        }
    }
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = theme_test_legacy_callback($item);
        }
    }
    return $value;
}

function theme_test_original_source($source, $file, $context = null)
{
    global $theme_test_method_map, $theme_test_template_map;
    $imports = array();
    preg_match_all('/^use ([\w\\\\]+)(?: as (\w+))?;/m', file_get_contents($file), $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        $parts = explode('\\', $match[1]);
        $imports[$match[1]] = $match[2] ?? end($parts);
    }
    foreach ($theme_test_method_map as $old => $method) {
        list($class, $name) = $method;
        $references = array('\\' . $class);
        if (isset($imports[$class])) {
            $references[] = $imports[$class];
        }
        if ($context === $class) {
            $references[] = 'self';
        }
        foreach ($references as $reference) {
            $reference = preg_quote($reference, '/');
            $callback = '/\[\s*' . $reference . "::class\s*,\s*(['\"])" . preg_quote($name, '/') . '\\1\s*\]/';
            $source = preg_replace($callback, "'" . $old . "'", $source);
            $call = '/(?<![\w\\\\])' . $reference . '::' . preg_quote($name, '/') . '(?=\s*\()/';
            $source = preg_replace($call, $old, $source);
        }
    }
    // The old optional template function is now an always-loaded public method.
    $source = str_replace("is_callable('edemchinim_phone_href')", "function_exists('edemchinim_phone_href')", $source);
    // File relocation changes the dispatch path, not the layout names stored in ACF.
    foreach ($theme_test_template_map as $old => $new) {
        $source = str_replace("'" . $new . "'", "'" . $old . "'", $source);
    }
    $source = str_replace("'template-parts/blocks/'", "'template-parts/landing-'", $source);
    return preg_replace('/^use [\w\\\\]+(?: as \w+)?;\s*$/m', '', $source);
}

function theme_test_code_hash($source)
{
    $code = '';
    foreach (token_get_all($source) as $token) {
        if (is_array($token)) {
            if (! in_array($token[0], array(T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) {
                $code .= $token[1];
            }
        } else {
            $code .= $token;
        }
    }
    return hash('sha256', $code);
}
