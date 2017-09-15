import json
import glob
import csv
import re

place_list = []
weighted_utility_list = []
weighted_average_throughput_list = []

def alphabeticalSort(value):
    return value.rsplit('_', 1)[1]

for fin in sorted(glob.glob('./result/*.json'), key=alphabeticalSort):
    jsonString = open(fin).read()
    place_list.append([e['place'] for e in json.loads(jsonString)])
    weighted_utility_list.append([e['weighted_utility'] for e in json.loads(jsonString)])
    weighted_average_throughput_list.append([e['weighted_average_throughput'] for e in json.loads(jsonString)])

place_list = [[s.encode('ascii') for s in list] for list in place_list]
weighted_utility_list = [[s.encode('ascii') for s in list] for list in weighted_utility_list]
weighted_average_throughput_list = [[s.encode('ascii') for s in list] for list in weighted_average_throughput_list]

place_list = [val for sublist in place_list for val in sublist]
weighted_utility_list = [val for sublist in weighted_utility_list for val in sublist]
weighted_average_throughput_list = [val for sublist in weighted_average_throughput_list for val in sublist]

weighted_utility_list = [float(i) for i in weighted_utility_list]
weighted_average_throughput_list = [float(i) for i in weighted_average_throughput_list]

with open('./csv/sec_tx_weighted_utility.csv', 'w') as f1:
    writer = csv.writer(f1, delimiter=',')
    writer.writerows(zip(place_list, weighted_utility_list))

with open('./csv/sec_tx_weighted_average_throughput.csv', 'w') as f2:
    writer = csv.writer(f2, delimiter=',')
    writer.writerows(zip(place_list, weighted_average_throughput_list))

f = open('temp.json', 'w')

for filename in glob.glob("./result/*.json"):
    json_data = open(filename)
    data = json.load(json_data)
    str = json.dumps(data[0])
    f.write(str+",")

f.close()
