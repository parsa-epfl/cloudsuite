name := "Graph Analytics"

version := "2.0"

organization := "PARSA"

scalaVersion := "2.13.10"

artifactName := { (sv: ScalaVersion, module: ModuleID, artifact: Artifact) =>
  artifact.name + "-" + module.revision + "." + artifact.extension
}

libraryDependencies ++= Seq(
  "org.apache.spark" %% "spark-core" % "3.3.2",
  "org.apache.spark" %% "spark-graphx" % "3.3.2"
)

