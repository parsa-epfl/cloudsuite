---
layout: page
title: Archive
sidebar: "false"
---

<ul>
  {% for post in site.posts %}
    <li>
        <span class="recent-news-date">{{ post.date | date_to_string }} »</span>
        <a href="{{ site.url }}{{ post.url }}" >{{ post.title }}</a>
    </li>
  {% endfor %}
</ul>