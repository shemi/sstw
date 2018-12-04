<?php
/**
 * Provide a search form
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/shemi
 * @since      1.0.0
 *
 * @package    Sstw
 * @subpackage Sstw/admin/partials
 */
?>

<script id="sstw-tep-template" type="text/html">
    <li class="sstw-sep-item">
        <span class="sstw-sep-label"><%= label %></span>
        <span class="sstw-sep-count"><%= count %></span>
    </li>
</script>

<script id="sstw-item-template" type="text/html">
    <a class="ab-item sstw-search-results-item-link sstw-search-results-item-<%= type %>" id="<%= id %>" href="<%= url %>">
        <span class="title"><%= title %></span>
    </a>
</script>