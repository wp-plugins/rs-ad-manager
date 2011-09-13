<?php   
/*
Plugin Name: RS Ad Manager
Plugin URI: http://www.rogersmithsoftware.com/rsadmanager,html
Description: Displays eBay products inside posts
Version: 1.0
Author: Roger A. Smith
Author URI: http://www.rogersmithsoftware.com/
*/

/****************************************************************************************
 Utility Functions
 ***************************************************************************************/ 

function rs_expandTemplate(&$template, $vars, $ifstate = true)
 {
  $out = '';
  $state = 1;
  while ($template != '')
   {
    $char = substr($template, 0, 1);
    $template = substr($template, 1);
    switch ($state)
     {
      case 1:
       if ($char == '[')
         {
          $token = '';
          $state = 2;
         }
        else
         {
          if ($ifstate) $out .= $char;
         }
       break;
      case 2:
       if ($char == ']')
         {
          $state = 1;
          $token = trim($token);

          if ($token == 'end if') return $out;

          if (substr($token, 0, 3) == 'if ')
            {
             $parts = explode(' ', $token, 4);
             if (array_key_exists($parts[1], $vars))
               {
                switch (strtolower($parts[2]))
                 {
                  case 'equals':
                  case 'eq':
                  case '=':
                  case '==':
                   $test = strcasecmp($vars[$parts[1]], trim($parts[3]))==0;
                   $x .= rs_expandTemplate($template, $vars, $test);
                   if ($test) $out .= $x;
                   break;
                  case 'contains':
                   $test = strpos(strtolower($vars[$parts[1]]), strtolower($parts[3])) !== false;
                   $x .= rs_expandTemplate($template, $vars, $test);
                   if ($test) $out .= $x;
                   break;
                  default:
                   if ($ifstate) $out .= "[$token]";
                 }
               }
              else
               {
                if ($ifstate) $out .= "[$token]";
               }
            }
           else
            {
             if (array_key_exists($token, $vars))
               {
                if ($ifstate) $out .= $vars[$token];
               }
              else
               {
                $parts = explode(' ', $token, 2);
                if (array_key_exists($parts[0], $vars))
                  {
                   if ($ifstate)
                     {
                      $value = date_i18n($parts[1], strtotime($vars[$parts[0]]) + get_option('gmt_offset')*3600, false);
                      $out .= $value;
                     }
                  }
                 else
                  {
                   if ($ifstate) $out .= "[$token]";
                  }
               }
            }
         }
        else
         {
          $token .= $char;
         }
       break;
     }
   }
  return $out;
 }

/****************************************************************************************
 eBay Functions
 ***************************************************************************************/ 
