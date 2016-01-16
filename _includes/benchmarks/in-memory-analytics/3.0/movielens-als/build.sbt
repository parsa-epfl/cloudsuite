name := "movielens-als"

version := "1.0"

organization := "PARSA"

scalaVersion := "2.10.4"

artifactName := { (sv: ScalaVersion, module: ModuleID, artifact: Artifact) =>
  artifact.name + "-" + module.revision + "." + artifact.extension
}

libraryDependencies += "org.apache.spark" % "spark-mllib_2.10" % "1.5.1" % "provided"

