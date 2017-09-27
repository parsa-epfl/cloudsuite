---
layout: default
title: Overview
---

# News

<div class="posts">

<ul>
  {% for post in site.posts limit: 2%}
    <li>
          <h4 class="post-title" id="no-margin-here">
            <span class="recent-news-date">{{ post.date | date_to_string }} &raquo;</span>
            <a href="{{ site.url }}{{ post.url }}">{{ post.title }}</a>
          </h4>
          <span class="no-margin-here"> {{ post.excerpt }} </span>
          {% capture content_words %} {{ post.content | number_of_words }} {% endcapture %}
          {% capture excerpt_words %} {{ post.excerpt | number_of_words }} {% endcapture %}
          {% if excerpt_words != content_words %}
            <a href="{{ site.url }}{{ post.url }}" class="no-margin-here">Read more...</a>
          {% endif %}
    </li>
    <hr class="no-margin-here"/>
  {% endfor %}
</ul>

<span class="no-margin-here">
For more news refer to our <a href="{{ site.url }}{{ site.blog_path }}" >blog</a>.
</span>

</div>

<hr class="no-margin-here" />

# Overview

CloudSuite is a benchmark suite for cloud services. The third release consists of eight applications that have been selected based on their popularity in today's datacenters. The benchmarks are based on real-world software stacks and represent real-world setups.

Cloud computing is emerging as a dominant computing platform for providing scalable online services to a global client base. Today's popular online services (e.g., web search, social networking, and business analytics) are characterized by massive working sets, high degrees of parallelism, and real-time constraints. These characteristics set cloud services apart from desktop (SPEC), parallel (PARSEC), and traditional commercial server applications (TPC). In order to stimulate research into the field of cloud and data-centric computing, we have created CloudSuite, a benchmark suite based on real-world online services.

CloudSuite covers a broad range of application categories commonly found in today's datacenters. The first release included data analytics, data serving, media streaming, large-scale and computation-intensive tasks, web search, and web serving. The second release expanded CloudSuite with graph analytics and data caching.

CloudSuite 3.0 is a major enhancement over prior releases both in benchmarks and infrastructure. It includes benchmarks that represent massive data manipulation with tight latency constraints such as: in-memory data analytics using Apache Spark, a new real-time video streaming benchmark following today’s most popular video-sharing website setups, and a new web serving benchmark mirroring today’s multi-tier web server software stacks with a caching layer.