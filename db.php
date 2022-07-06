<?php
function getFavorites($chat_id) {
    if (!file_exists('db/favorites.json')) return false;
$file = json_decode(file_get_contents('db/favorites.json'), true);
if (isset($file[$chat_id])) {
    return $file[$chat_id];
} else {
    return false;
}
}

function addFavorites($chat_id, $coin) {
    $favorites = [];
    if (file_exists('db/favorites.json')) {
     $favorites = json_decode(file_get_contents('db/favorites.json'), true);
    }

    $coins = [];
    if (!isset($favorites[$chat_id])) $favorites[$chat_id] = [];
    if (isset($favorites[$chat_id])) $coins = $favorites[$chat_id];

if (array_search($coin, $coins) === false) {
    array_push($coins, $coin);
    $favorites[$chat_id] = $coins;
    file_put_contents('db/favorites.json', json_encode($favorites, JSON_OBJECT_AS_ARRAY, JSON_PRETTY_PRINT));
    return true;
} else {
    return false;
}
}

function deleteFavorites($chat_id, $coin) {
    $favorites = [];
    if (file_exists('db/favorites.json')) {
     $favorites = json_decode(file_get_contents('db/favorites.json'), true);
    }

    $coins = [];
if (isset($favorites[$chat_id])) $coins = $favorites[$chat_id];

if (array_search($coin, $coins) !== false) {
unset($coins[array_search($coin, $coins)]);
error_log(json_encode(array_search($coin, $coins)));
$coins = array_values($coins);
$favorites[$chat_id] = $coins;
if (count($coins) === 0) unset($favorites[$chat_id]);
    file_put_contents('db/favorites.json', json_encode($favorites, JSON_OBJECT_AS_ARRAY, JSON_PRETTY_PRINT));
    return true;
} else {
    return false;
}
}

?>