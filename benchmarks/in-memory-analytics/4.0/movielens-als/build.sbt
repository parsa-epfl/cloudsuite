name := "movielens-als"

version := "1.0"

organization := "PARSA"

scalaVersion := "2.11.12"

artifactName := { (sv: ScalaVersion, module: ModuleID, artifact: Artifact) =>
  artifact.name + "-" + module.revision + "." + artifact.extension
}

libraryDependencies += "org.apache.spark" % "spark-mllib_2.11" % "2.4.5" % "provided"

