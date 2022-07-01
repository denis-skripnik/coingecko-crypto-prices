<?php
require_once('functions.php');
$t = [];
$t['ru'] = require 'lng/ru.php';
$t['en'] = require 'lng/en.php';

$api_key = 'ADD_HEAR_API_KEY';

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
 
include_once ('telegramgclass.php');   

$tg = new tg($api_key);
$sended = [];
$chat_id;
$text;
if (isset($arr['message']['from']['language_code'])) $lang = $arr['message']['from']['language_code'];
if (isset($arr['callback_query']['from']['language_code'])) $lang = $arr['callback_query']['from']['language_code'];
if (isset($arr['callback_query']['message']['from']['language_code'])) $lang = $arr['callback_query']['message']['from']['language_code'];
if ($lang !== 'ru') $lang = 'en';
if (isset($arr['message']['chat']['id'])) $chat_id = $arr['message']['chat']['id'];
if (isset($arr['callback_query']['message']['chat']['id'])) $chat_id = $arr['callback_query']['message']['chat']['id'];
    if (isset($arr['callback_query']['data'])) $text = $arr['callback_query']['data'];
if (isset($arr['message']['text'])) $text = $arr['message']['text'];
    $action = mb_substr($text, 1);
    $action = explode("@", $action)[0];
    if (isset($arr['message']) && ($text[0] === '$' or $text[0] === '/')) {
        $tg->delete($chat_id, $arr['message']['message_id']);
    }

