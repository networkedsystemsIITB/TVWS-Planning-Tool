awk -F ',' '{print > "sec_tx_propagation_model_for_sec_base_stations_"$1".csv"}' /var/www/html/tvws_tool/London/csv/propagation_model_for_sec_base_stations_input.csv
