awk -F ',' '{print > "input_places_population_within_radius_"$1".csv"}' /var/www/html/tvws_tool/London/csv/input_places_population_within_radius.csv