if ($text && $text === '/start' || $text && strpos($text, '/start') !== false || $text && $text === '/help' || $text && strpos($text, '/help') !== false) {
        $msg = $t[$lang]['home_text'];
    $arInfo["inline_keyboard"][0][0]["url"] = 'https://t.me/blind_dev_bot';
    $arInfo["inline_keyboard"][0][0]['text'] = 'Main bot';
    $arInfo["inline_keyboard"][0][1]["url"] = 'https://t.me/blind_dev_chat';
    $arInfo["inline_keyboard"][0][1]['text'] = 'Chat';
    $arInfo["inline_keyboard"][1][0]["url"] = 'https://dpos.space';
    $arInfo["inline_keyboard"][1][0]['text'] = 'Multi blockchains site';
    $arInfo["inline_keyboard"][1][1]["url"] = 'https://github.com/denis-skripnik';
    $arInfo["inline_keyboard"][1][1]['text'] = 'Author github';
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
} else if ($text && strpos($text, '/sma') !== false) {
$data = explode(' ', $text);
$pair = mb_strtolower($data[1]);
[$coin_id, $vs_currency] = explode('_', $pair);
$days = (int)$data[2];
$page = getPage('https://api.coingecko.com/api/v3/coins/'.$coin_id.'/ohlc?vs_currency='.$vs_currency.'&days=max');
$prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency='.$vs_currency.'&ids='.$coin_id.'&order=market_cap_desc');
if ($page && count($page) > 0 && $prices && count($prices) > 0) {
    $price = $prices[0]['current_price'];
    $amount = 0;
    rsort($page);
    foreach($page as $key => $el) {
$day = $key + 1;
        if ($day > $days) break;
$amount += (float)$el[3];
    }
    $amount /= $days;
$zone = $t[$lang]['support_area'].$amount;
if ($amount > $price) $zone = $t[$lang]['resistance_zone'].$amount;
    $msg = $t[$lang]['now_price'].$price.'.
'.$zone.$t[$lang]['days_count'].$days;
$arInfo["inline_keyboard"] = [];
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} else if ($text && strpos($text, '/mc') !== false) {
    $data = explode(' ', $text);
    $list = mb_strtolower($data[1]);
    
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&symbols='.mb_strtolower($list).'&order=market_cap_desc&per_page=250&page=1&sparkline=false');
if (count($prices) === 2) {
    $token1 = $prices[0];
    $token2 = $prices[1];

    $price_data1 = sprintf("%.11f", $token1['current_price']);
    $price_data2 = sprintf("%.11f", $token2['current_price']);
    $msg = $t[$lang]['market_cap'].' '.$token1['symbol'].' = '.$token1['market_cap'].', '.$token2['symbol'].' = '.$token2['market_cap'];
$cap1 = $token1['market_cap'];
$cap2 = $token2['market_cap'];
$mc_change = $cap1 / $cap2;
$big_price = $token1['current_price'];
$small_price = $token2['current_price'];
$small_symbol = $token2['symbol'];
if ($cap2 > $cap1) {
    $mc_change = $cap2 / $cap1;
    $big_price = $token2['current_price'];
$small_price = $token1['current_price'];
$small_symbol = $token1['symbol'];
}
    $res_price = $small_price * $mc_change;
    $msg .= '
'.$t[$lang]['res_price'].' '.$small_symbol.' = '.$res_price.' USD';
$arInfo["inline_keyboard"] = [];
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if (count($prices) > 2) {
        $msg = $t[$lang]['select_pair'];
    $arInfo["inline_keyboard"] = [];
    $row = 0;
    $b_counter = 1;
    foreach ($prices as $key => $token) {
        foreach ($prices as $key2 => $token2) {
        if ($key2 === $key) continue;
            $pair = $token['id'].','.$token2['id'];
        $projects = $token['name'].','.$token2['name'];
            if ($key2 < $key) {
                $pair = $token2['id'].','.$token['id'];
                $projects = $token2['name'].','.$token['name'];
            }
            $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '/comcap '.$pair;
    $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = $projects;
    if ($b_counter % 3 == 0) {
        $row++;
        $b_counter = 1;
    } else {
        $b_counter++;
    }
} // end foreach 2.
} // end foreach 1.
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} else if ($text && strpos($text, '/comcap') !== false) {
    $data = explode(' ', $text);
    $list = mb_strtolower($data[1]);
    
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.mb_strtolower($list).'&order=market_cap_desc&per_page=250&page=1&sparkline=false');
if (count($prices) === 2) {
    $token1 = $prices[0];
    $token2 = $prices[1];

    $price_data1 = sprintf("%.11f", $token1['current_price']);
    $price_data2 = sprintf("%.11f", $token2['current_price']);
    $msg = $t[$lang]['market_cap'].' '.$token1['symbol'].' = '.$token1['market_cap'].', '.$token2['symbol'].' = '.$token2['market_cap'];
$cap1 = $token1['market_cap'];
$cap2 = $token2['market_cap'];
$mc_change = $cap1 / $cap2;
$big_price = $token1['current_price'];
$small_price = $token2['current_price'];
$small_symbol = $token2['symbol'];
if ($cap2 > $cap1) {
    $mc_change = $cap2 / $cap1;
    $big_price = $token2['current_price'];
$small_price = $token1['current_price'];
$small_symbol = $token1['symbol'];
}
    $res_price = $small_price * $mc_change;
    $msg .= '
'.$t[$lang]['res_price'].' '.$small_symbol.' = '.$res_price.' USD';
$arInfo["inline_keyboard"] = [];
if (!isset($arr['message']) && isset($arr['callback_query'])) {
    $msg_id = $arr['callback_query']['message']['message_id'];
    $sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
    } else {
        $sended = $tg->send($chat_id, $msg, 0, $arInfo);
    }
}
} else if (strpos($text, '/') !== false) {
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&symbols='.mb_strtolower($action).'&order=market_cap_desc&per_page=250&page=1&sparkline=false&price_change_percentage=1h,24h,7d,14d,30d,1y');
if (count($prices) === 1) {
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
if (!isset($token['symbol'])) return;
    $msg = strtoupper($token['symbol']).'/USD (<a href="https://coingecko.com/en/coins/'.$token['id'].'">'.$token['id'].'</a>): '.$current_price.'
'.$t[$lang]['ath'].$token['ath'].' ('.$token['ath_change_percentage'].'%)
'.$t[$lang]['price_changes'].'
'.$t[$lang]['1h'].round($token['price_change_percentage_1h_in_currency'], 3).'%,
'.$t[$lang]['24h'].round($token['price_change_percentage_24h_in_currency'], 3).'%,
'.$t[$lang]['7d'].round($token['price_change_percentage_7d_in_currency'], 3).'%,
'.$t[$lang]['14d'].round($token['price_change_percentage_14d_in_currency'], 3).'%,
'.$t[$lang]['1mo'].round($token['price_change_percentage_30d_in_currency'], 3).'%,
'.$t[$lang]['1y'].round($token['price_change_percentage_1y_in_currency'], 3).'%.
';
$arInfo["inline_keyboard"] = [];
$sended = $tg->send($chat_id, $msg, 0, $arInfo);
} else if (count($prices) > 1) {
        $msg = $t[$lang]['select_project'];
    $arInfo["inline_keyboard"] = [];
    $row = 0;
    $b_counter = 1;
    foreach ($prices as $token) {
    $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '$'.$token['id'];
    $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = $token['name'];
    if ($b_counter % 3 == 0) {
        $row++;
        $b_counter = 1;
    } else {
        $b_counter++;
    }
}
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} else if (strpos($text, '$') !== false) {
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.mb_strtolower($action).'&order=market_cap_desc&per_page=250&page=1&sparkline=false&price_change_percentage=1h,24h,7d,14d,30d,1y');
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
if (!isset($token['symbol'])) return;
$msg = strtoupper($token['symbol']).'/USD (<a href="https://coingecko.com/en/coins/'.$token['id'].'">'.$token['id'].'</a>): '.$current_price.'
'.$t[$lang]['ath'].$token['ath'].' ('.$token['ath_change_percentage'].'%)
'.$t[$lang]['price_changes'].'
'.$t[$lang]['1h'].round($token['price_change_percentage_1h_in_currency'], 3).'%,
'.$t[$lang]['24h'].round($token['price_change_percentage_24h_in_currency'], 3).'%,
'.$t[$lang]['7d'].round($token['price_change_percentage_7d_in_currency'], 3).'%,
'.$t[$lang]['14d'].round($token['price_change_percentage_14d_in_currency'], 3).'%,
'.$t[$lang]['1mo'].round($token['price_change_percentage_30d_in_currency'], 3).'%,
'.$t[$lang]['1y'].round($token['price_change_percentage_1y_in_currency'], 3).'%.
';
$arInfo["inline_keyboard"] = [];
if (!isset($arr['message']) && isset($arr['callback_query'])) {
$msg_id = $arr['callback_query']['message']['message_id'];
$sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
} else {
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} // end if prices.
?>