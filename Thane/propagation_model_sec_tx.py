from collections import OrderedDict
from math import *
import json
import sys
import os
import glob
import re



def calculate_intermediate_path_loss(propagation_model, radius_edge_data, channel_frequency, secondary_base_station_height, secondary_mobile_station_height):
    if propagation_model == 'Hata':
        environment_type = 'rural'                                      # Environment type - 'urban', 'suburban' or 'rural'
        antenna_height_correction_factor = 0.8 + (1.1 * log10 (channel_frequency) - 0.7) * secondary_mobile_station_height - (1.56 * log10 (channel_frequency)) #For small or medium-sized city

        A = 69.55 + 26.16 * log10 (channel_frequency) - 13.82 * log10 (secondary_base_station_height) - antenna_height_correction_factor
        B = 44.9 - 6.55 * log10 (secondary_base_station_height)
        C = 5.4 + 2 * (log10 (channel_frequency / 28)) ** 2
        D = 40.94 + 4.78 * (log10 (channel_frequency)) ** 2 - 18.33 * log10 (channel_frequency)

        if environment_type == 'urban':
            inter_path_loss = A + B * log10(radius_edge_data)
        elif environment_type == 'suburban':
            inter_path_loss = A + B * log10(radius_edge_data) - C
        elif environment_type == 'rural':
            inter_path_loss = A + B * log10(radius_edge_data) - D


    elif propagation_model == 'Egli':
        # Egli path loss formula: Path loss (dB) = 117 + 40 * log10(coverage_radius) + 20 * log10(channel_frequency) \
        # - 20 * log10(secondary_base_station_height x secondary_mobile_station_height)
        # Here, coverage_radius is in miles and secondary_base_station_height and secondary_mobile_station_height are in feet
        # channel_frequency is in MHz
        secondary_base_station_height_feet = secondary_base_station_height * 3.28084
        secondary_mobile_station_height_feet = secondary_mobile_station_height * 3.28084

        radius_edge_data_miles = float(radius_edge_data) * 0.621371     # in miles
        inter_path_loss = 117 + (40 * log10 (radius_edge_data_miles)) + (20 * log10 (channel_frequency)) - (20 * log10 (secondary_base_station_height_feet * secondary_mobile_station_height_feet))


    elif propagation_model == 'Free_Space':
        # Free space path loss formula: Path loss (dB) = 32.5 + 20 * log10(coverage_radius) + 20 * log10(channel_frequency)
        # Here, coverage_radius is in kms and channel_frequency is in MHz
        inter_path_loss = 32.5 + (20 * log10 (radius_edge_data)) + (20 * log10 (channel_frequency))


    elif propagation_model == 'Plane_Earth':
        # Plane earth path loss formula: Path loss (dB) = 40 * log10(coverage_radius) - 20 * log10(secondary_base_station_height)
        # - 20 * log10(secondary_mobile_station_height)
        # Here, coverage_radius, secondary_base_station_height and secondary_mobile_station_height are in metres
        radius_edge_data_meters = radius_edge_data * 1000
        inter_path_loss = (40 * log10 (radius_edge_data_meters)) - (20 * log10 (secondary_base_station_height)) - (20 * log10 (secondary_mobile_station_height))

    return inter_path_loss



def alphabeticalSort(value):
    return value.rsplit('_', 1)[1]



