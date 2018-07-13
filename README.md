# BE Rate Content #
![Release](https://img.shields.io/github/release/billerickson/be-rate-content.svg) ![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg?style=flat-square&maxAge=2592000)

**Contributors:** billerickson  
**Requires at least:** 4.1  
**Tested up to:** 4.9  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

BE Rate Content is a lean plugin for allowing visitors to rate content with a thumbs up or down. It's developer-friendly and very extensible.

Total likes and dislikes are stored as post meta (`_be_rate_content_likes` and `_be_rate_content_dislikes`), and the post's total rating (likes - dislikes) is stored as `_be_content_rating`. You can use this to sort content by most liked / disliked.

This is a fork of [BE Like Content](https://github.com/billerickson/be-like-content), which is similar but only allows users to like content.

## Installation ##

[Download the plugin here.](https://github.com/billerickson/BE-Rate-Content/archive/master.zip)

## Customization ##

In your theme, add `if( function_exists( 'be_rate_content' ) { be_rate_content()->display(); }` to display the like button. It is unstyled, so you will need to style it yourself.
