sed '$ s/.$//' temp.json > temp1.json
awk '{print "["$0"]"}' temp1.json > ./result/result.json
rm temp.json temp1.json
