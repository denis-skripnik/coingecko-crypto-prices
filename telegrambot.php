<?php
require_once('functions.php');
require_once('db.php');
$t = [];
$t['ru'] = require 'lng/ru.php';
$t['en'] = require 'lng/en.php';

$api_key = 'ADD_HERE_API_KEY';

$body = file_get_contents('php://input'); 
$arr = json_decode($body, true); 
 
include_once ('telegramgclass.php');   

$tg = new tg($api_key);
$sended = [];
$chat_id;
$from_id;
$text;
$lang = 'en';
if (isset($arr['message']['from']['language_code'])) $lang = $arr['message']['from']['language_code'];
if (isset($arr['callback_query']['from']['language_code'])) $lang = $arr['callback_query']['from']['language_code'];
if (isset($arr['callback_query']['message']['from']['language_code'])) $lang = $arr['callback_query']['message']['from']['language_code'];
if (!isset($lang) || $lang !== 'ru') $lang = 'en';
if (isset($arr['message']['chat']['id'])) $chat_id = $arr['message']['chat']['id'];
if (isset($arr['callback_query']['message']['chat']['id'])) $chat_id = $arr['callback_query']['message']['chat']['id'];
if (isset($arr['message']['from']['id'])) $from_id = $arr['message']['from']['id'];
if (isset($arr['callback_query']['from']['id'])) $from_id = $arr['callback_query']['from']['id'];    
if (isset($arr['callback_query']['data'])) $text = $arr['callback_query']['data'];
if (isset($arr['message']['text'])) $text = $arr['message']['text'];
    error_log(json_encode($arr));
if (!isset($text)) return;

