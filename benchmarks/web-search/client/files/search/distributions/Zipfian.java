/* CloudSuite4.0 Benchmark Suite
 * Copyright (c) 2022, Parallel Systems Architecture Lab, EPFL
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions 
 * are met:
 *
 *   - Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   - Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in 
 *     the documentation and/or other materials provided with the distribution.

 *   - Neither the name of the Parallel Systems Architecture Laboratory, 
 *     EPFL nor the names of its contributors may be used to endorse or 
 *     promote products derived from this software without specific prior 
 *     written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS 
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE 
 * PARALLEL SYSTEMS ARCHITECTURE LABORATORY, EPFL BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL 
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR 
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN 
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * Author: Stavros Volos, Vukasin Stefanovic
 */

package sample.searchdriver;

import com.sun.faban.driver.*;
import javax.xml.xpath.XPathExpressionException;
import java.io.*;
import java.util.Vector;
import java.util.logging.Logger;
import java.util.Random;

@BenchmarkDefinition(
  name = "Sample Search Workload",
  version = "0.4",
  configPrecedence = true
)
@BenchmarkDriver(
  name = "SearchDriver",
  threadPerScale = 1
)
@FlatMix(
  operations = {
    "GET"
  },
  mix = {
    100
  },
  deviation = 0
)

@FixedTime(
  cycleType = CycleType.CYCLETIME,
  cycleTime = 2000,
  cycleDeviation = 2
)

// You can also use negative exponential 
// and uniform distributions, for example:
/*@NegativeExponential(
  cycleType = CycleType.CYCLETIME,
  cycleMean = 2000,
  cycleDeviation = 2
)*/

public class SearchDriver {
  private DriverContext ctx;
  private HttpTransport http;
  String url;
  Random random = new Random();
  Vector < Vector < String >> queries = new Vector < Vector < String >> ();
  String frontend, termsFile;
  private Logger logger;
  private boolean even;
  int termsCount = 0;

  public SearchDriver() throws XPathExpressionException, IOException {
    ctx = DriverContext.getContext();

    logger = Logger.getLogger(SearchDriver.class.getName());

    // Read the ip address and the port number of the frontend server
    frontend = "http://" +
      ctx.getXPathValue("/searchBenchmark/serverConfig/ipAddress1").trim() +
      ":" +
      ctx.getXPathValue("/searchBenchmark/serverConfig/portNumber1").trim();

    // Read the path and the filename of the terms which will be used to create the queries
    termsFile = ctx.getXPathValue("/searchBenchmark/filesConfig/termsFile").trim();
    FileInputStream fstream = new FileInputStream(termsFile);
    DataInputStream in = new DataInputStream(fstream);
    BufferedReader br = new BufferedReader(new InputStreamReader( in ));
    String strLine;
    while ((strLine = br.readLine()) != null) {
      String[] tokens = strLine.split(" ");
      int count = Integer.parseInt(tokens[0]);
      termsCount += count;
    } in .close();
    http = HttpTransport.newInstance();
  }
  private void Prefetch(int num) throws IOException {
    FileInputStream fstream = new FileInputStream(termsFile);
    DataInputStream in = new DataInputStream(fstream);
    BufferedReader br = new BufferedReader(new InputStreamReader( in ));
    Vector < Integer > linesToGet = new Vector < Integer > ();
    Vector < Integer > wordBelonging = new Vector < Integer > ();
    String strLine;
    if (queries != null)
      queries.removeAllElements();
    queries = new Vector < Vector < String >> ();
    for (int i = 0; i < num; i++) {
      int randomCard = random.nextInt(100);
      int termsSize = termsCount;
      Vector < String > query = new Vector < String > ();
      queries.add(query);
      // Create the query
      linesToGet.add(random.nextInt(termsSize));
      wordBelonging.add(i);
      if (randomCard > 23) {
        linesToGet.add(random.nextInt(termsSize));
        wordBelonging.add(i);
        if (randomCard > 47) {
          linesToGet.add(random.nextInt(termsSize));
          wordBelonging.add(i);
          if (randomCard > 69) {
            linesToGet.add(random.nextInt(termsSize));
            wordBelonging.add(i);
            if (randomCard > 83) {
              linesToGet.add(random.nextInt(termsSize));
              wordBelonging.add(i);
              if (randomCard > 88) {
                linesToGet.add(random.nextInt(termsSize));
                wordBelonging.add(i);
                if (randomCard > 92) {
                  wordBelonging.add(i);
                  linesToGet.add(random.nextInt(termsSize));
                  if (randomCard > 95) {
                    int j = random.nextInt(10);
                    for (int k = 0; k < j; k++) {
                      wordBelonging.add(i);
                      linesToGet.add(random.nextInt(termsSize));
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    int lineNum = 0;
    int passed = 0;
    while ((strLine = br.readLine()) != null) {
      String[] tokens = strLine.split(" ");
      int i = 0;
      int count = Integer.parseInt(tokens[0]);
      int currCount = passed + count;
      for (int lineToGet: linesToGet) {
        if (lineToGet <= currCount && lineToGet >= 0) {
          int j = wordBelonging.get(i);
          queries.get(j).add(tokens[1]);
          linesToGet.set(i, -1);
        }
        i++;
      }
      lineNum++;
      passed += count;
    } in .close();
    wordBelonging.removeAllElements();
    linesToGet.removeAllElements();
  }
  @BenchmarkOperation(
    name = "GET",
    max90th = 0.5,
    timing = Timing.AUTO
  )
  public void doGet() throws IOException {
    if (queries.isEmpty()) {
      Prefetch(100);
    }
    Vector < String > queryVector = queries.remove(0);
    String query = "";
    boolean first = true;
    for (String s: queryVector) {
      if (first) {
        first = false;
      } else {
        query = query + "+";
      }
      query = query + s;
    }
    url = frontend + "/solr/cloudsuite_web_search/query?q=" + query + "&lang=en&fl=url&df=text&rows=10&q.op=AND";
    try {
      StringBuilder sb = http.fetchURL(url);
      System.out.println(sb.toString());
    } catch (IOException e) {
      logger.severe("ERROR!\n");
    }
  }
}
