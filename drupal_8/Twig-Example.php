<div class="megamap">
  <div class="megamenu-locations">
    <ul>
    {% for branch in params.branches %}<li><a href="{{ branch.url }}">{{ branch.title }}</a></li>{% endfor %}
    </ul>
  </div>
  <div class="megamenu-map"></div>
</div>
