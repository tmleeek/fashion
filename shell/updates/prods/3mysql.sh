#!/bin/bash
mysql -uroot -pf4sh1oN321 fashion < 1frame_size.sql
mysql -uroot -pf4sh1oN321 fashion < 2stats.visb.sql

mysql -uroot -pf4sh1oN321 fashion < stock.sql
mysql -uroot -pf4sh1oN321 fashion < _decimal.sql
mysql -uroot -pf4sh1oN321 fashion < _int.sql
mysql -uroot -pf4sh1oN321 fashion < _text.sql
mysql -uroot -pf4sh1oN321 fashion < _varchar.sql