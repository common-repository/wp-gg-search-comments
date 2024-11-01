<?php
/*
Plugin Name: WP GG Search Comments
Plugin URI: http://wordpress.org/plugins/wp-gg-search-comments/
Description: This Plugin extends your GG Search Engine. It allows to search Comments.
Author: Matthias Günter
Version: 1.0
Author URI: http://matthias-web.de
Licence: GPLv2
*/

add_action("gg_filter", "gg_search_comments");
function gg_search_comments($collection) {
    $collection->add(array(
        "name" =>  "comments",
        "priority" => 4,
        "category" => __("Comments"),
        "title_col" => "comment_content",
        "limit" => 3,
        "cap" => "moderate_comments",
        "search" => function($term, $opt) {
            $comments = new WP_Comment_Query( );
            return $comments->query(array(
                "search" => $term,
                "number" => $opt["limit"]
            ));
        },
        "title_format" => function($title, $term) {
            return gg_snippet($title, $term);
        },
        "link" => function($row) {
            return admin_url( 'comment.php?c=' . $row["comment_ID"] ) . '&action=editcomment';
        },
        "output" => function($row) {
            return array(
                "a" => $row["comment_author"],
                "p" => get_the_title($row["comment_post_ID"])
            );
        }
    ));
    
    return $collection;
}

add_action("gg_search_box_end", "gg_search_comments_box_end");
function gg_search_comments_box_end() {
    ?>
    <script type="text/javascript">
        "use strict";
        jQuery(document).ready(function($) {
            var gg = GG_HOOK;
            
            gg.register("output_comments", function(objs, args) {
                var row = args[0], regex, content;
                $('<div class="gg-group gg-group-' + row.name + '">' + row.category + '</div>').appendTo(objs.rows);
                $.each(row.rows, function(key, value) {
                    regex = new RegExp( '(' + objs.input.val().trim() + ')', 'gi' );
                    content = value.title.replace(regex, "<b class=\"gg-hl\">$1</b>");
                    content += '<div class="gg-comments-info"><i class="fa fa-user"></i> ' + value.output.a + ' · ' + value.output.p + '</div>';
                    $('<a href="' + value.link + '" class="gg-item gg-cpt gg-group-' + row.name + '" data-name="' + row.name + '">' + content + '</a>').appendTo(objs.rows);
                });
            });
        });
    </script>
    <style type="text/css">
        #gg-search .gg-group-comments .gg-comments-info {
            font-size: 9.5px;
            opacity: 0.8;
        }
    </style>
    <?php
}

/**
 * @link http://stackoverflow.com/questions/1292121/how-to-generate-the-snippet-like-generated-by-google-with-php-and-mysql
 */
function gg_snippet($text, $phrase, $radius = 100, $ending = "[...]") { 
    $phraseLen = strlen($phrase); 
    if ($radius < $phraseLen) { 
        $radius = $phraseLen; 
    } 
    
    $phrases = explode (' ',$phrase);
    
    foreach ($phrases as $phrase) {
        $pos = strpos(strtolower($text), strtolower($phrase)); 
        if ($pos > -1) break;
    }
    
    $startPos = 0; 
    if ($pos > $radius) {
        $startPos = $pos - $radius; 
    } 
    
    $textLen = strlen($text); 
    
    $endPos = $pos + $phraseLen + $radius; 
    if ($endPos >= $textLen) { 
        $endPos = $textLen; 
    } 
    
    $excerpt = substr($text, $startPos, $endPos - $startPos); 
    if ($startPos != 0) { 
        $excerpt = substr_replace($excerpt, $ending, 0, $phraseLen); 
    } 
    
    if ($endPos != $textLen) { 
        $excerpt = substr_replace($excerpt, $ending, -$phraseLen); 
    }
    
    return $excerpt;
}
?>