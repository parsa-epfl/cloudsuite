import java.io.File
import scala.io.Source

/* Graph analytics */
import org.apache.spark._
import org.apache.spark.graphx._
// To make some of the examples work we will also need RDD
import org.apache.spark.rdd.RDD

object GraphAnalytics {

  def main(args: Array[String]) : Unit = {

    // val options: List[(String, String)] = List() 
    val options = args.map {
      arg =>
        arg.dropWhile(_ == '-').split('=') match {
          case Array(opt, v) => (opt -> v)
          case _ => throw new IllegalArgumentException("Invalid argument: " + arg)
        }
    }

    var app = "pr"
    var niter = 3
    var edgesFilename = "EDGES_FILES"

    options.foreach {
      case ("app", v) => app = v
      case ("niter", v) => niter = v.toInt
      case ("file", v) => edgesFilename = v
      case (opt, _) => throw new IllegalArgumentException("Invalid option: " + opt)
    }

    val conf = new SparkConf().setAppName("Graph analytics")
    val sc = new SparkContext(conf)

    val graph = GraphLoader.edgeListFile(sc, edgesFilename)
    graph.cache()

    var startTime = System.currentTimeMillis()

    if (app == "pr") {
      println("Running PageRank")
      val totalPR = graph.staticPageRank(niter).vertices.map(_._2).sum()
      println(s"Total PageRank = $totalPR")

    } else if (app == "cc") {
      println("Running Connected Components")
      val numComponents = graph.connectedComponents().vertices.map(_._2).distinct().count()
      println(s"Number of components = $numComponents")

    } else if (app == "tc") {
      println("Running Triangle Counting")
      val triangleCnt = graph.triangleCount().vertices.map(_._2).distinct().count()
      println(s"Number of triangles = $triangleCnt")
    }

    val runTime = System.currentTimeMillis() - startTime

    println(s"Running time = $runTime")

  }
}
