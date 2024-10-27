<?php
/**
 * Plugin Name: Ada Fwp Link Decoder For Google News
 * Plugin URI: https://adadaa.com
 * Description: The core functionality of the plugin is to decode and replace Google News links within syndicated content. Its primary purpose is to modify the content of syndicated posts imported through FeedWordPress Plugin.
 * Version: 2024.1602
 * Author: Adadaa
 * Author URI: https://adadaa.com/
 * License: GPL
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if FeedWordPress is active
function ada_fwp_check_feedwordpress_plugin() {
    if (!is_plugin_active('feedwordpress/feedwordpress.php')) {
        // FeedWordPress is not active, display a notice to the user
        add_action('admin_notices', 'ada_fwp_feedwordpress_not_active_notice');
    }
}
add_action('admin_init', 'ada_fwp_check_feedwordpress_plugin');

// Display a notice if FeedWordPress is not active
function ada_fwp_feedwordpress_not_active_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php echo 'FeedWordPress is not active. Please install and activate the FeedWordPress plugin for this plugin to work.'; ?></p>
    </div>
    <?php
}

// Prefixing the filter function with "ada_fwp_" for uniqueness
add_filter(
    /*hook=*/ 'syndicated_item_content',
    /*function=*/ 'ada_fwp_add_source_to_content',
    /*order=*/ 10,
    /*arguments=*/ 2
);

/**
 * ada_fwp_add_source_to_content: Gets the content of the syndication source and
 * includes it in the content of all syndicated posts
 * that are not by the defined author (presumably, you).
 *
 * @param string $content The current content of the syndicated item.
 * @param SyndicatedPost $post An object representing the syndicated post.
 *  The syndicated item data is contained in $post->item
 *  The syndication feed channel data is contained in $post->feed
 *  The subscription data is contained in $post->link
 * @return string The new content to give the syndicated item.
 */
function ada_fwp_add_source_to_content ($content, $post) {
    $googleNewsLinkPattern = '/https:\/\/news\.google\.com\/rss\/articles\/(.*?)/';

    if (!preg_match_all($googleNewsLinkPattern, $content, $matches)) {
        $content; 
    }
    else {
       


        $dom = new DOMDocument();
        //$dom->loadHTML($content);
        @$dom->loadHTML($content);

        // Find the <a> element
        $aElement = $dom->getElementsByTagName('a')->item(0);
        // Get the href attribute value
        $href = $aElement->getAttribute('href');

        // Output the href value
        $urlParts = parse_url($href);

        // Extract the path from the URL.
        $path = $urlParts['path'];

        // Use basename() to get the part after the last '/'.
        $partAfterLastSlash = basename($path);

        $decodedUrl = urldecode($partAfterLastSlash);

       // Now, you can decode it from base64.
        $decodedData = base64_decode($decodedUrl);
        $decodedData = preg_replace('/^.*?(https?:\/\/)/', '$1', $decodedData);
        $decodedData = preg_replace('/[^a-zA-Z0-9-_.~?&=:%\/]/', '', $decodedData);
        $pattern = '/^(.*?https:\/\/.*?)(https:\/\/.*)$/';
        $filtered_url = preg_replace($pattern, '$1', $decodedData);


       
        $syndicatedContent = str_replace($href,$filtered_url, $content);
      
        $content = $syndicatedContent;
        
       
    }

     // Send it back
    return $content;
}