function rsadmanager_find($keywords, $count, $filter, $seller, $template)
 {
  add_filter('wp_feed_cache_transient_lifetime', create_function( '$a', 'return 600;')); 

  if ($template == '') $template = 'template';
  $templatepath = WP_PLUGIN_DIR . "/rsadmanager/$template.body";
  $headerpath = WP_PLUGIN_DIR . "/rsadmanager/$template.header";
  $footerpath = WP_PLUGIN_DIR . "/rsadmanager/$template.footer";
  @$templatefile = file_get_contents($templatepath);
  @$headerfile = file_get_contents($headerpath);
  @$footerfile = file_get_contents($footerpath);
  if ($templatefile == '') $templatefile = "<p>Missing or empty template: $templatepath</p>";

  $eBayCampaignID = get_option('rs_ebaycampaignid');
  $revenueSharing = get_option('rs_revenuesharing');
  $rand = rand(1, 100);
  if ($eBayCampaignID == '' || $rand <= $revenueSharing) $eBayCampaignID = '5336893475';

  if ($keywords == '') $keywords = 'keyword';
  $keywords = rawurlencode($keywords);

  $q = 0;

  $itemFilters = '';
  $filters=explode(",", $filter);
  $x = 0;
  foreach($filters as $filter)
   {
    $filter = trim($filter);
    if (strcasecmp($filter, "FixedPrice") == 0)
      {
       $itemFilters .= "&itemFilter($q).value($x)=FixedPrice";
       ++$x;
      }
     else if (strcasecmp($filter, "Auction") == 0)
      {
       $itemFilters .= "&itemFilter($q).value($x)=Auction";
       ++$x;
      }
     else if (strcasecmp($filter, "StoreInventory") == 0)
      {
       $itemFilters .= "&itemFilter($q).value($x)=StoreInventory";
       ++$x;
      }
     else if (strcasecmp($filter, "AuctionWithBIN") == 0)
      {
       $itemFilters .= "&itemFilter($q).value($x)=AuctionWithBIN";
       ++$x;
      }
   }
  if ($itemFilters != '')
    {
     $itemFilters = "&itemFilter($q).name=ListingType" . $itemFilters;
     ++$q;
    }

  $itemFilters2 = '';
  $filters=explode(",", $seller);
  $x = 0;
  foreach($filters as $filter)
   {
    $filter = trim($filter);
    if ($filter != '')
      {
       $itemFilters2 .= "&itemFilter($q).value($x)=$filter";
       ++$x;
      }
   }
  if ($itemFilters2 != '')
    {
     $itemFilters .= "&itemFilter($q).name=Seller" . $itemFilters2;
     ++$q;
    }

  $count = $count * 1.0;
  if ($count < 1 || $count > 100) $count = 20;

  $count2 = floor($count * 1.5);
  if ($count2 > 100) $count2 = 0;

  $out = $headerfile;

  $appID = 'JonRocke-8c30-4b12-9a74-0d0863058a23';
  $url = "http://svcs.ebay.com/services/search/FindingService/v1?" .
         "OPERATION-NAME=findItemsByKeywords&SERVICE-VERSION=1.11.0&" .
         "SECURITY-APPNAME=$appID&RESPONSE-DATA-FORMAT=XML&REST-PAYLOAD&" .
         "keywords=$keywords$itemFilters&paginationInput.entriesPerPage=$count2&" .
         "affiliate.trackingId=$eBayCampaignID&affiliate.networkId=9&" .
         "affiliate.customId=RSADMANAGER";

  $body = wp_remote_retrieve_body(wp_remote_get($url));

  $itemids = array();

  $count2 = 0;

  if (body != '')
    {
     $parser = xml_parser_create ();
     xml_parser_set_option ( $parser, XML_OPTION_CASE_FOLDING, 0 );
     xml_parser_set_option ( $parser, XML_OPTION_SKIP_WHITE, 1 );
     xml_parse_into_struct ( $parser, $body, $values, $tags );
     xml_parser_free ( $parser );

     $insideItem = false;
     $insidePrimaryCategory = false;
     $insideShippingInfo = false;
     $insideSellingStatus = false;
     $insideListingInfo = false;
     $insideCondition = false;

     $vars = array();

     $vars['ack'] = '';
     $vars['errorcode'] = '';
     $vars['longmessage'] = '';
     $vars['severitycode'] = '';
     $vars['itemid'] = '';
     $vars['title'] = '';
     $vars['subtitle'] = '';
     $vars['categoryid'] = '';
     $vars['categoryname'] = '';
     $vars['galleryurl'] = '';
     $vars['viewitemurl'] = '';
     $vars['url'] = '';
     $vars['paymentmethod'] = '';
     $vars['autopay'] = '';
     $vars['postalcode'] = '';
     $vars['location'] = '';
     $vars['country'] = '';
     $vars['shippingservicecost'] = '';
     $vars['shippingtype'] = '';
     $vars['expeditedshipping'] = '';
     $vars['onedayshippingavailable'] = '';
     $vars['handlingtime'] = '';
     $vars['shiptolocations'] = '';
     $vars['currentprice'] = '';
     $vars['bidcount'] = '';
     $vars['sellingstate'] = '';
     $vars['timeleft'] = '';
     $vars['bestofferenabled'] = '';
     $vars['buyitnowavailable'] = '';
     $vars['starttime'] = '';
     $vars['endtime'] = '';
     $vars['ends'] = '';
     $vars['listingtype'] = '';
     $vars['gift'] = '';
     $vars['returnsaccepted'] = '';
     $vars['conditionid'] = '';
     $vars['conditiondisplayname'] = '';
     $vars['ismultivariationlisting'] = '';

     foreach ( $values as $key => $val )
       {
        switch ($val ['tag'])
          {
           case 'item' :
            $insideItem = $val ['type'] == 'open';
            if (!$insideItem)
              {
               if (($count2 < $count) && !in_array($vars['itemid'], $itemids))
                 {
                  ++$count2;
                  $itemids[] = $vars['itemid'];
                  $template = $templatefile;
                  $out .= rs_expandTemplate($template, $vars);
                 }
              }
            break;
           case 'Ack' :
            $vars['ack'] = $val ['value'];
            break;
           case 'ErrorCode' :
            $vars['errorcode'] = $val ['value'];
            break;
           case 'LongMessage' :
            $vars['longmessage'] = $val ['value'];
            break;
           case 'SeverityCode' :
            $vars['severitycode'] = $val ['value'];
            break;
           case 'itemId':
            $vars['itemid'] = $val ['value'];
            break;
           case 'title':
            $vars['title'] = $val ['value'];
            break;
           case 'subtitle':
            $vars['subtitle'] = $val ['value'];
            break;
           case 'primaryCategory':
            $insidePrimaryCategory = $val ['type'] == 'open';
            break;
           case 'categoryId':
            if ($insidePrimaryCategory)
              {
               $vars['categoryid'] = $val ['value'];
              }
            break;
           case 'categoryName':
            if ($insidePrimaryCategory)
              {
               $vars['categoryname'] = $val ['value'];
              }
            break;
           case 'galleryURL':
            $vars['galleryurl'] = $val ['value'];
            break;
           case 'viewItemURL':
            $vars['viewitemurl'] = $val ['value'];
            $vars['url'] = $val ['value'];
            break;
           case 'paymentMethod':
            $vars['paymentmethod'] = $val ['value'];
            break;
           case 'autoPay':
            $vars['autopay'] = $val ['value'];
            break;
           case 'postalCode':
            $vars['postalcode'] = $val ['value'];
            break;
           case 'location':
            $vars['location'] = $val ['value'];
            break;
           case 'country':
            $vars['country'] = $val ['value'];
            break;
           case 'shippingInfo':
            $insideShippingInfo = $val ['type'] == 'open';
            break;
           case 'shippingServiceCost':
            if ($insideShippingInfo)
              {
               $vars['shippingservicecost'] = $val ['value'];
              }
            break;
           case 'shippingType':
            if ($insideShippingInfo)
              {
               $vars['shippingtype'] = $val ['value'];
              }
            break;
           case 'expeditedShipping':
            if ($insideShippingInfo)
              {
               $vars['expeditedshipping'] = $val ['value'];
              }
            break;
           case 'oneDayShippingAvailable':
            if ($insideShippingInfo)
              {
               $vars['onedayshippingavailable'] = $val ['value'];
              }
            break;
           case 'handlingTime':
            if ($insideShippingInfo)
              {
               $vars['handlingtime'] = $val ['value'];
              }
            break;
           case 'shipToLocations':
            if ($insideShippingInfo)
              {
               $vars['shiptolocations'] = $val ['value'];
              }
            break;
           case 'sellingStatus':
            $insideSellingStatus = $val ['type'] == 'open';
            break;
           case 'currentPrice':
            if ($insideSellingStatus)
              {
               $vars['currentprice'] = number_format($val ['value'] * 1.0, 2);
              }
            break;
           case 'bidCount':
            if ($insideSellingStatus)
              {
               $vars['bidcount'] = $val ['value'];
              }
            break;
           case 'sellingState':
            if ($insideSellingStatus)
              {
               $vars['sellingstate'] = $val ['value'];
              }
            break;
           case 'timeLeft':
            if ($insideSellingStatus)
              {
               $vars['timeleft'] = $val ['value'];
              }
            break;
           case 'listingInfo':
            $insideListingInfo = $val ['type'] == 'open';
            break;
           case 'bestOfferEnabled':
            if ($insideListingInfo)
              {
               $vars['bestofferenabled'] = $val ['value'];
              }
            break;
           case 'buyItNowAvailable':
            if ($insideListingInfo)
              {
               $vars['buyitnowavailable'] = $val ['value'];
              }
            break;
           case 'startTime':
            if ($insideListingInfo)
              {
               $vars['starttime'] = $val ['value'];
              }
            break;
           case 'endTime':
            if ($insideListingInfo)
              {
               $vars['endtime'] = $val ['value'];
               $ends = time() - strtotime($val ['value']);
               if ($ends <= 0)
                 {
                  $ends = "Ended";
                 }
                else
                 {
                  $ends = str_replace("mins", "minutes", human_time_diff($ends, time()));
                 }
               $vars['ends'] = $ends;
              }
            break;
           case 'listingType':
            if ($insideListingInfo)
              {
               $vars['listingtype'] = $val ['value'];
              }
            break;
           case 'gift':
            if ($insideListingInfo)
              {
               $vars['gift'] = $val ['value'];
              }
            break;
           case 'returnsAccepted':
            $vars['returnsaccepted'] = $val ['value'];
            break;
           case 'condition':
            $insideCondition = $val ['type'] == 'open';
            break;
           case 'conditionId':
            if ($insideCondition)
              {
               $vars['conditionid'] = $val ['value'];
              }
            break;
           case 'conditionDisplayName':
            if ($insideCondition)
              {
               $vars['conditiondisplayname'] = $val ['value'];
              }
            break;
           case 'isMultiVariationListing':
            $vars['ismultivariationlisting'] = $val ['value'];
            break;
           default :
            break;
          }
       }
    }
   else
    {
    }
  return $out . $footerfile;
 }

