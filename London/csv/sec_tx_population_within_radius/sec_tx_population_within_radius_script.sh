awk -F ',' '{print > "sec_tx_population_within_radius_"$1".csv"}' /var/www/html/tvws_tool/London/csv/sec_tx_population_within_radius.csv
