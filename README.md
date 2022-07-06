# coingecko-crypto-prices
 My bot for getting prices from coingecko with short stats.

1. Add your API key in telegrambot.php;
2. Upload to your hosting with site.
3. Change ADD_HEAR_YOUR_API_KEY to your API key and YOUR_URL to url of bot file:
https://api.telegram.org/botADD_HEAR_YOUR_API_KEY/setWebhook?url=YOUR_URL/telegrambot.php
and click for add webhook.
4. Go to bot and click /start.
5. Send /BTC and other crypto currensy for getting price and stats.
6. Set the permissions for the db/favorites file.json 0777.
7. Send /interest to get favorites with a list of previously added tokens.
8. send:
/sma bitcoin_usd 23
and other for get SMA.
bitcoin_usd - pair with coingecko id and symbol.
23 - number of days.
9. send
/mc ATOM,SOL
to get the ATOM rate when it reaches SOL capitalization. In the case of specifying other tickers, it may be the opposite (depends on whose capitalization is greater or less).
10. wait Will be updates.