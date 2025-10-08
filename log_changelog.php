<?php
/**
 * log_changelog.php
 * Posts composer update deltas to a Nuxt/Nitro API, including unchanged plugins.
 *
 * Required env:
 *  - NUXT_API_URL=https://dashboard.example.com/api/changelogs
 *  - NUXT_API_KEY=your-bearer-key
 *  - NUXT_HMAC_SECRET=your-long-random-secret
 *  - SITE_ID=cc-london (short slug)
 *  - SITE_NAME="Clements & Church"
 *  - SITE_ENV=production|staging|dev
 * Optional:
 *  - CI_BUILD_URL, GIT_SHA, GIT_BRANCH
 */

date_default_timezone_set(@date_default_timezone_get());

// ---------- Config ----------
$API_URL   = getenv('NUXT_API_URL') ?: 'https://maintenance.plott.co.uk/api/changelogs';
$API_KEY   = getenv('NUXT_API_KEY') ?: '55616d31e0e60b8f91406ed4af325eccc690b020f55b8df9d67199af8b782f94';
$HMAC      = getenv('NUXT_HMAC_SECRET') ?: 'a43138c3df492aced2b641ef34f1314eee4b87eee090c94cecb9ec172811c7be';
$SITE_ID   = getenv('SITE_ID') ?: 'plott-core';
$SITE_NAME = getenv('SITE_NAME') ?: 'PLOTT Core';
$SITE_ENV  = getenv('SITE_ENV') ?: 'production';

// Limit to WP plugins (add 'wordpress-theme' if you want themes too)
$PLUGIN_TYPES = ['wordpress-plugin','wordpress-muplugin'];

$oldLockFile = __DIR__ . '/composer.lock.bak';
$newLockFile = __DIR__ . '/composer.lock';

if (!file_exists($newLockFile)) {
    fwrite(STDERR, "New composer.lock file is missing.\n");
    exit(1);
}

// ---------- Helpers ----------
function read_lock(string $path): array {
    $raw = json_decode(@file_get_contents($path), true) ?: [];
    $out = [];
    foreach (['packages','packages-dev'] as $k) {
        foreach (($raw[$k] ?? []) as $pkg) {
            $name    = $pkg['name'] ?? null;
            if (!$name) continue;
            $out[$name] = [
                'name'    => $name,
                'version' => $pkg['version'] ?? '',
                'type'    => $pkg['type'] ?? '',
                'source'  => $pkg['source']['url'] ?? null,
                'dist'    => $pkg['dist']['url'] ?? null,
            ];
        }
    }
    $out['_meta'] = [
        'plugin-api-version' => $raw['plugin-api-version'] ?? null,
    ];
    return $out;
}

function filter_plugins(array $map, array $types): array {
    $plugins = [];
    foreach ($map as $name => $pkg) {
        if ($name === '_meta') continue;
        if (in_array(($pkg['type'] ?? ''), $types, true)) {
            $plugins[$name] = $pkg;
        }
    }
    return $plugins;
}

function sort_by_name(array &$arr): void {
    usort($arr, fn($a,$b) => strcmp($a['name'], $b['name']));
}

// ---------- Load locks ----------
$newMap     = read_lock($newLockFile);
$newPlugins = filter_plugins($newMap, $PLUGIN_TYPES);

$hasOld          = file_exists($oldLockFile);
$initialSnapshot = !$hasOld;

$oldMap     = $hasOld ? read_lock($oldLockFile) : [];
$oldPlugins = $hasOld ? filter_plugins($oldMap, $PLUGIN_TYPES) : [];

// ---------- Compute deltas & full snapshot ----------
$updated = $added = $removed = $unchanged = [];
$all = [];

// Union of names
$names = array_unique(array_merge(array_keys($oldPlugins), array_keys($newPlugins)));
sort($names);

