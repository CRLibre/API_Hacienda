<?php

declare(strict_types=1);

$bridge_replied = false;

function bridge_emit(array $payload): void
{
    global $bridge_replied;
    if ($bridge_replied) {
        return;
    }
    $bridge_replied = true;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
}

set_exception_handler(function (\Throwable $ex): void {
    bridge_emit([
        'ok' => false,
        'error' => 'php_exception',
        'message' => $ex->getMessage(),
    ]);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    bridge_emit([
        'ok' => false,
        'error' => 'php_error',
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'severity' => $severity,
    ]);
    return true;
});

register_shutdown_function(function (): void {
    global $bridge_replied;
    if ($bridge_replied) {
        return;
    }

    $err = error_get_last();
    if ($err !== null) {
        bridge_emit([
            'ok' => false,
            'error' => 'php_fatal',
            'message' => $err['message'] ?? 'fatal_error',
            'file' => $err['file'] ?? '',
            'line' => $err['line'] ?? 0,
        ]);
    }
});

$raw = file_get_contents('php://stdin');
if ($raw === false || trim($raw) === '') {
    bridge_emit(['ok' => false, 'error' => 'invalid_input', 'message' => 'Empty stdin payload']);
    exit(0);
}

$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    bridge_emit(['ok' => false, 'error' => 'invalid_json', 'message' => 'Cannot decode input JSON']);
    exit(0);
}

$route = isset($decoded['route']) ? (string) $decoded['route'] : '';
$params = isset($decoded['params']) && is_array($decoded['params']) ? $decoded['params'] : [];

$normalized = [];
foreach ($params as $k => $v) {
    $key = (string) $k;
    if (is_bool($v)) {
        $normalized[$key] = $v ? 'true' : 'false';
    } elseif ($v === null) {
        $normalized[$key] = '';
    } elseif (is_scalar($v)) {
        $normalized[$key] = (string) $v;
    } else {
        $normalized[$key] = json_encode($v, JSON_UNESCAPED_UNICODE);
    }
}
$GLOBALS['bridge_params'] = $normalized;

if (!function_exists('params_get')) {
    function params_get($which, $def = '')
    {
        $params = $GLOBALS['bridge_params'] ?? [];
        if (array_key_exists($which, $params)) {
            return $params[$which];
        }
        return $def;
    }
}

if (!function_exists('grace_debug')) {
    function grace_debug($msg = '')
    {
        return;
    }
}

$repoRoot = realpath(__DIR__ . '/../../../../');
if ($repoRoot === false) {
    bridge_emit(['ok' => false, 'error' => 'repo_root_not_found', 'message' => 'Cannot resolve repository root']);
    exit(0);
}

$genXmlFile = $repoRoot . '/api/contrib/genXML/genXML.php';
if (!is_file($genXmlFile)) {
    bridge_emit(['ok' => false, 'error' => 'genxml_file_not_found', 'message' => $genXmlFile]);
    exit(0);
}

require_once $genXmlFile;

$routeToAction = [
    'gen_xml_fe' => 'genXMLFe',
    'gen_xml_nc' => 'genXMLNC',
    'gen_xml_nd' => 'genXMLND',
    'gen_xml_te' => 'genXMLTE',
    'gen_xml_mr' => 'genXMLMr',
    'gen_xml_fec' => 'genXMLFec',
    'gen_xml_fee' => 'genXMLFee',
    'test' => 'test',
];

if (!isset($routeToAction[$route])) {
    bridge_emit(['ok' => false, 'error' => 'unknown_route', 'message' => $route]);
    exit(0);
}

$action = $routeToAction[$route];
if (!function_exists($action)) {
    bridge_emit(['ok' => false, 'error' => 'action_not_found', 'message' => $action]);
    exit(0);
}

ob_start();
$result = $action();
$stdout = ob_get_clean();

bridge_emit([
    'ok' => true,
    'result' => $result,
    'stdout' => $stdout,
]);

