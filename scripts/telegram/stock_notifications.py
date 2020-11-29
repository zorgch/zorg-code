#!/usr/bin/env python
# -*- coding: utf-8 -*-

# ~~~~~
# == Setup ==
# % pip install [--user] requests
# % pip install [--user] schedule
# % pip install [--user] yfinance
#
# Make Python-script executable:
# % chmod +x ./stock_notifications.py
#
# == Usage ==
# one time (without any threshold):
# % python3 ./stock_notifications.py "BTC-USD"
#
# running as a service (every 11 hours, if threshold above 99.9):
# % nohup python3 ./stock_notifications.py "TSLA" 99.9 39600 $
#
# ~~~~~
# Original source: https://codeburst.io/indian-stock-market-price-notifier-bot-telegram-92e376b0c33a
# Resources:
#  - Symbol: https://finance.yahoo.com/quote/TSLA?p=TSLA
#  - YF for Python: https://pypi.org/project/fix-yahoo-finance/0.1.30/
#  - YF4P docu: https://aroussi.com/post/python-yahoo-finance
#  - Telegram Bot API: https://core.telegram.org/bots/api#sendmessage
# ~~~~~
import sys

# Check for stock symbol as 1st parameter
if len(sys.argv) <= 1:
    print("Missing a valid symbol as 1st parameter. Use something like 'TSLA' or 'BTC-USD'...")
    quit()
else:
    symbol=sys.argv[1]

# Check 2nd parameter for price notification threshold
if len(sys.argv) <= 2:
    # Default: very low threshold of 0.01 (so it needs at least a change)
    price_threshold=0.01
elif float(sys.argv[2]) > 0.00:
    price_threshold=float(sys.argv[2])
else:
    print("Invalid 2nd parameter: must be a positive number as price change threshold (e.g. 1.00).")
    quit()

# Check for optional timer seconds as 3rd parameter
if len(sys.argv) <= 3:
    # Default: 1 minute = 60 seconds
    repeat=60
elif int(sys.argv[3]) >= 1:
    repeat=int(sys.argv[3])
else:
    print("Invalid 3rd parameter: must be a number representing seconds to re-run script (e.g. 3600).")
    quit()

# Global vars
currency=''
prev_price=0
prev_diff=0
price=0

def getStock():
    global symbol
    global price_threshold
    global currency
    global prev_price
    global prev_diff
    global price
    
    import botconfigs as bot
    if len(bot.token) > 40 and len(bot.chat) > 5:
        bot_token=bot.token
        bot_chatID=bot.chat
    else:
        print("Missing botconfigs!")
        quit()
    
    import yfinance as yf
    # Get currency (only once)
    if currency == '':
        import json
        ticker_meta=yf.Ticker(symbol)
        ticker_dict=ticker_meta.info
        ticker_json=json.dumps(ticker_dict)
        ticker_json=json.loads(ticker_json)
        if ticker_json["currency"] != '':
            currency=ticker_json["currency"]
    
    ticker=yf.download(symbol, period="1d")
    #import datetime
    #ticker=pdr.get_data_yahoo(symbol, start=datetime.datetime(2020, 11, 25), end=datetime.datetime(2020, 12, 31))
    #DEBUG: print(ticker)
    if prev_price == 0:
        prev_price=(ticker["Open"][0]).round(2)
    else:
        prev_price=price
    price=(ticker["Close"][0]).round(2)
    if price == prev_price:
        price_diff = 0
        price_change_str = ''
        price_diff_str = ''
    elif price > prev_price:
        price_diff = (price-prev_price).round(2)
        price_change_str = "surged"
        price_diff_str = "("+price_change_str+" +"+str("{0:,.2f}".format(price_diff)).replace(',', '\'')+")"
    else:
        price_diff = (prev_price-price).round(2)
        price_change_str = "dropped"
        price_diff_str = "("+price_change_str+" -"+str("{0:,.2f}".format(price_diff)).replace(',', '\'')+")"
    
    # Price diff above given threshold AND change of stock price
    if abs(price_diff) >= price_threshold and abs(price_diff) > abs(prev_diff):
        import requests
        import urllib.parse
        message=symbol+" @ *"+currency+" "+str("{0:,.2f}".format(price)).replace(',', '\'')+"* "+price_diff_str
        message=message.replace("-","\-")
        message=message.replace("+","\+")
        message=message.replace(".","\.")
        message=message.replace("(","\(")
        message=message.replace(")","\)")
        message=message.replace("?","\?")
        message=message.replace("^","\^")
        message=message.replace("$","\$")
        message=urllib.parse.quote_plus(message)
        #DEBUG: print(message)
        send='https://api.telegram.org/bot' + bot_token + '/sendMessage?parse_mode=MarkdownV2&disable_notification=true&chat_id=' + bot_chatID + '&text=' + message
        #DEBUG: print(send)
        response=requests.get(send)
        #DEBUG: print(response)
    
    # Store price_diff for next iteration
    prev_diff = price_diff

import threading
import time
import schedule
while True:
    getStock()
    time.sleep(repeat)
