from math import *
import sys



def calculate_coverage_radius(propagation_model, channel_frequency, secondary_base_station_height, secondary_mobile_station_height):
    if propagation_model == 'Hata':
        environment_type = 'rural'                                      # Environment type - 'urban', 'suburban' or 'rural'
        antenna_height_correction_factor = 0.8 + (1.1 * log10 (channel_frequency) - 0.7) * secondary_mobile_station_height - (1.56 * log10 (channel_frequency)) #For small or medium-sized city

        A = 69.55 + 26.16 * log10 (channel_frequency) - 13.82 * log10 (secondary_base_station_height) - antenna_height_correction_factor
        B = 44.9 - 6.55 * log10 (secondary_base_station_height)
        C = 5.4 + 2 * (log10 (channel_frequency / 28)) ** 2
        D = 40.94 + 4.78 * (log10 (channel_frequency)) ** 2 - 18.33 * log10 (channel_frequency)

        if environment_type == 'urban':
            radius = 10 ** ((path_loss - A) / B)                        # radius for urban scenario
        elif environment_type == 'suburban':
            radius = 10 ** ((path_loss - A + C) / B)                    # radius for suburban scenario
        elif environment_type == 'rural':
            radius = 10 ** ((path_loss - A + D) / B)                    # radius for rural scenario


    elif propagation_model == 'Egli':
        # Egli path loss formula: Path loss (dB) = 117 + 40 * log10(coverage_radius) + 20 * log10(channel_frequency) \
        # - 20 * log10(secondary_base_station_height x secondary_mobile_station_height)
        # Here, coverage_radius is in miles and secondary_base_station_height and secondary_mobile_station_height are in feet
        # channel_frequency is in MHz
        secondary_base_station_height_feet = secondary_base_station_height * 3.28084
        secondary_mobile_station_height_feet = secondary_mobile_station_height * 3.28084

        A = path_loss - 117
        B = 20 * log10 (channel_frequency)
        C = 20 * log10 (secondary_base_station_height_feet * secondary_mobile_station_height_feet)

        temp = A - B + C
        radius_miles = 10 ** (temp/40)                                  # in miles
        radius = radius_miles * 1.60934                                 # in kilometres


    elif propagation_model == 'Free_Space':
        # Free space path loss formula: Path loss (dB) = 32.5 + 20 * log10(coverage_radius) + 20 * log10(channel_frequency)
        # Here, coverage_radius is in kms and channel_frequency is in MHz
        radius = 10 ** ((path_loss - 32.5 - (20 * log10 (channel_frequency)))/20)


    elif propagation_model == 'Plane_Earth':
        # Plane earth path loss formula: Path loss (dB) = 40 * log10(coverage_radius) - 20 * log10(secondary_base_station_height)
        # - 20 * log10(secondary_mobile_station_height)
        # Here, coverage_radius, secondary_base_station_height and secondary_mobile_station_height are in metres
        radius = 10 ** ((path_loss + (20 * log10 (secondary_base_station_height)) + (20 * log10(secondary_mobile_station_height)))/40)
        radius = radius * 0.001

    return radius



infile = open('./csv/place_pop_lat_long_alt_bw.csv','r')

per_row = []
for line in infile:
    per_row.append(line.split(','))

per_column = zip(*per_row)                                      # Matrix containing the entire input file "infile"
population_data = per_column[1]                                 # List of population data values
extracted_data = []                                             # List of data to be extracted

for i in range(len(population_data)):
            extracted_data.append(per_row[i])

data = [[x.strip() for x in y] for y in extracted_data]
data.sort(key=lambda x: float(x[4]), reverse=True)              # data sorted by altitude in decreasing order

propagation_model = sys.argv[1]                                 # Propagation model - 'Hata', 'Egli', 'Free_Space', 'Plane_Earth'
transmit_power = int(sys.argv[2])                               # Transmit Power in dBm, default = 36 dB
receiver_sensitivity = int(sys.argv[3])                         # Receiver Sensitivity in dBm, default = -96 dBm
channel_frequency = int(sys.argv[4])                            # Channel Frequency in MHz, default = 470 MHz

secondary_mobile_station_height = 1.5                           # Height of mobile station antenna in meters

path_loss = transmit_power - receiver_sensitivity               # Path loss in dBm

coverage_radius = [0 for x in range(len(data))]

for i in range(len(data)):
    secondary_base_station_height = float(data[i][4]) + 30

    coverage_radius[i] = calculate_coverage_radius(propagation_model, channel_frequency, secondary_base_station_height, secondary_mobile_station_height)
    print "\nCoverage Radius [%d] = %f" % (i, coverage_radius[i])

    if (coverage_radius[i] < 1):
        coverage_radius[i] = 1

    coverage_radius[i] = round(coverage_radius[i], 2)

for i, j in zip(data, coverage_radius):
    i.append(j)

for row in data:
    for k in (2, 3, 4, 5):
        row[k] = float(row[k])

for row in data:
    for k in (1, ):
        row[k] = int(row[k])

fid = open('./csv/place_pop_lat_long_alt_bw_range.csv', 'w')
#fid.write('PLACE, POPULATION, LATITUDE, LONGITUDE, ALTITUDE, BANDWIDTH_REQUIRED_PER_USER(Mbps), COVERAGE_RADIUS(kms)\n')

for i in range(len(data)):
    fid.write('%s, %d, %f, %f, %.2f, %.2f, %.2f\n' % (data[i][0], data[i][1], data[i][2], data[i][3], data[i][4], data[i][5], data[i][6]))

fid.close();
