---
layout: page
title: Benchmarks
sidebar: "true"
show_ord: 20
---

<ul>
	{% assign pages_list = site.pages %}
    	{% for node in pages_list %}
      	{% if node.title != null %}
        	{% if node.layout == "benchmark" %}
          		<li><a target="_blank" href="{{ site.url }}{{ site.baseurl }}{{ node.url }}">{{ node.title }}</a></li>
        	{% endif %}
      	{% endif %}
    {% endfor %}
</ul>