foreach ($names as $name) {
    $oldVer = $oldPlugins[$name]['version'] ?? null;
    $newVer = $newPlugins[$name]['version'] ?? null;

    if ($oldVer === null && $newVer !== null) {
        $status = $initialSnapshot ? 'current' : 'added';
        $added[] = ['name' => $name, 'new' => $newVer];
        $all[] = ['name'=>$name, 'old'=>null, 'new'=>$newVer, 'status'=>$status];
    } elseif ($newVer === null && $oldVer !== null) {
        $status = 'removed';
        $removed[] = ['name' => $name, 'old' => $oldVer];
        $all[] = ['name'=>$name, 'old'=>$oldVer, 'new'=>null, 'status'=>$status];
    } elseif ($oldVer !== null && $newVer !== null) {
        if ($oldVer !== $newVer) {
            $status = 'updated';
            $updated[] = ['name'=>$name,'old'=>$oldVer,'new'=>$newVer];
        } else {
            $status = $initialSnapshot ? 'current' : 'unchanged';
            $unchanged[] = ['name'=>$name,'version'=>$newVer];
        }
        $all[] = ['name'=>$name,'old'=>$oldVer,'new'=>$newVer,'status'=>$status];
    }
}

sort_by_name($updated);
sort_by_name($added);
sort_by_name($removed);
sort_by_name($unchanged);
sort_by_name($all);

$nowIso = (new DateTimeImmutable())->format(DateTimeInterface::ATOM);

$payload = [
    'site' => [
        'id'   => $SITE_ID,
        'name' => $SITE_NAME,
        'env'  => $SITE_ENV,
    ],
    'run' => [
        'timestamp'   => $nowIso,
        'php_version' => PHP_VERSION,
        'composer'    => $newMap['_meta']['plugin-api-version'] ?? null,
        'ci_url'      => getenv('CI_BUILD_URL') ?: null,
        'git_sha'     => getenv('GIT_SHA') ?: trim((string)@shell_exec('git rev-parse --short HEAD')),
        'git_branch'  => getenv('GIT_BRANCH') ?: trim((string)@shell_exec('git rev-parse --abbrev-ref HEAD')),
    ],
    'summary' => [
        'total_plugins'   => count($all),
        'updated_count'   => count($updated),
        'added_count'     => count($added),
        'removed_count'   => count($removed),
        'unchanged_count' => count($unchanged),
        'has_changes'     => (bool)(count($updated)+count($added)+count($removed)),
        'initial_snapshot'=> $initialSnapshot,
    ],
    // Back-compat "changes" block
    'changes' => [
        'updated'   => $updated,   // [{name, old, new}]
        'added'     => $added,     // [{name, new}]
        'removed'   => $removed,   // [{name, old}]
        'unchanged' => $unchanged, // [{name, version}]
    ],
    // New: flattened view of every plugin with status
    'plugins' => $all, // [{name, old|null, new|null, status: updated|added|removed|unchanged|current}]
];

// sign with HMAC (body-based)
$body = json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
$nonce = bin2hex(random_bytes(16));
$signature = base64_encode(hash_hmac('sha256', $nonce . '.' . $body, $HMAC, true));

// send
$ch = curl_init($API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $API_KEY,
        'X-Nonce: ' . $nonce,
        'X-Signature: ' . $signature,
    ],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_POSTFIELDS     => $body,
]);
$response = curl_exec($ch);
$errno    = curl_errno($ch);
$http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Only delete the backup after a successful POST
if ($errno === 0 && $http >= 200 && $http < 300 && file_exists($oldLockFile)) {
    @unlink($oldLockFile);
}

if ($errno) {
    fwrite(STDERR, "POST failed. cURL error {$errno}\n");
    exit(2);
}
if ($http < 200 || $http >= 300) {
    fwrite(STDERR, "API HTTP {$http}. Body:\n{$response}\n");
    exit(3);
}
echo "Posted to dashboard (HTTP {$http}).\n";
