#!/bin/bash

/faban/master/bin/startup.sh
#cd /web20_benchmark/build && java -jar Usergen.jar http://$(docker inspect --format '{{ .NetworkSettings.Networks.my_net.IPAddress }}' php_server):8080

#sed -i 's/<fa:scale.*/<fa:scale>7<\/fa:scale>/' /web20_benchmark/deploy/run.xml
#sed -i 's/<fa:rampUp.*/<fa:rampUp>10<\/fa:rampUp>/' /web20_benchmark/deploy/run.xml
#sed -i 's/<fa:rampDown.*/<fa:rampDown>10<\/fa:rampDown>/' /web20_benchmark/deploy/run.xml
#sed -i 's/<fa:steadyState.*/<fa:steadyState>30<\/fa:steadyState>/' /web20_benchmark/deploy/run.xml
#sed -i 's/<host.*/<host>php_server<\/host>/' /web20_benchmark/deploy/run.xml
#sed -i 's/<port.*/<port>8080<\/port>/' /web20_benchmark/deploy/run.xml
#sed -i 's/<outputDir.*/<outputDir>\/faban\/output<\/outputDir>/' /web20_benchmark/deploy/run.xml
bash
#cd /web20_benchmark
#ant run

