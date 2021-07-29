## Movielens Dataset

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains an image with two datasets from the Movielens suite.
They are taken from http://grouplens.org/datasets/movielens/. The small dataset
(ml-latest-small) has 100,000 ratings applied to 9,000 movies by 700 users.
Size is around 1MB.  The large dataset (ml-latest) has 21,000,000 ratings
applied to 30,000 movies by 230,000 users. Size is 144MB.

This image is intended to be used with the
[cloudsuite3/in-memory-analytics][ima-dhrepo] image as the dataset to run the
benchmark on.

The datasets and the personal ratings file myratings.csv are located in /data,
the directory on the image that is exposed as a Docker volume. The user can
map it to a directory on the host and add different datasets or personal
ratings.

To obtain the image:

    $ docker pull cloudsuite3/movielens-dataset

[dhrepo]: https://hub.docker.com/r/cloudsuite3/movielens-dataset/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite3/movielens-dataset.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite3/movielens-dataset.svg "Go to DockerHub Page"
[ima-dhrepo]: https://hub.docker.com/r/cloudsuite3/in-memory-analytics/

