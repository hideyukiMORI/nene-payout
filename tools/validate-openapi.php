<?php

declare(strict_types=1);

/**
 * Minimal OpenAPI contract validation for `composer openapi`.
 *
 * Parses docs/openapi/openapi.yaml, asserts it is structurally an OpenAPI 3.1
 * document, and that every local `$ref` resolves to a defined component. This is
 * the first-pass guard; richer runtime contract tests come with the HTTP layer.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$path = __DIR__ . '/../docs/openapi/openapi.yaml';

if (!is_file($path)) {
    fwrite(STDERR, "OpenAPI file not found: {$path}\n");
    exit(1);
}

/** @var array<string, mixed> $doc */
$doc = Yaml::parseFile($path);

$errors = [];

if (!isset($doc['openapi']) || !is_string($doc['openapi']) || !str_starts_with($doc['openapi'], '3.')) {
    $errors[] = 'Missing or invalid `openapi` version (expected 3.x).';
}

if (!isset($doc['info']['title'], $doc['info']['version'])) {
    $errors[] = 'Missing `info.title` / `info.version`.';
}

if (!isset($doc['paths']) || !is_array($doc['paths']) || $doc['paths'] === []) {
    $errors[] = 'No `paths` defined.';
}

// Collect defined component refs.
$defined = [];
foreach (((array) ($doc['components'] ?? [])) as $section => $items) {
    if (is_array($items)) {
        foreach (array_keys($items) as $name) {
            $defined['#/components/' . $section . '/' . $name] = true;
        }
    }
}

// Collect all $ref usages (recursively) and check resolution.
$refs = [];
$walk = static function (mixed $node) use (&$walk, &$refs): void {
    if (is_array($node)) {
        foreach ($node as $key => $value) {
            if ($key === '$ref' && is_string($value)) {
                $refs[$value] = true;
            } else {
                $walk($value);
            }
        }
    }
};
$walk($doc);

foreach (array_keys($refs) as $ref) {
    if (str_starts_with($ref, '#/') && !isset($defined[$ref])) {
        $errors[] = "Unresolved \$ref: {$ref}";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "OpenAPI validation failed:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

$pathCount = count((array) $doc['paths']);
echo "OpenAPI OK: {$doc['openapi']}, {$pathCount} paths, " . count($refs) . " refs resolved.\n";
exit(0);
