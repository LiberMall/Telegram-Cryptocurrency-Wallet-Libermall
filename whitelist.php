<?php
$allowedAssets = ['TON', 'TGR'];
$allowedNetworks = ['TON', 'BEP20'];
$allowedHistoryTypes = ['add', 'pauout', 'trans', 'exchange'];

function isValidAsset($asset) {
    global $allowedAssets;
    return in_array($asset, $allowedAssets, true);
}

function isValidNetwork($network) {
    global $allowedNetworks;
    return in_array($network, $allowedNetworks, true);
}

function isValidHistoryType($type) {
    global $allowedHistoryTypes;
    return in_array($type, $allowedHistoryTypes, true);
}

function validateAssetNetworkParts(array $parts) {
    if (count($parts) < 3) {
        return false;
    }
    return isValidAsset($parts[1]) && isValidNetwork($parts[2]);
}
?>