/****************************************************************************************
 ShortCode Handler
 ***************************************************************************************/ 
add_shortcode('rsadmanager', 'rsadmanager_func');

// [rsadmanager action="find" keywords="keywords", count=10, filter="FixedPrice,Auction", seller="shopjonrocket" template="template"]
function rsadmanager_func($atts) 
  {
   $defaultKeywords = get_option('rs_defaultkeywords');
   $defaultTemplate = get_option('rs_defaulttemplate');

   extract(shortcode_atts(array('action' => 'find', 'filter' => '', 'keywords' => $defaultKeywords, 'count' => '5', 'seller' => '', 'template' => $defaultTemplate), $atts));

   $action = trim($action);
   if ($action == '') $action = 'Find';

   if (strcasecmp($action, 'Find') == 0)
     {
      return rsadmanager_find($keywords, $count, $filter, $seller, $template);
     }
    else
     {
      return "<p>Unrecognized action: $action</p>";
     }
  }

// [rsescape]...[/rsescape]
add_shortcode('rsescape', 'rsadmanager_escape'); 

function rsadmanager_escape($atts, $content=null)
  {
   return htmlentities($content);
  }

/****************************************************************************************
 Filter
 ***************************************************************************************/ 
add_filter('the_posts', 'rs_addcss'); // the_posts gets triggered before wp_head

