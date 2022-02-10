<?php
require_once('functions.php');

$conf = json_decode(file_get_contents(__DIR__.'/config.json'), true);
$api_key = $conf['api_key'];

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
 
include_once ('telegramgclass.php');   

$tg = new tg($api_key);
$sended = [];
$chat_id;
$text;
if (isset($arr['message']['chat']['id'])) $chat_id = $arr['message']['chat']['id'];
if (isset($arr['callback_query']['message']['chat']['id'])) $chat_id = $arr['callback_query']['message']['chat']['id'];
    if (isset($arr['callback_query']['data'])) $text = $arr['callback_query']['data'];
if (isset($arr['message']['text'])) $text = $arr['message']['text'];
    $action = mb_substr($text, 1);
    $action = explode("@", $action)[0];

    if ($text && $text === '/start' || $text && strpos($text, '/start') !== false || $text && $text === '/help' || $text && strpos($text, '/help') !== false) {
    $msg = $conf['home_text'];
    $arInfo["inline_keyboard"][0][0]["url"] = 'https://t.me/blind_dev_bot';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Main bot';
    $arInfo["inline_keyboard"][0][1]["url"] = 'https://t.me/blind_dev_chat';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Chat';
    $arInfo["inline_keyboard"][1][0]["url"] = 'https://dpos.space';
    $arInfo["inline_keyboard"][1][0]['text'] = 'Multi blockchains site';
    $arInfo["inline_keyboard"][1][1]["url"] = 'https://github.com/denis-skripnik';
    $arInfo["inline_keyboard"][1][0]['text'] = 'Author github';
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
    } else if ($text && $text === '/interest' || $text && strpos($text, '/interest') !== false) {
        $tokens = file_get_contents("https://dpos.space/crypto-prices/tokens.txt");
$prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.$tokens.'&order=market_cap_desc&per_page=250&page=1&sparkline=false&price_change_percentage=24h');
$bip_price = 0;
$text2 = 'Список интересных мне криптовалют с их ценами на данный момент и процентами изменения за сутки:
';
foreach ($prices as $coin) {
    $direction = '⬆';
    if ($coin['price_change_percentage_24h'] < 0) $direction = '⬇';
    $text2 .= $coin['id'].' ('.$coin['symbol'].') - $'.$coin['current_price'].' ('.$direction.' '.$coin['price_change_percentage_24h'].'%),
';
if ($coin['symbol'] === 'bip') $bip_price = $coin['current_price'];
}

$viz = getPage('https://api.minter.one/v2/swap_pool/0/1969');
$viz_amount0 = (float)$viz['amount0'] / (10 ** 18);
$viz_amount1 = (float)$viz['amount1'] / (10 ** 18);
$viz_bip_price = $viz_amount0 / $viz_amount1;
$viz_usd_price = $viz_bip_price * $bip_price;
$viz_usd_price = round($viz_usd_price, 5);
$text2 .= 'Viz (VIZ) - '.$viz_usd_price;

$arInfo["inline_keyboard"] = [];
$sended = $tg->send($chat_id, $text2, 0, $arInfo);
} else {
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&symbols='.mb_strtolower($action).'&order=market_cap_desc&per_page=250&page=1&sparkline=false&price_change_percentage=1h,24h,7d,14d,30d,1y');
$token = $prices[0];
$price_data = sprintf("%.11f", $token['current_price']);
$price_numbers = explode('.', $price_data);
$current_price = $price_numbers[0];
$not_zero_numbers = 0;
for ($i = 0; $i < mb_strlen($price_data); $i++) {
    $char = mb_substr($price_numbers[1], $i, 1);
    if ($i === 0) $current_price .= '.';
    if ((float)$char > 0) $not_zero_numbers += 1;
    if ($not_zero_numbers === 2 && (float)$char == 0) {
        break;
    } else if ($not_zero_numbers <= 2) {
        $current_price .= $char;        
    }
    }
if ($not_zero_numbers === 0) {
    $current_price = $price_numbers[0];
}

    $msg = strtoupper($token['symbol']).'/USD (<a href="https://coingecko.com/en/coins/'.$token['id'].'">'.$token['id'].'</a>): '.$current_price.'.
ath: $ '.$token['ath'].' ('.$token['ath_change_percentage'].'%)
    Price changes:
1 hour: '.round($token['price_change_percentage_1h_in_currency'], 3).'%,
1 day: '.round($token['price_change_percentage_24h_in_currency'], 3).'%,
7 days: '.round($token['price_change_percentage_7d_in_currency'], 3).'%,
14 days: '.round($token['price_change_percentage_14d_in_currency'], 3).'%,
1 month: '.round($token['price_change_percentage_30d_in_currency'], 3).'%,
1 year: '.round($token['price_change_percentage_1y_in_currency'], 3).'%.
';
$arInfo["inline_keyboard"] = [];
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} // end if prices.
?>