=== Plugin Name ===
Contributors: jadebox
Tags: ebay, auctions, epn, affiliate, shortcode
Requires at least: 3.2
Tested up to: 3.2
Stable tag: trunk

Adds a shortcode for displaying eBay listings in your posts with support for eBay Parter Network (ePN) affiliates.

== Description ==

RS Ad Manager allows you to add a shortcode to your post which is replaced by eBay product listings. If you're a member of the eBay Partner Network, the links to eBay will include your campaign ID so you will receive credit for the traffic you send to eBay.

For example, here's the shortcode for displaying products from a specific seller:

* [rsadmanager seller="shopjonrocket" keywords="rocket" count=3]

Complete documention is online at http://www.rogersmithsoftware.com/rsadmanager.html

== Installation ==

Upload the rs-ad-manager folder (not just the files in the folder) to your WordPress wp-content/plugins folder. If installed correctly, the wp-content/plugins folder should contain the rs-ad-manager folder. The rs-ad-manager folder will contain rsadmanager.php and other files.

Activate RS Ad Manager by accessing the Plugin Panel in your Administration Panels. Scroll down through the list of Plugins to find RS Ad Manager. Click the Activate link to turn the plugin on.

== Frequently Asked Questions ==

= How do I configure RS Ad Manager? =

Configure RS Ad Manager by clicking the RS Ad Manager link under Plugins (not the Edit link on the Plugins page).

The adminstration page is displayed. At the top of the page is a summary of how to use the rsadmanager shortcode. Below are fields for configuring RS Ad Manager.

If you are a member of the eBay Partner Network (ePN), you can enter your Campaign ID into RS Ad Manager. The ID will be used in links to eBay so that you will earn commissions on traffic you send to eBay.

RS Ad Manager allows you to enter a default keyword phrase to use if you don't specify the keywords in your rsadmanager short code. The phrase you enter into the adminstration page will be used if you don't include the keywords in your short code or if you specify a blank keywords value.

RS Ad Manager uses a template to allow you to control how the list of eBay products is displayed. If you don't specify the template name in the rsadmanager shortcode, RS Ad Manager uses the name you specify using the RS Ad Manager adminstration page.

RS Ad Manager sometimes uses one of our eBay Campaign IDs instead of the one you enter. By default, it substitutes our ID for yours a small percentage of the time. This allows you to support the continued development of RS Ad Manager.

You can, however, elect to share none (or a larger percentage) of your revenue. Select the percentage of the revenue you wish to share from the drop-down list on the RS Ad Manager administration page.

For more information, please see: http://www.rogersmithsoftware.com/rsadmanager.html#configuration

= How do I use RS Ad Manager? =

To display eBay product listings in a post, add the rsadmanager shortcode. Just type the following:

* [rsadmanager]

The above shortcode will be replaced with eBay product listings found using the default keywords and template you specified on the RS Ad Manager administration page.

You can add parameters to the shortcode to override the defaults. For example, to display items from the seller named shopjonrocket, use:

* [rsadmanager seller="shopjonrocket"]

You can display items from more than one seller. For example, to inslude products from the sellers named pvhc and shopjonrocket, use:

* [rsadmanager seller="shopjonrocket,pvhc"]

The *action* parameter tells RS Ad Manager what type of information to display. The default value (and, currently, the only acceptable value) is find. The find action searches eBay for matching products.

* [rsadmanager action="find"]

The *count* parameter tells RS Ad Manager the maximum number of products to display. The default is 10. The allowable range is 1 to 100.

* [rsadmanager count=15]

The *filter* parameter tells RS Ad Manager what type of listings to display. The listing types are Auction,AuctionWithBIN,FixedPriced, and StoreInventory. The default value is Auction,AuctionWithBIN,FixedPriced,StoreInventory.

* [rsadmanager filter="Auction,AuctionWithBIN"]

Auction listings are typical eBay auction listings. AuctionWithBIN listings are auction listings with the Buy It Now option. FixedPrice and StoreInventory are fixed-price listings. There's really no difference between FixedPrice and StoreInventory listings, so the same template can be used for both.

The *keywords* parameter specifies the keyword phrase to search for in the titles of the eBay products. eBay requires a keyword phrase to be used even when listing products from specific sellers. The default value you enter on the adminstration page is used for the keywords if you don't use this parameter or if its value is an empty string.

* [rsadmanager keywords="model rocket"]

The *seller* parameter specifies the seller (or sellers) of the products that will be listed. The seller parameter may be a list of seller names separated by commas. The default value you enter on the adminstration page is used for the seller if you don't use this parameter or if its value is an empty string.

* [rsadmanager seller="shopjonrocket"]

The *template* parameter specifies the name of the template to use. RS Ad Manager comes with two templates, named template and grid, which you can use. Or you can create your own templates.

* [rsadmanager template="grid"]

You can, of course, combine parameters:

* [rsadmanager seller="shopjonrocket" keywords="parachute" count=10 template="grid" listing="FixedPrice,StoreInventory"]

For more information, please see: http://www.rogersmithsoftware.com/rsadmanager.html#usage

= How do I uninstall RS Ad Manager? =

Deactivate RS Ad Manager by accessing the Plugin Panel in your Administration Panels. Scroll down through the list of Plugins to find RS Ad Manager. Click the Dectivate link to turn the plugin off. Then click the Delete link to delete the plugin.

For more information, please see: http://www.rogersmithsoftware.com/rsadmanager.html#uninstall

= How do I create or modify templates? =

Please see: http://www.rogersmithsoftware.com/rsadmanager.html#advanced

== Screenshots ==

1. RS Ad Manager Example

== Changelog ==

= 1.0.1 =
* Fixed bad characters in readme.txt
* Fixed problem with path to templates

= 1.0 =
* Initial Release

