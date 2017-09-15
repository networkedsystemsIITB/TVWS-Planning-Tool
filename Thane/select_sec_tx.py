infile = open('./csv/select_sec_tx.csv', 'r')

per_row = []
for line in infile:
    per_row.append(line.split(','))

per_column = zip(*per_row)              # Matrix containing the entire input file "infile"

sec_tx = per_column[0]                  # List of secondary transmitter values
sec_tx_set = list(set(sec_tx))

sec_tx_population = per_column[1]       # List of secondary transmitter population values

sec_tx_lat = per_column[2]              # List of secondary transmitter latitude values

sec_tx_long = per_column[3]             # List of secondary transmitter longitude values

sec_tx_alt = per_column[4]              # List of secondary transmitter altitude values

coverage_radius_kms = per_column[5]     # List of secondary transmitter coverage radius values

sec_rx = per_column[6]                  # List of secondary receiver values
sec_rx_set = list(set(sec_rx))

sec_rx_population = per_column[7]       # List of secondary receiver population values

bw_reqt_sec_rx = per_column[8]          # List of secondary receiver bandwidth requirement values

distance = per_column[9]                # List of distance between secondary transmitter and secondary receiver values

uval_sec_tx = per_column[10]            # List of secondary transmitter weighted utility values

print "\nNumber of rows = ", len(per_row)

extracted_data = []                     # List of data to be extracted

for i in range(len(per_row)):
    extracted_data.append(per_row[i])

data = [[x.strip() for x in y] for y in extracted_data]

for i in range(len(data)):
    print data[i]

print "\n======================================================================\n"

fid = open('./csv/sec_tx_locations.csv', 'w')
#fid.write('SEC_TX, SEC_TX_POPULATION, SEC_TX_LAT, SEC_TX_LONG, SEC_TX_ALT, COVERAGE_RADIUS_KMS, SEC_RX, SEC_RX_POPULATION, BW_REQT_SEC_RX, DISTANCE, UVAL_SEC_TX\n')

for i in range(len(data)):
    for j in range(len(data[i])):
        if data[i][0] in sec_tx_set and data[i][6] in sec_rx_set and data[i][0] == data[i][6]:
            sec_rx_set.remove(data[i][6]);
            fid.write('%s, %d, %f, %f, %.2f, %.2f, %s, %d, %.2f, %.2f, %.2f\n' % (data[i][0], int(data[i][1]), float(data[i][2]), float(data[i][3]), float(data[i][4]), float(data[i][5]), data[i][6], int(data[i][7]), float(data[i][8]), float(data[i][9]), float(data[i][10])))
            print data[i]
        elif data[i][0] in sec_tx_set and data[i][6] in sec_rx_set and data[i][0] != data[i][6]:
            sec_rx_set.remove(data[i][6]);
            sec_tx_set.remove(data[i][6]);
            fid.write('%s, %d, %f, %f, %.2f, %.2f, %s, %d, %.2f, %.2f, %.2f\n' % (data[i][0], int(data[i][1]), float(data[i][2]), float(data[i][3]), float(data[i][4]), float(data[i][5]), data[i][6], int(data[i][7]), float(data[i][8]), float(data[i][9]), float(data[i][10])))
            print data[i]

fid.close();
