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

<form action="<?php echo esc_url( home_url( '/' ) ) ?>" method="get" id="adminbarsearch">
    <input class="adminbar-input mousetrap" name="s" id="adminbar-search" type="text" value="" maxlength="150" />
    <label for="adminbar-search" class="screen-reader-text"><?php _e( 'Search' ) ?></label>
    <input type="submit" class="adminbar-button" value="<?php _e('Search') ?>"/>
</form>

<div class="ab-sub-wrapper sstw-search-results-container noticon" id="sstw-search-results-container">
    <span class="sstw-search-results-message"></span>
    <ul id="sstw-search-results-list">

    </ul>
    <p class="sstw-search-results-loader"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 30" fill="#fff"><circle cx="15" cy="15" r="15"><animate attributeName="r" begin="0s" calcMode="linear" dur="0.8s" from="15" repeatCount="indefinite" to="15" values="15;9;15"/><animate attributeName="fill-opacity" begin="0s" calcMode="linear" dur="0.8s" from="1" repeatCount="indefinite" to="1" values="1;.5;1"/></circle><circle cx="60" cy="15" r="9" fill-opacity="0.3"><animate attributeName="r" begin="0s" calcMode="linear" dur="0.8s" from="9" repeatCount="indefinite" to="9" values="9;15;9"/><animate attributeName="fill-opacity" begin="0s" calcMode="linear" dur="0.8s" from="0.5" repeatCount="indefinite" to="0.5" values=".5;1;.5"/></circle><circle cx="105" cy="15" r="15"><animate attributeName="r" begin="0s" calcMode="linear" dur="0.8s" from="15" repeatCount="indefinite" to="15" values="15;9;15"/><animate attributeName="fill-opacity" begin="0s" calcMode="linear" dur="0.8s" from="1" repeatCount="indefinite" to="1" values="1;.5;1"/></circle></svg></p>
</div>