$action = mb_substr($text, 1);
    $action = explode("@", $action)[0];
    if (isset($arr['message']) && isset($text) && ($text[0] === '$' or $text[0] === '/')) {
        $tg->delete($chat_id, $arr['message']['message_id']);
    }

    if (isset($text) && $text === '/start' || isset($text) && strpos($text, '/start') !== false || isset($text) && $text === '/help' || isset($text) && strpos($text, '/help') !== false) {
    if (strpos($text, 'portfoleo') !== false) {
$portfoleo_id = explode('portfoleo', $text)[1];
        $coins = getFavorites($portfoleo_id);
        if ($coins !== false) {
            $msg = $t[$lang]['select_favorites_project'].'https://t.me/blind_dev_prices_bot?start=portfoleo'.$portfoleo_id;
            $arInfo["inline_keyboard"] = [];
            $row = 0;
            $b_counter = 1;
            foreach ($coins as $token) {
            $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '$'.$token;
            $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = $token;
            if ($b_counter % 3 == 0) {
                $row++;
                $b_counter = 1;
            } else {
                $b_counter++;
            }
        }
    } else {
            $msg = $t[$lang]['not_favorites'];
            $arInfo["inline_keyboard"] = [];
        }
    } else {
        $msg = $t[$lang]['home_text'];
        $arInfo["inline_keyboard"][0][0]["url"] = 'https://t.me/blind_dev_bot';
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['main_bot'];
        $arInfo["inline_keyboard"][0][1]["url"] = 'https://t.me/blind_dev_chat';
        $arInfo["inline_keyboard"][0][1]['text'] = $t[$lang]['chat'];
        $arInfo["inline_keyboard"][1][0]["url"] = 'https://t.me/blind_dev';
        $arInfo["inline_keyboard"][1][0]['text'] = $t[$lang]['channel'];
        $arInfo["inline_keyboard"][1][1]["url"] = 'https://github.com/denis-skripnik';
        $arInfo["inline_keyboard"][1][1]['text'] = $t[$lang]['github'];    
    }
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
    } else if (isset($text) && $text === '/interest' || isset($text) && strpos($text, '/interest') !== false) {
        $coins = getFavorites($chat_id);
        if ($coins !== false) {
            $msg = $t[$lang]['list_favorites_project'].'https://t.me/blind_dev_prices_bot?start=portfoleo'.$chat_id;
            $arInfo["inline_keyboard"] = [];
            $row = 0;
            $b_counter = 1;
            foreach ($coins as $token) {
            $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '$'.$token;
            $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = $token;
            if ($b_counter % 3 == 0) {
                $row++;
                $b_counter = 1;
            } else {
                $b_counter++;
            }
        }
    } else {
            $msg = $t[$lang]['not_favorites'];
            $arInfo["inline_keyboard"] = [];
        }
            $sended = $tg->send($chat_id, $msg, 0, $arInfo);
        } else if (isset($text) && $text === '/my_list' || isset($text) && strpos($text, '/my_list') !== false) {
            $coins = getFavorites($chat_id);
            if ($coins !== false) {
                $msgs = [];
                $msgs[0] = $t[$lang]['list_favorites_project'].'https://t.me/blind_dev_prices_bot?start=portfoleo'.$chat_id.'
';
                $ids_list = array_map(function ($arr) {
                    return implode(',', $arr);
                }, array(array_splice($coins, 0, floor(10)), $coins));
foreach ($ids_list as $key => $ids_row) {
    if (!isset($msgs[$key])) $msgs[$key] = '';
    $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency=USD&ids='.mb_strtolower($ids_row).'&order=market_cap_desc&per_page=250&page=1&sparkline=false&price_change_percentage=1h,24h,7d,14d,30d,1y');
    foreach ($prices as $token) {
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
if (!isset($token['symbol'])) continue;
$msgs[$key] .= strtoupper($token['symbol']).'/USD (<a href="https://coingecko.com/en/coins/'.$token['id'].'">'.$token['id'].'</a>): '.$current_price.'
'.$t[$lang]['ath'].$token['ath'].' ('.$token['ath_change_percentage'].'%)
'.$t[$lang]['price_changes'].'
'.$t[$lang]['1h'].round($token['price_change_percentage_1h_in_currency'], 3).'%,
'.$t[$lang]['24h'].round($token['price_change_percentage_24h_in_currency'], 3).'%,
'.$t[$lang]['7d'].round($token['price_change_percentage_7d_in_currency'], 3).'%,
'.$t[$lang]['14d'].round($token['price_change_percentage_14d_in_currency'], 3).'%,
'.$t[$lang]['1mo'].round($token['price_change_percentage_30d_in_currency'], 3).'%,
'.$t[$lang]['1y'].round($token['price_change_percentage_1y_in_currency'], 3).'%.

';
}
}
            } else {
                $msg = $t[$lang]['not_favorites'];
            }
            $arInfo["inline_keyboard"] = [];
foreach ($msgs as $msg) {
    if (!isset($arr['message']) && isset($arr['callback_query'])) {
        $msg_id = $arr['callback_query']['message']['message_id'];
        $sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
        } else {
            $sended = $tg->send($chat_id, $msg, 0, $arInfo);
        }
}
        } else if (isset($text) && strpos($text, '/sma ') !== false && count(explode(' ', $text) >= 3)) {
            $data = explode(' ', $text);
            $pair = mb_strtolower($data[1]);
            [$ticker, $vs_currency] = explode('_', $pair);
            $days_list = $data[2];
            $prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency='.$vs_currency.'&symbols='.mb_strtolower($ticker).'&order=market_cap_desc&per_page=250&page=1&sparkline=false&price_change_percentage=1h,24h,7d,14d,30d,1y');
            if (count($prices) === 1) {
                $coin_id = $prices[0]['id'];
            $days = array_map('intval', explode(',', $days_list));
            sort($days);
            $page = getPage('https://api.coingecko.com/api/v3/coins/'.$coin_id.'/ohlc?vs_currency='.$vs_currency.'&days=max');
            if ($page && count($page) > 0 && $prices && count($prices) > 0) {
                $price = $prices[0]['current_price'];
                $amounts = [];
            $amount = 0;
                rsort($page);
                $day = 0;
                foreach($page as $el) {
for ($i = 0; $i < 4; $i++) {
    $day++;
    if ($day > $days[count($days)-1]) break;
    $amount += $el[3];
if (array_search($day, $days) !== false) {
if (!isset($amounts[$day])) $amounts[$day] = 0;
$amounts[$day] += $amount;
}
} // end for i.
                }
                $msg = $t[$lang]['now_price'].$price;
                foreach ($amounts as $day =>  $amount) {
                    $amount /= $day;
                    $zone = $t[$lang]['support_area'].$amount;
                    if ($amount > $price) $zone = $t[$lang]['resistance_zone'].$amount;
                    $msg .= '
            '.$zone.$t[$lang]['days_count'].$day;
                }
            $arInfo["inline_keyboard"] = [];
            $sended = $tg->send($chat_id, $msg, 0, $arInfo);
            }
        } else if (count($prices) > 1) {
                    $msg = $t[$lang]['select_project_for_sma'];
                $arInfo["inline_keyboard"] = [];
                $row = 0;
                $b_counter = 1;
                foreach ($prices as $token) {
                $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '/ma '.$token['id'].'_'.$vs_currency.' '.$days_list;
                $arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = $token['name'].' _ '.$vs_currency;
                if ($b_counter % 3 == 0) {
                    $row++;
                    $b_counter = 1;
                } else {
                    $b_counter++;
                }
            }
                $sended = $tg->send($chat_id, $msg, 0, $arInfo);
            }
                   } else if (isset($text) && strpos($text, '/ma ') !== false) {
                    $data = explode(' ', $text);
$pair = mb_strtolower($data[1]);
[$coin_id, $vs_currency] = explode('_', $pair);
$days_list = $data[2];
$days = array_map('intval', explode(',', $days_list));
sort($days);
$page = getPage('https://api.coingecko.com/api/v3/coins/'.$coin_id.'/ohlc?vs_currency='.$vs_currency.'&days=max');
$prices = getPage('https://api.coingecko.com/api/v3/coins/markets?vs_currency='.$vs_currency.'&ids='.$coin_id.'&order=market_cap_desc');
if ($page && count($page) > 0 && $prices && count($prices) > 0) {
    $price = $prices[0]['current_price'];
    $amounts = [];
$amount = 0;
    rsort($page);
    foreach($page as $key => $el) {
$day = $key + 1;
        if ($day > $days[count($days)-1]) break;
$amount += $el[3];
        if (array_search($day, $days) !== false) {
    if (!isset($amounts[$day])) $amounts[$day] = 0;
    $amounts[$day] += $amount;
}
    }
    $msg = $t[$lang]['now_price'].$price;
    foreach ($amounts as $day =>  $amount) {
        $amount /= $day;
        $zone = $t[$lang]['support_area'].$amount;
        if ($amount > $price) $zone = $t[$lang]['resistance_zone'].$amount;
        $msg .= '
'.$zone.$t[$lang]['days_count'].$day;
    }
$arInfo["inline_keyboard"] = [];
if (!isset($arr['message']) && isset($arr['callback_query'])) {
    $msg_id = $arr['callback_query']['message']['message_id'];
    $sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
    } else {
        $sended = $tg->send($chat_id, $msg, 0, $arInfo);
    }
}
} else if (isset($text) && strpos($text, '/mc') !== false) {
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
    $all_projects = [];
    foreach ($prices as $key => $token) {
        foreach ($prices as $key2 => $token2) {
        if ($key2 === $key) continue;
            $pair = $token['id'].','.$token2['id'];
        $projects = $token['name'].','.$token2['name'];
            if ($key2 < $key) {
                $pair = $token2['id'].','.$token['id'];
                $projects = $token2['name'].','.$token['name'];
            }
if (array_search($pair, $all_projects) === false) {
    array_push($all_projects, $pair);
    $arInfo["inline_keyboard"][$row][$b_counter-1]["callback_data"] = '/comcap '.$pair;
$arInfo["inline_keyboard"][$row][$b_counter-1]['text'] = $projects;
if ($b_counter % 3 == 0) {
$row++;
$b_counter = 1;
} else {
$b_counter++;
}
}
} // end foreach 2.
} // end foreach 1.
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} else if (isset($text) && strpos($text, '/comcap') !== false) {
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
$mc_change = 0;
if (isset($cap2)) $mc_change = $cap1 / $cap2;
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
} else if (isset($text) && strpos($text, '/+') !== false) {
    $coin_id = explode(' ', $text)[1];
$chat_user = [];
if ($chat_id < 0)         $chat_user = $tg->getChatMember($chat_id, $from_id);
if (isset($chat_user) && $chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $chat_id === $from_id) {
            $res = addFavorites($chat_id, $coin_id);
            if ($res === true) {
                $msg = $t[$lang]['added_to_favorites'].': '.$coin_id;
            } else {
                $msg = $t[$lang]['not_added_to_favorites'].': '.$coin_id;
            } // end if res.
                    } // yes user.
    else {
        $msg = $t[$lang]['no_access_to_change_favorites'];
    }
    $arInfo["inline_keyboard"][0][0]["callback_data"] = '$'.$coin_id;
    $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['back'];
                    $arInfo["inline_keyboard"][0][1]["callback_data"] = '/start';
                    $arInfo["inline_keyboard"][0][1]['text'] = $t[$lang]['home_button'];
    if (!isset($arr['message']) && isset($arr['callback_query'])) {
        $msg_id = $arr['callback_query']['message']['message_id'];
        $sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
        } else {
            $sended = $tg->send($chat_id, $msg, 0, $arInfo);
        }
} else if (isset($text) && strpos($text, '/-') !== false) {
    $coin_id = explode(' ', $text)[1];
    $chat_user = [];
    if ($chat_id < 0)         $chat_user = $tg->getChatMember($chat_id, $from_id);
    if (isset($chat_user) && $chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $chat_id === $from_id) {
    $res = deleteFavorites($chat_id, $coin_id);
if ($res === true) {
    $msg = $t[$lang]['deleted_favorite'].': '.$coin_id;
} else {
    $msg = $t[$lang]['not_deleted_favorite'].': '.$coin_id;
} // end if res.
} // yes user.
else {
$msg = $t[$lang]['no_access_to_change_favorites'];
}

$arInfo["inline_keyboard"][0][0]["callback_data"] = '$'.$coin_id;
$arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['back'];
                $arInfo["inline_keyboard"][0][1]["callback_data"] = '/start';
                $arInfo["inline_keyboard"][0][1]['text'] = $t[$lang]['home_button'];
    if (!isset($arr['message']) && isset($arr['callback_query'])) {
        $msg_id = $arr['callback_query']['message']['message_id'];
        $sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
        } else {
            $sended = $tg->send($chat_id, $msg, 0, $arInfo);
        }
} else if (isset($text) && $text[0] === '/') {
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
$chat_user = $tg->getChatMember($chat_id, $from_id);
if (isset($chat_user) && $chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $chat_id === $from_id) {
    $favorites = getFavorites($chat_id);
    if ($favorites !== false && count($favorites) > 0) {
    $isFavorites = 0;
        foreach ($favorites as $el) {
        if (strpos($el, $token['id']) !== false) {
            $isFavorites++;
        }
    }
    if ($isFavorites === 0) {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/+ '.$token['id'];
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['add_to_favorites'];
    } else {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/- '.$token['id'];
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['delete_from_favorites'];
    }
    } else {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/+ '.$token['id'];
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['add_to_favorites'];
    }
}
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
} else if (isset($text) && strpos($text, '$') !== false) {
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
$chat_user = [];
      $chat_user = $tg->getChatMember($chat_id, $from_id);
if (isset($chat_user) && $chat_user['result']['status'] === 'creator' || $chat_user['result']['status'] === 'administrator' || $chat_id === $from_id) {
    $favorites = getFavorites($chat_id);
    if ($favorites !== false && count($favorites) > 0) {
    $isFavorites = 0;
        foreach ($favorites as $el) {
        if (strpos($el, $token['id']) !== false) {
            $isFavorites++;
        }
    }
    if ($isFavorites === 0) {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/+ '.$token['id'];
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['add_to_favorites'];
    } else {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/- '.$token['id'];
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['delete_from_favorites'];
    }
    } else {
        $arInfo["inline_keyboard"][0][0]["callback_data"] = '/+ '.$token['id'];
        $arInfo["inline_keyboard"][0][0]['text'] = $t[$lang]['add_to_favorites'];
    }
}
if (!isset($arr['message']) && isset($arr['callback_query'])) {
$msg_id = $arr['callback_query']['message']['message_id'];
$sended = $tg->edit($chat_id, $msg_id, $msg, $arInfo);
} else {
    $sended = $tg->send($chat_id, $msg, 0, $arInfo);
}
} // end if prices.
?>