for fin1 in sorted(glob.glob('./csv/sec_tx_propagation_model_for_sec_base_stations/*.csv'), key=alphabeticalSort):
    infile = open(fin1,'r')
    name = fin1.rsplit('_', 1)[1]

    per_row = []
    for line in infile:
        per_row.append(line.split(','))

    per_column = zip(*per_row)              # Matrix containing the entire input file "infile"
    population_data = per_column[1]         # List of population data values
    coverage_radius_data = per_column[6]    # List of coverage radius data values
    extracted_data = []                     # List of data to be extracted

    for i in range(len(population_data)):
        extracted_data.append(per_row[i])

    data = [[x.strip() for x in y] for y in extracted_data]

    population_file = open('./csv/sec_tx_population_within_radius/sec_tx_population_within_radius_'+name, 'r')
    per_row_file = []
    for line in population_file:
        per_row_file.append(line.split(','))

    per_column_file = zip(*per_row_file)                        # Matrix containing the entire input file "population_file"
    sec_tx_data = per_column_file[0]                            # List of secondary transmitter locations
    sec_tx_data = list(sec_tx_data)
    population_within_radius_data = per_column_file[1]          # List of population within radius data values
    population_within_radius_data = list(population_within_radius_data)
    population_within_radius_data = [int(value) for value in population_within_radius_data]
    radius_edge_data = per_column_file[2]                       # List of coverage radius edge data values
    radius_edge_data = list(radius_edge_data)
    radius_edge_data = map(lambda s: s.strip(), radius_edge_data)
    radius_edge_data = [int(value) for value in radius_edge_data]

    boltzmann_constant = 1.38e-23;                  # Boltzmann Constant
    temperature_kelvin = 30 + 273.15;               # Temperature in Kelvin

    propagation_model = sys.argv[1]                 # Propagation model - 'Hata', 'Egli', 'Free_Space', 'Plane_Earth'
    transmit_power = int(sys.argv[2])               # Transmit Power in dBm, default = 36 dBm
    channel_bandwidth = float(sys.argv[3]) * 10**6  # Channel Bandwidth in Hz, default = 8 MHz
    channel_frequency = int(sys.argv[4])            # Channel Frequency in MHz, default = 470 MHz

    secondary_mobile_station_height = 1.5           # Height of mobile station antenna in meters

    thermal_noise_dBm = 10 * log10 (boltzmann_constant * temperature_kelvin * channel_bandwidth/1e-3)   # Thermal Noise in dBm
    thermal_noise_watts = (10 ** (thermal_noise_dBm/10)/1000)                                           # Thermal Noise in watts

    coverage_radius = [0 for x in range(len(data))]
    inter_path_loss = [[] for i in xrange(len(data))]
    inter_throughput_Mbps = [[] for i in xrange(len(data))]
    inter_throughput_per_user_Mbps = [[] for i in xrange(len(data))]
    inter_utility_value = [[] for i in xrange(len(data))]
    population_sum = [0 for x in range(len(data))]
    weighted_utility_value = [0 for x in range(len(data))]
    weighted_average_throughput = [0 for x in range(len(data))]

    for k in range(len(radius_edge_data)):
        radius_edge_data[k] = radius_edge_data[k]+1

    print "\nRadius edge data = ", radius_edge_data
    print "Population with radius data = ", population_within_radius_data

    for i in range(len(data)):
        secondary_base_station_height = float(data[i][4]) + 30

        print "\n============================================================================"
        print "============================================================================"
        print "Place = ", data[i][0]
        print "Population = ", data[i][1]

        population_sum[i] = 0
        population_uv_sum_of_product = 0
        population_throughput_sum_of_product = 0
        throughput_sum = 0

        for j in range(len(radius_edge_data)):
            print "\n------------------------------------------------------------------------"
            print ":: Intermediate Values ::"

            population_within_radius_data[j] = 0.1 * population_within_radius_data[j]
            if (population_within_radius_data[j] < 1):
                population_within_radius_data[j] = 1
            print "Internet Population (10%%) Within %d km Radius[%d][%d] = %d" % (radius_edge_data[j], i, j, population_within_radius_data[j])

            population_sum[i] = population_sum[i] + population_within_radius_data[j]
            population_sum[i] = int(population_sum[i])

            inter_path_loss_result = calculate_intermediate_path_loss(propagation_model, radius_edge_data[j], channel_frequency, secondary_base_station_height, secondary_mobile_station_height)

            inter_path_loss[i].append(inter_path_loss_result)
            print "Path Loss[%d][%d] = %f dB" % (i, j, inter_path_loss[i][j])

            inter_received_power_dBm = transmit_power - inter_path_loss[i][j]
            print "Received Power[%d][%d] = %f dBm" % (i, j, inter_received_power_dBm)

            inter_received_power_watts = (10 ** (inter_received_power_dBm/10)/1000)
            print "Received Power[%d][%d] = %.15f watts" % (i, j, inter_received_power_watts)
            print "Thermal Noise = %.15f watts" % thermal_noise_watts

            signal_to_noise_ratio = inter_received_power_watts/thermal_noise_watts
            print "Signal to Noise Ratio[%d][%d] = %f" % (i, j, signal_to_noise_ratio)

            inter_throughput_Mbps[i].append(round((channel_bandwidth * log((1 + signal_to_noise_ratio), 2)) * 1e-6, 2)) # Throughput in Mbps
            print "Throughput[%d][%d] = %f Mbps" % (i, j, inter_throughput_Mbps[i][j])

            inter_throughput_per_user_Mbps[i].append(inter_throughput_Mbps[i][j]/population_within_radius_data[j])  # Throughput per user in Mbps
            print "Throughput/User[%d][%d] = %f Mbps" % (i, j, inter_throughput_per_user_Mbps[i][j])

            inter_utility_value[i].append(inter_throughput_Mbps[i][j]/(float(data[i][5]) * population_within_radius_data[j]))
            print "Utility Value[%d][%d] = %f" % (i, j, inter_utility_value[i][j])

            inter_population_square = population_within_radius_data[j]**2
            population_uv_sum_of_product = population_uv_sum_of_product + (inter_population_square * inter_utility_value[i][j])
            population_throughput_sum_of_product = population_throughput_sum_of_product + (population_within_radius_data[j] * inter_throughput_Mbps[i][j])
            throughput_sum = throughput_sum + inter_throughput_Mbps[i][j]

        print "\nPopulation Sum[%d] = %d" % (i, population_sum[i])

        if (population_sum[i] < 1):
            population_sum[i] = 1

        weighted_average_throughput[i] = population_throughput_sum_of_product/population_sum[i]
        weighted_utility_value[i] = population_uv_sum_of_product/population_sum[i]
        print "Weighted Average Throughput Value[%d] = %f" % (i, weighted_average_throughput[i])
        print "Weighted Utility Value[%d] = %f" % (i, weighted_utility_value[i])

    for i in range(len(data)):
        coverage_radius = float(data[i][6])
        population_sum[i] = str(population_sum[i])
        weighted_utility_value[i] = round(weighted_utility_value[i], 2)
        weighted_utility_value[i] = str(weighted_utility_value[i])
        weighted_average_throughput[i] = round(weighted_average_throughput[i], 2)
        weighted_average_throughput[i] = str(weighted_average_throughput[i])

    for i, j in zip(data, inter_throughput_per_user_Mbps):
        i.append(j)

    for i, j in zip(data, population_sum):
        i.append(j)

    for i, j in zip(data, weighted_utility_value):
        i.append(j)

    for i, j in zip(data, weighted_average_throughput):
        i.append(j)

    header_list = ["place", "population", "lat", "long", "alt", "bw_reqt", "coverage", "throughput_list", "users_served", "weighted_utility", "weighted_average_throughput"]
    keys = header_list

    dict_content = [dict(zip(keys, values)) for values in data[0:]]
    dict_content = sorted(dict_content)

    json_dump = json.dumps(dict_content)

    print "\n============================================================================"
    print json_dump
    print "\n============================================================================"

    name = str(name)
    temp = os.path.splitext(name)[0]

    fid = open('./result/result_'+temp+'.json', 'w')
    fid.write(str(json_dump))
    fid.close()
