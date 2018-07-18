package workload.harness;

import static com.sun.faban.harness.RunContext.getParamRepository;

import com.sun.faban.harness.DefaultFabanBenchmark2;
import com.sun.faban.harness.PreRun;

/**
 * Harness hook for the Elgg web2.0 benchmark. I'm adding it, but probably it's not required.
 *
 * @author Tapti Palit
 */
public class ElggBenchmark extends DefaultFabanBenchmark2 {
    
    int totalRunningTimeInSecs = 0;
    
    /**
     * This method is called to configure the specific benchmark run
     * Tasks done in this method include reading user parameters,
     * logging them and initializing various local variables.
     *
     * @throws Exception If configuration was not successful
     */
    @PreRun public void prerun() throws Exception {
        
        params = getParamRepository();

        //calculate total running time, including rampUp, steadyState,
        // and rampDown
        String rampUp = params.getParameter(
                               "fa:runConfig/fa:runControl/fa:rampUp");
        String steadyState = params.getParameter(
                               "fa:runConfig/fa:runControl/fa:steadyState");
        String rampDown = params.getParameter(
                               "fa:runConfig/fa:runControl/fa:rampDown");

        this.totalRunningTimeInSecs = Integer.parseInt(rampUp) +
                Integer.parseInt(steadyState) + Integer.parseInt(rampDown);

    }
}
