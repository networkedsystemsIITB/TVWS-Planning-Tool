awk -F ',' '{print > "input_places_propagation_model_for_sec_base_stations_"$1".csv"}' /var/www/html/tvws_tool/Thane/csv/place_pop_lat_long_alt_bw_range.csv