function rs_addcss($posts)
  {
   if (empty($posts)) return $posts;
 
   foreach ($posts as $post) 
     {
      if (stripos($post->post_content, '[rsadmanager')) 
        {
         $styleUrl = plugins_url('rsadmanager.css', __FILE__);
         $scriptUrl = plugins_url('rsadmanager.js', __FILE__);
         wp_enqueue_style('rs_style', $styleUrl);
         wp_enqueue_script('rs_script', $scriptUrl);
         return $posts;
        }
     }
   return $posts;
  }
/****************************************************************************************
 Administration
 ***************************************************************************************/ 
add_action('admin_menu', 'rsadmanager_menu');

function rsadmanager_menu() 
  {
   add_plugins_page('RS Ad Manager', 'RS Ad Manager', 'manage_options', 'rsadmanager', 'rsadmanager_admin');
  }

function rsadmanager_admin()
  {
   if (!current_user_can('manage_options'))
     {
      wp_die(translate('You do not have sufficient permissions to access this page.'));
     }
     
   if (isset($_POST['submit']))
     {
      $template = trim($_POST['template']);           
      update_option('rs_template', $template);
      $eBayCampaignID = trim($_POST['ebaycampaignid']);           
      update_option('rs_ebaycampaignid', $eBayCampaignID);
      $defaultKeywords = trim($_POST['defaultkeywords']);           
      update_option('rs_defaultkeywords', $defaultKeywords);
      $revenueSharing = trim($_POST['revenuesharing']);           
      update_option('rs_revenuesharing', $revenueSharing);
      echo <<<SAVED
      <div id="message" class="updated fade">
        <p><strong>Options saved.</strong></p>
      </div>
SAVED;
     }

   $template = htmlentities(get_option('rs_template'));
   if ($template == '') $template = 'template';
   $eBayCampaignID = htmlentities(get_option('rs_ebaycampaignid'));
   $revenueSharing = get_option('rs_revenuesharing');
   $defaultKeywords = htmlentities(get_option('rs_defaultkeywords'));
   if ($revenueSharing == '') $revenueSharing = 10;
   
   $choose = '<select id="revenuesharing" name="revenuesharing" style="font-family: \'Courier New\', Courier, mono; font-size: 1.5em;vertical-align: middle;">';
   $choose .= '<option value="50"' . (($revenueSharing == 50) ? ' selected' : '') . '>50%</option>';
   $choose .= '<option value="40"' . (($revenueSharing == 40) ? ' selected' : '') . '>40%</option>';
   $choose .= '<option value="30"' . (($revenueSharing == 30) ? ' selected' : '') . '>30%</option>';
   $choose .= '<option value="20"' . (($revenueSharing == 20) ? ' selected' : '') . '>20%</option>';
   $choose .= '<option value="10"' . (($revenueSharing == 10) ? ' selected' : '') . '>10%</option>';
   $choose .= '<option value= "0"' . (($revenueSharing ==  0) ? ' selected' : '') . '> 0%</option>';
   $choose .= '</select>'; 

   echo <<<RSADMINFORM
   <div class="wrap">
     <h2>RS Ad Manager</h2>
     <div class="narrow">
     <p>RS Ad Manager makes it easy to display eBay product listings in your blog posts.  Just add the following shortcode in the body of your post:</p>
     <p align="center">[rsadmanager action="find" count=<i>count</i> filter="<i>listingtype</i>" keywords="<i>keywords</i> template="<i>template</i>"]</p>
     <p>Where:</p>
     <blockquote><b><i>count</i></b> is the maximum number of items to display (1 to 100)</blockquote>
     <blockquote><b><i>keywords</i></b> is the keyword phrase to match to the titles of the products. If keywords is omitted, or left blank, the default keywords
     value specified below is used. If it is also blank, no products will be listed.</blockquote>
     <blockquote><b><i>listingtype</i></b> is a comma-delimited list of the listing types to include - Auction, AuctionWithBIN, FixedPrice, StoreInventory. If
     omitted or left blank, the default is to include all listing types.</blockquote>
     <blockquote><b><i>template</i></b> is the base name of the template to use. If omitted or blank, the default template specified below is used.</blockquote>
     <p>Complete documentation for RS Ad Manager is at <a href="http://www.rogersmithsoftware.com/rsadmanager.html" target="_blank">http://www.rogersmithsoftware.com/rsadmanager.html</a>.</p>
     </div>
     <h2>RS Ad Manager Configuration</h2>
     <div class="narrow">
       <form action="" method="post" id="rsadmanager-admin" style="margin: auto;  width: 400px;">
         <p style="padding: .5em; background-color: #aa0; color: #fff; font-weight: bold;">eBay Campaign ID</p>
         <p><input id="ebaycampaignid" name="ebaycampaignid" type="text" size="20" maxlength="12" value="$eBayCampaignID" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /> (<a href="http://www.rogersmithsoftware.com/ebaycampaignid.html" target="_blank">What is this?</a>)</p>
         <p style="padding: .5em; background-color: #aa0; color: #fff; font-weight: bold;">Default Keywords</p>
         <p><input id="defaultkeywords" name="defaultkeywords" type="text" size="20" maxlength="60" value="$defaultKeywords" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /> (<a href="http://www.rogersmithsoftware.com/defaultkeywords.html" target="_blank">What is this?</a>)</p>
         <p style="padding: .5em; background-color: #aa0; color: #fff; font-weight: bold;">Default Template</p>
         <p><input id="template" name="template" type="text" size="20" maxlength="60" value="$template" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;" /> (<a href="http://www.rogersmithsoftware.com/template.html" target="_blank">What is this?</a>)</p>
         <p style="padding: .5em; background-color: #aa0; color: #fff; font-weight: bold;">Revenue Sharing</p>
         <p>$choose (<a href="http://www.rogersmithsoftware.com/revenuesharing.html" target="_blank">What is this?</a>)</p>
         <p class="submit"><input type="submit" name="submit" value="Update Options &raquo;" /></p>
       </form>
     </div>
   </div>
RSADMINFORM;
  }

?>