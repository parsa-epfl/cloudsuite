/* Graph analytics */
import org.apache.spark._
import org.apache.spark.graphx._
// To make some of the examples work we will also need RDD
import org.apache.spark.rdd.RDD

object GraphAnalytics {
  def main(args: Array[String]) {
    val options = args.map {
      arg =>
        arg.dropWhile(_ == '-').split('=') match {
          case Array(opt, v) => (opt -> v)
          case _ => throw new IllegalArgumentException("Invalid argument: " + arg)
        }
    }
    var app = "pagerank"
    var niter = 10
    var numVertices = 100000
    
    val conf = new SparkConf().setAppName("Graph analytics")
    val sc = new SparkContext(conf)

options.foreach {
      case ("app", v) => app = v
      case ("niters", v) => niter = v.toInt
      case ("nverts", v) => numVertices = v.toInt
      case (opt, _) => throw new IllegalArgumentException("Invalid option: " + opt)
    }


	val graph = GraphLoader.edgeListFile(sc, "EDGES_FILE")
	graph.cache()

	var startTime = System.currentTimeMillis()
	if (app == "pagerank") {
	      println("Running PageRank")
	      val totalPR = graph.staticPageRank(5).vertices.map(_._2).sum()
	      println(s"Total PageRank = $totalPR")
	} else if (app == "cc") {
	      println("Running Connected Components")
	      val numComponents = graph.connectedComponents.vertices.map(_._2).distinct().count()
	      println(s"Number of components = $numComponents")
	}
	else if (app == "tc") {
	      println("Running Triangle Counting")
	      val triangleCnt = graph.triangleCount.vertices.map(_._2).distinct().count()
	      println(s"Number of triangles = $triangleCnt")
	}
	val runTime = System.currentTimeMillis() - startTime

	println(s"Running time = $runTime")

  }
}
