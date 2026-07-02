<?php
require_once __DIR__ . '/../whitelist.php';

function assertEqual($actual, $expected, $message) {
    if ($actual !== $expected) {
        fwrite(STDERR, "Assertion failed: $message\n");
        exit(1);
    }
}

assertEqual(validateAssetNetworkParts(explode('|', 'ADD|TGR|TON')), true, 'valid asset and network');
assertEqual(validateAssetNetworkParts(explode('|', 'ADD|BAD|TON')), false, 'invalid asset');
assertEqual(validateAssetNetworkParts(explode('|', 'ADD|TGR|BADNET')), false, 'invalid network');
assertEqual(validateAssetNetworkParts(explode('|', 'ADD|TGR')), false, 'missing parts');
assertEqual(isValidHistoryType('add'), true, 'valid history type');
assertEqual(isValidHistoryType('unknown'), false, 'invalid history type');

fwrite(STDOUT, "All callback tests passed.\n");
?>
