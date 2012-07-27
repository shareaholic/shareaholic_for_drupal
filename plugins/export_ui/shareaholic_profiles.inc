<?php

/**
 * @file
 * CTools Export UI plugin for Shareaholic profiles.
 */
$plugin = array(
    'schema' => 'shareaholic_profiles',
    'access' => 'administer shareaholic',
    'menu' => array(
        'menu prefix' => 'admin/config/content',
        'menu item' => 'shareaholic',
        'menu title' => 'Shareaholic',
        'menu description' => 'Administer Shareaholic profiles.',
    ),
    'title singular' => t('profile'),
    'title singular proper' => t('Profile'),
    'title plural' => t('profiles'),
    'title plural proper' => t('Profiles'),
    'form' => array(
        'settings' => 'shareaholic_profiles_export_ui_form',
        'submit' => 'shareaholic_profiles_export_ui_form_submit',
    ),
);

/**
 * Shareaholic profiles settings form.
 */
function shareaholic_profiles_export_ui_form(&$form, &$form_state) {
  drupal_add_library('system', 'farbtastic');
  drupal_add_js('jQuery(document).ready(jQuery("document").delegate("#replace_textfield_div","click", function(event){alert(jQuery(this).text());}));', array('type' => 'inline', 'scope' => 'footer', 'weight' => 5));
  $config = $form_state['item']->config;
  $icons_proj_path = drupal_get_path('module', 'shareaholic');
  $icons_path = file_create_url("{$icons_proj_path}/images/glyphicons-halflings.png");

  $form['config'] = array(
      '#type' => 'container',
      '#tree' => TRUE,
      '#prefix' => '<div id="replace_config_div">',
      '#suffix' => '</div>',
  );
  drupal_add_css('.form-item-config-changethis {
     display:inline;margin-left: 20px;font-size: 15px;
    }
  ', 'inline');

  drupal_add_css('.form-item-config-classic-options {
     margin-left: 20px;font-size: 15px;
    }
  ', 'inline');

  drupal_add_css('[class^="icon-"] {
  background-image: url("' . $icons_path . '");
  background-repeat: no-repeat;
  display: inline-block;
  height: 14px;
  vertical-align: text-top;
  width: 14px;}.icon-ok {
    background-position: -288px 0;
}.icon-remove {
    background-position: -312px 0;
}
  ', 'inline');

  $form['config']['changethis'] = array(
      '#title' => t("Choose the bookmarks style"),
      '#theme' => 'formfactors_select',
      '#type' => 'radios',
      '#default_value' => isset($config['changethis']) ? $config['changethis'] : 1,
      '#parents' => array('config', 'changethis'),
      '#options' => array(
          1 => 'SexyBookmarks',
          2 => 'Classic Bookmarks',
      ),
  );

  $form['config']['classic_options'] = array(
      '#title' => t("Choose the size"),
      '#theme' => 'classic_formfactor',
      '#type' => 'radios',
      '#default_value' => isset($config['classic_options']) ? $config['classic_options'] : 32,
      '#parents' => array('config', 'classic_options'),
      '#options' => array(
          16 => '16X16',
          32 => '32X32',
      ),
      '#prefix' => '<div id="classic_div" style="margin-top: 30px;">',
      '#suffix' => '</div>',
      '#states' => array(
          'visible' => array(// action to take.
              ':input[name="config[changethis]"]' => array('value' => 2),
          ),
      ),
  );

  $form['config']['service'] = array(
      '#type' => 'textarea',
      '#default_value' => isset($config['service']) ? $config['service'] : '5,7,88,2,3,257,74,52,207,191,236,54',
      '#theme' => 'shareaholic_services',
      '#prefix' => '<div id="replace_service_div">',
      '#suffix' => '</div>',
      '#states' => array(
          'visible' => array(// action to take.
              ':input[name="config[changethis]"]' => array('value' => 1),
          ),
      ),
  );

  $form['config']['perfOptionSetting'] = array(
      '#theme' => 'perfoption',
      '#type' => 'radios',
      '#default_value' => isset($config['perfOptionSetting']) ? $config['perfOptionSetting'] : 1,
      '#options' => array(
          1 => 'Allow',
          0 => 'Disallow',
      ),
  );

  // Functionality settings.
  $form['config']['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Functionality settings'),
      '#tree' => FALSE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#prefix' => '<div id="replace_settings_div">',
      '#suffix' => '</div>',
      '#states' => array(
          'visible' => array(// action to take.
              ':input[name="config[changethis]"]' => array('value' => 1),
          ),
      ),
  );
  $form['config']['settings']['fbLikeButton'] = array(
      '#type' => 'radios',
      '#title' => t('Include Facebook Like button'),
      '#options' => array(
          1 => t('Yes'),
          0 => t('No'),
      ),
      '#default_value' => isset($config['fbLikeButton']) ? $config['fbLikeButton'] : 0,
      '#parents' => array('config', 'fbLikeButton'),
  );
  $form['config']['settings']['fbButtonPos'] = array(
      '#type' => 'select',
      '#title' => t('Facebook Like button location'),
      '#options' => array(
          'bottom-left' => t('Bottom Left'),
          'bottom-right' => t('Bottom Right'),
          'top-left' => t('Top Left'),
          'top-right' => t('Top Right'),
      ),
      '#default_value' => isset($config['fbButtonPos']) ? $config['fbButtonPos'] : 'bottom-right',
      '#states' => array(
          'invisible' => array(
              'input[name="config[fbLikeButton]"]' => array('checked' => FALSE),
          ),
      ),
      '#parents' => array('config', 'fbButtonPos'),
  );
  $form['config']['settings']['dontShowShareCount'] = array(
      '#type' => 'radios',
      '#title' => t('Show Share counters'),
      '#options' => array(
          0 => t('Yes (recommended)'),
          1 => t('No'),
      ),
      '#default_value' => isset($config['dontShowShareCount']) ? $config['dontShowShareCount'] : 0,
      '#description' => t('For Facebook, Twitter and Delicious'),
      '#parents' => array('config', 'dontShowShareCount'),
  );
  $form['config']['settings']['designer_toolTips'] = array(
      '#type' => 'radios',
      '#title' => t('Use Designer Tooltips'),
      '#options' => array(
          1 => t('Yes (recommended)'),
          0 => t('No'),
      ),
      '#default_value' => isset($config['designer_toolTips']) ? $config['designer_toolTips'] : 1,
      '#parents' => array('config', 'designer_toolTips'),
  );
  $form['config']['settings']['tip_bg_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Background Color for Tooltips'),
      '#size' => 7,
      '#maxlength' => 7,
      '#default_value' => isset($config['tip_bg_color']) ? $config['tip_bg_color'] : '#000000',
      '#states' => array(
          'invisible' => array(
              'input[name="config[designer_toolTips]"]' => array('checked' => FALSE),
          ),
      ),
      '#parents' => array('config', 'tip_bg_color'),
  );
  $form['config']['settings']['tip_text_color'] = array(
      '#type' => 'textfield',
      '#title' => t('Text Color for Tooltips'),
      '#size' => 7,
      '#maxlength' => 7,
      '#default_value' => isset($config['tip_text_color']) ? $config['tip_text_color'] : '#FFFFFF',
      '#states' => array(
          'invisible' => array(
              'input[name="config[designer_toolTips]"]' => array('checked' => FALSE),
          ),
      ),
      '#parents' => array('config', 'tip_text_color'),
  );
  $form['config']['settings']['rel'] = array(
      '#type' => 'radios',
      '#title' => t('Add Nofollow to Links'),
      '#options' => array(
          'nofollow' => t('Yes'),
          '' => t('No'),
      ),
      '#default_value' => isset($config['rel']) ? $config['rel'] : 'nofollow',
      '#parents' => array('config', 'rel'),
  );
  $form['config']['settings']['target'] = array(
      '#type' => 'radios',
      '#title' => t('Open Links in New Window'),
      '#options' => array(
          '_blank' => t('Yes'),
          '_self' => t('No'),
      ),
      '#default_value' => isset($config['target']) ? $config['target'] : '_blank',
      '#parents' => array('config', 'target'),
  );
  $form['config']['settings']['shrlink'] = array(
      '#type' => 'radios',
      '#title' => t('Show Shareaholic Link'),
      '#options' => array(
          1 => t('Yes'),
          0 => t('No'),
      ),
      '#default_value' => isset($config['shrlink']) ? $config['shrlink'] : 1,
      '#parents' => array('config', 'shrlink'),
  );
  $form['config']['settings']['shortener'] = array(
      '#type' => 'select',
      '#title' => t('Which URL Shortener'),
      '#empty_value' => 'none',
      '#options' => array(
          'bitly' => t('bit.ly'),
          'google' => t('goo.gl'),
          'jmp' => t('jmp.li'),
          'supr' => t('su.pr'),
          'tinyurl' => t('tinyurl.com'),
      ),
      '#default_value' => isset($config['shortener']) ? $config['shortener'] : 'google',
      '#parents' => array('config', 'shortener'),
  );

  // Twitter specific settings.
  $form['config']['twitter'] = array(
      '#type' => 'fieldset',
      '#title' => t('Twitter settings'),
      '#tree' => FALSE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#prefix' => '<div id="replace_twitter_div">',
      '#suffix' => '</div>',
      '#states' => array(
          'visible' => array(// action to take.
              ':input[name="config[changethis]"]' => array('value' => 1),
          ),
      ),
  );
  $form['config']['twitter']['twitter_template'] = array(
      '#type' => 'textfield',
      '#title' => t('Configure Custom Tweet Template'),
      '#default_value' => isset($config['twitter_template']) ? $config['twitter_template'] : '${title} - ${short_link} via @Shareaholic',
      '#description' =>
      '<strong>' . t('Configuration Instructions') . ':</strong><br />'
      . '<em>' . t('Using the strings ${title} and ${short_link} you can fully customize your tweet output.') . '</em>'
      . '<p>'
      . '  <strong>' . t('Example Configurations') . ':</strong><br />'
      . '  <em>' . t('${title} - ${short_link} (via @Shareaholic)') . '</em><br />'
      . '  ' . t('or') . '<br />'
      . '  <em>' . t('RT @Shareaholic: ${title} - ${short_link}') . '</em>'
      . '</p>',
      '#parents' => array('config', 'twitter_template'),
  );

  // Aesthetic settings.
  $form['config']['aesthetics'] = array(
      '#type' => 'fieldset',
      '#title' => t('Aesthetic settings'),
      '#tree' => FALSE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#prefix' => '<div id="replace_aesthetics_div">',
      '#suffix' => '</div>',
      '#states' => array(
          'visible' => array(// action to take.
              ':input[name="config[changethis]"]' => array('value' => 1),
          ),
      ),
  );
  $form['config']['aesthetics']['expand'] = array(
      '#type' => 'radios',
      '#title' => t('Animate-expand multi-lined bookmarks'),
      '#options' => array(
          1 => t('Yes'),
          0 => t('No'),
      ),
      '#default_value' => isset($config['expand']) ? $config['expand'] : 1,
      '#parents' => array('config', 'expand'),
  );
  $form['config']['aesthetics']['positioning'] = array(
      '#type' => 'radios',
      '#title' => t('Auto-space/center the bookmarks'),
      '#options' => array(
          'spaced' => t('Space'),
          'center' => t('Center'),
          'no' => t('No'),
      ),
      '#default_value' => isset($config['spaced']) && $config['spaced'] ? 'spaced' : (
              isset($config['center']) && $config['center'] ? 'center' : 'no'
              ),
      '#parents' => array('config', 'positioning'),
  );
}

function ajax_example_simplest2_callback($form, $form_state) {

  return $form['config']['replace_textfield'];
}

/**
 * Shareaholic profiles settings form; submit callback.
 */
function shareaholic_profiles_export_ui_form_submit(&$form, &$form_state) {
  $form_state['values']['config']['center'] = $form_state['values']['config']['positioning'] == 'center';
  $form_state['values']['config']['spaced'] = $form_state['values']['config']['positioning'] == 'spaced';
  unset($form_state['values']['config']['positioning']);
  if ($form_state['values']['config']['changethis'] == 2) {
    unset($form_state['values']['config']['fbLikeButton']);
    unset($form_state['values']['config']['fbButtonPos']);
    unset($form_state['values']['config']['dontShowShareCount']);
    unset($form_state['values']['config']['expand']);
    unset($form_state['values']['config']['spaced']);
    unset($form_state['values']['config']['center']);
    unset($form_state['values']['config']['shrlink']);
    $form_state['values']['config']['target'] = '_blank';
    $form_state['values']['config']['rel'] = 'nofollow';
  }
}

function theme_perfoption($variables) {

  $form_element = $variables['form_element'];
  $default = $form_element['#default_value'];
  $checked1 = 'checked="checked"';
  $checked2 = 'checked="checked"';
  if ($default == 0)
    $checked1 = '';
  else
    $checked2 = '';
  return '<div id="genopts" class="form-item form-type-radios form-item-config-perfOptionSetting">
                            <table><tbody>

                                    <tr>
                                        <td><span style="padding-bottom: 10px;color: #444444;display: block;font-weight: bold;margin-top: 10px;">Enable 3rd Party Services to use the following features:</span></td>
                                        <td><label><input type="radio" value="1" id="edit-config-perfoptionsetting-1" name="config[perfOptionSetting]" ' . $checked1 . ' > Yes (recommended)</label></td>
                                        <td><label><input type="radio" value="0" id="edit-config-perfoptionsetting-0" name="config[perfOptionSetting]" ' . $checked2 . ' > No</label></td>
                                    </tr>
                                    <tr>
                                        <td><span style="display:block; font-size: 11px; color: #666666;margin-left: 30px;">Facebook, Twitter, LinkedIn and Delicious Share Counters</span></td>
                                        <td><div class="icon-ok"></div></td>
                                        <td><div class="icon-remove"></div></td>
                                    </tr>
                                    
                                    <tr>
                                        <td><span style="display:block; font-size: 11px; color: #666666;margin-left: 30px;" >Shareaholic Social Analytics</span></td>
                                       <td><div class="icon-ok"></div></td>
                                        <td><div class="icon-remove"></div></td>
                                    </tr>
                                    
                                    <tr>
                                        <td><span style="display:block; font-size: 11px; color: #666666;margin-left: 30px;">Google Analytics Social Tracking</span></td>
                                        <td><div class="icon-ok"></div></td>
                                        <td><div class="icon-remove"></div></td>
                                    </tr>

                            </tbody></table>
                        </div>';
}

function theme_formfactors_select($variables) {

  $form_element = $variables['form_element'];
  $default = $form_element['#default_value'];
  $checked1 = 'checked="checked"';
  $checked2 = 'checked="checked"';
  if ($default == 2)
    $checked1 = '';
  else
    $checked2 = '';

  return '<div class="form-item form-type-radios form-item-config-changethis" style="margin-top: 100px;">
 <div class="form-radios" id="edit-config-changethis"><div class="form-item form-type-radio form-item-config-changethis">
 <input type="radio" class="form-radio"' . $checked1 . ' value="1" name="config[changethis]" id="edit-config-changethis-1">  <label for="edit-config-changethis-1" class="option">SexyBookmarks </label>

</div>
<div class="form-item form-type-radio form-item-config-changethis" style="margin-left: 50px;">
 <input type="radio" class="form-radio"' . $checked2 . ' value="2" name="config[changethis]" id="edit-config-changethis-2">  <label for="edit-config-changethis-2" class="option">Classic Bookmarks </label>

</div>
</div>
</div>';
}

function theme_classic_formfactor($variables) {
  $form_element = $variables['form_element'];
  $default = $form_element['#default_value'];
  $checked1 = 'checked="checked"';
  $checked2 = 'checked="checked"';
  if ($default == 32)
    $checked1 = '';
  else
    $checked2 = '';
  return '<div id="classic_div"><div class="form-item form-type-radios form-item-config-classic-options" style="display: block;">
 <div class="form-radios" id="edit-config-classic-options"><div class="form-item form-type-radio form-item-config-classic-options">
 <input type="radio" class="form-radio"' . $checked1 . '  value="16" name="config[classic_options]" id="edit-config-classic-options-1">  <img src="http://www.shareaholic.com/media/images/classicbookmark_16x16.png">

</div>
<div class="form-item form-type-radio form-item-config-classic-options">
 <input type="radio" class="form-radio"' . $checked2 . '  value="32" name="config[classic_options]" id="edit-config-classic-options-2">  <img src="http://www.shareaholic.com/media/images/classicbookmark_32x32.png">

</div>
</div>
</div>
</div>';
}
function shrsb_truncate_text($text, $nbrChar, $append='..') {
     if(strlen($text) > $nbrChar) {
          $text = substr($text, 0, $nbrChar);
          $text .= $append;
     }
     return $text;
}

function theme_shareaholic_services($variables) {
  $services_list =array(
    "1" => "Buzzster!",
    "2"=>"Delicious",
    "3"=>"Digg",
    "4"=>"Mixx",
    "5"=>"Facebook",
    "6"=>"Mister-Wong",
    "7"=>"Twitter",
    "8"=>"Netlog",
    "9"=>"Pownce",
    "10"=>"Technorati Favorites",
    "11"=>"Furl",
    "12"=>"Shoutwire",
    "13"=>"CiteULike",
    "14"=>"Jumptags",
    "15"=>"Windows Live Spaces",
    "16"=>"Hemidemi",
    "17"=>"FunP",
    "18"=>"Instapaper",
    "19"=>"PhoneFavs",
    "20"=>"Xerpi",
    "21"=>"Netvouz",
    "22"=>"Wink",
    "23"=>"Ma.gnolia",
    "24"=>"Diigo",
    "25"=>"BibSonomy",
    "26"=>"Taggly",
    "27"=>"BlogMarks",
    "28"=>"Tailrank",
    "29"=>"StartAid",
    "30"=>"Kledy",
    "31"=>"Khabbr",
    "32"=>"Yahoo My Web",
    "33"=>"Meneame",
    "34"=>"Yoolink",
    "35"=>"Bookmarks.fr",
    "36"=>"Technotizie",
    "37"=>"Windows Live Favorites",
    "38"=>"StumbleUpon",
    "39"=>"MySpace",
    "40"=>"Reddit",
    "41"=>"NewsVine",
    "42"=>"Multiply",
    "43"=>"FriendFeed",
    "44"=>"Plaxo Pulse",
    "45"=>"Ping",
    "46"=>"Squidoo",
    "47"=>"Protopage Bookmarks",
    "48"=>"Blinklist",
    "49"=>"Faves",
    "50"=>"AIM",
    "51"=>"E-mail program",
    "52"=>"Gmail",
    "53"=>"Hotmail",
    "54"=>"Yahoo Mail",
    "55"=>"AOL Mail",
    "56"=>"YiGG",
    "57"=>"Webnews",
    "58"=>"Segnalo",
    "59"=>"Pusha",
    "60"=>"YouMob",
    "61"=>"Slashdot",
    "62"=>"Fark",
    "63"=>"Allvoices",
    "64"=>"Jamespot",
    "65"=>"Imera Brazil",
    "66"=>"Twiddla",
    "67"=>"LinkaGoGo",
    "68"=>"MindBodyGreen",
    "69"=>"Feedmarker Bookmarks",
    "70"=>"unalog",
    "71"=>"Hugg",
    "72"=>"Diglog",
    "73"=>"Yahoo Buzz",
    "74"=>"Google Bookmarks",
    "75"=>"NowPublic",
    "76"=>"Yahoo Bookmarks",
    "78"=>"Tumblr",
    "79"=>"LiveJournal",
    "80"=>"Current",
    "81"=>"HelloTxt",
    "82"=>"Spurl",
    "83"=>"Yample",
    "84"=>"Oneview",
    "85"=>"Linkatopia",
    "86"=>"Simpy",
    "87"=>"Yahoo Messenger",
    "88"=>"LinkedIn",
    "89"=>"Global Grind",
    "90"=>"BuddyMarks",
    "91"=>"Ask.com MyStuff",
    "92"=>"Viadeo",
    "93"=>"Maple",
    "94"=>"Wists",
    "95"=>"Gravee",
    "96"=>"Connotea",
    "97"=>"Backflip",
    "98"=>"MyLinkVault",
    "99"=>"SiteJot",
    "100"=>"Sphinn",
    "101"=>"Health Ranker",
    "102"=>"DZone",
    "103"=>"Symbaloo Feeds",
    "104"=>"Care2 News",
    "105"=>"Hyves",
    "106"=>"Design Float",
    "107"=>"Sphere",
    "108"=>"Bitty Browser",
    "109"=>"My Yahoo",
    "110"=>"Google",
    "111"=>"Excite MIX",
    "112"=>"iTunes",
    "113"=>"Zune",
    "114"=>"FeedM8",
    "115"=>"PodNova",
    "116"=>"WINKsite",
    "117"=>"NewsGator",
    "118"=>"Hubdog",
    "119"=>"BUZmob",
    "120"=>"NewsIsFree",
    "121"=>"KlipFolio",
    "122"=>"NETime Channel",
    "123"=>"Feed Mailer",
    "124"=>"Symbaloo Bookmarks",
    "125"=>"Rocket RSS Reader",
    "126"=>"Blogger",
    "127"=>"Flurry",
    "128"=>"FireAnt",
    "129"=>"Sofomo",
    "130"=>"Netomat HUB",
    "131"=>"FeedMarker",
    "132"=>"FeedBucket",
    "133"=>"SendMeRSS",
    "134"=>"Bloglines",
    "135"=>"Windows Live",
    "136"=>"Protopage News Feeds",
    "137"=>"My AOL",
    "138"=>"The Free Library",
    "139"=>"The Free Dictionary",
    "140"=>"Wikio",
    "141"=>"BlogRovR",
    "142"=>"Webwag",
    "143"=>"Daily Rotation",
    "144"=>"Outlook",
    "145"=>"Google Toolbar",
    "146"=>"Bitty Browser Preview",
    "147"=>"RSS 2 PDF",
    "148"=>"LiteFeeds",
    "149"=>"Gritwire",
    "150"=>"FeedLounge",
    "151"=>"FeedReader",
    "152"=>"FeedOnSite",
    "153"=>"i metaRSS",
    "154"=>"RssFwd",
    "155"=>"SimplyHeadlines",
    "156"=>"Zhua Xia",
    "157"=>"Xian Guo",
    "158"=>"mobilerss",
    "159"=>"Netvibes",
    "160"=>"Pageflakes",
    "161"=>"My MSN",
    "162"=>"Odeo",
    "163"=>"AideRSS",
    "164"=>"Fwicki",
    "165"=>"RapidFeeds",
    "166"=>"Miro",
    "167"=>"Shyftr",
    "168"=>"ZapTXT",
    "169"=>"Newgie",
    "170"=>"NewsAlloy",
    "171"=>"Plusmo",
    "172"=>"Yourminis",
    "173"=>"Eskobo",
    "174"=>"Alesti",
    "175"=>"Rasasa",
    "176"=>"AvantGo",
    "177"=>"FeedShow",
    "178"=>"Xanga",
    "179"=>"MySpace Profile",
    "180"=>"Friendster",
    "181"=>"Hi5",
    "182"=>"FeedBlitz",
    "183"=>"Gabbr",
    "184"=>"MSDN",
    "185"=>"Microsoft TechNet",
    "186"=>"Microsoft Expression",
    "187"=>"Tagza",
    "188"=>"Tipd",
    "189"=>"Agregator",
    "190"=>"Strands",
    "191"=>"Evernote",
    "192"=>"Stumpedia",
    "193"=>"Foxiewire",
    "194"=>"Arto",
    "195"=>"Netvibes",
    "196"=>"Bebo",
    "197"=>"Folkd",
    "198"=>"VodPod",
    "199"=>"NewsTrust",
    "200"=>"Amazon (US) Wish List",
    "201"=>"E-mail program",
    "202"=>"YC Hacker News",
    "203"=>"Truemors",
    "204"=>"Techmeme Tip",
    "205"=>"Identi.ca",
    "206"=>"SmakNews",
    "207"=>"Google Reader",
    "208"=>"Bit.ly",
    "209"=>"Kaboodle",
    "210"=>"Posterous",
    "211"=>"TipJoy",
    "212"=>"I Heart It",
    "213"=>"Google Notebook",
    "214"=>"Tr.im",
    "215"=>"Streakr",
    "216"=>"Twine",
    "217"=>"Soup",
    "218"=>"Plurk",
    "219"=>"Blogger",
    "220"=>"TypePad",
    "221"=>"AttentionMeter",
    "222"=>"Smush.it",
    "223"=>"TinyURL",
    "224"=>"Digg",
    "225"=>"BzzScapes",
    "226"=>"Tweetie",
    "227"=>"Diigo",
    "228"=>"Is.gd",
    "229"=>"vi.sualize.us",
    "230"=>"WordPress Blog",
    "231"=>"Mozillaca",
    "232"=>"Su.pr",
    "233"=>"TBUZZ",
    "234"=>"Hub.tm",
    "235"=>"Followup.cc",
    "236"=>"PrintFriendly",
    "237"=>"DailyMe",
    "238"=>"NUjij",
    "239"=>"Read It Later",
    "240"=>"Box.net",
    "241"=>"Balatarin",
    "242"=>"Favoriten",
    "243"=>"Oknotizie",
    "244"=>"diHITT",
    "245"=>"Svejo",
    "246"=>"Hatena",
    "247"=>"Orkut",
    "248"=>"Clicky",
    "249"=>"j.mp",
    "250"=>"ReadWriteWeb",
    "251"=>"Dwellicious",
    "252"=>"Google Translate",
    "253"=>"Yammer",
    "254"=>"Yahoo Buzz India",
    "255"=>"Boxee",
    "256"=>"Pinboard",
    "257"=>"Google Buzz",
    "258"=>"Readability",
    "259"=>"Social.com",
    "260"=>"Google Apps Email",
    "261"=>"HootSuite",
    "262"=>"Google Wave",
    "263"=>"iZeby",
    "264"=>"Ning",
    "265"=>"SpringPad",
    "266"=>"BobrDobr",
    "267"=>"Yandex",
    "268"=>"Moemesto",
    "269"=>"Memori.ru",
    "270"=>"Amazon (UK) Wish List",
    "271"=>"Amazon (CA) Wish List",
    "272"=>"Amazon (DE) Wish List",
    "273"=>"Amazon (FR) Wish List",
    "274"=>"Amazon (JP) Wish List",
    "275"=>"Google Sidewiki",
    "276"=>"Marginize",
    "277"=>"Twittley",
    "278"=>"Script & Style",
    "279"=>"PFBuzz",
    "280"=>"Fwisp",
    "281"=>"100 bookmarks",
    "282"=>"Design Bump",
    "283"=>"eKudos",
    "284"=>"Web Blend",
    "285"=>"Wykop",
    "286"=>"BlogEngage",
    "287"=>"FAQpal",
    "288"=>"Bitacoras",
    "289"=>"N4G",
    "290"=>"Kaevur",
    "291"=>"Virb",
    "292"=>"BonzoBox",
    "293"=>"Zabox",
    "294"=>"ToMuse",
    "295"=>"BuzzFeed",
    "296"=>"Amazon Universal Registry",
    "297"=>"TwitThat",
    "298"=>"Mister-Wong DE",
    "299"=>"Mister-Wong CN",
    "300"=>"Mister-Wong ES",
    "301"=>"Mister-Wong FR",
    "302"=>"Mister-Wong RU",
    "303"=>"Facebook Like",
    "304"=>"Google+",
    "305"=>"Facebook Send",
    "306"=>"Shareaholic More",
    "307"=>"Shareaholic All",
    "308"=>"Buffer",
    "309"=>"Pinterest",
    "310"=>"Quora",
    "313"=>"Shareaholic Mail"
);
  $output = '';
  $form_element = $variables['form_element'];
  // List of Shareaholic services.
  $services = array(
      2, 3, 5, 6, 7, 10, 24, 38, 39, 40, 52, 54, 74, 88, 201, 219,
      27, 33, 41, 43, 45, 46, 53, 78, 195, 196, 205, 210, 236, 247, 264,
      44, 48, 61, 80, 100, 105, 140, 191, 202, 218, 238, 240, 266, 269, 280, 289,
      1, 14, 21, 89, 98, 102, 106, 243, 267, 268, 277, 278, 283, 284, 285, 286, 288,
      20, 59, 92, 188, 190, 192, 204, 246, 263, 265, 279, 281, 282, 287, 290, 291, 292, 293, 294,309,304,313
  );
  $directory = md5(implode(',', $services));

  // Attach Javascript and Styles.
  $api = 'http://www.shareaholic.com/api/sprite/?v=2&apikey=' . SB_APIKEY . '&service=' . implode(',', $services);
  drupal_add_css(shareaholic_get_file($api, "{$directory}/sprite.css"));
  drupal_add_css('
    li.shareaholic {
      background-image: url("' . file_create_url(shareaholic_get_file("{$api}&apitype=png", "{$directory}/sprite.png")) . '");
      
    }

    div.shr-bookmarks ul.shr-socials li {
      cursor: move !important;
      font-size:9px !important;
      text-align:center;
    }
  ', 'inline');
  drupal_add_js(drupal_get_path('module', 'shareaholic') . '/scripts/shareaholic.admin.js');
  drupal_add_library('system', 'ui.sortable');

  // Active services list.
  $active = array();
  foreach (explode(',', $form_element['#default_value']) as $item) {
    $text_to_show =shrsb_truncate_text(end(explode('-', $services_list[$item])), 9);
    $active[] = array('data' => '<div style="margin-top:26px;"></div>'.$text_to_show, 'id' => "shr-{$item}", 'class' => array("shr-{$item}", 'shareaholic'));
  }
  $output .= '<div class="shr-bookmarks clearfix">' . theme('item_list', array('title' => t('Active services- Drag services to \'Inactive Services\' to add/remove services'), 'items' => $active, 'attributes' => array('id' => 'shareaholic-active', 'class' => array('shr-socials')))) . '</div>';

  // Inactive services list.
  $inactive = array();
  foreach (array_diff($services, explode(',', $form_element['#default_value'])) as $item) {
    $text_to_show =shrsb_truncate_text(end(explode('-', $services_list[$item])), 9);
    $inactive[] = array('data' => '<div style="margin-top:26px;"></div>'.$text_to_show, 'id' => "shr-{$item}", 'class' => array("shr-{$item}", 'shareaholic'));
  }
  $output .= '<div class="shr-bookmarks clearfix">' . theme('item_list', array('title' => t('Inactive services'), 'items' => $inactive, 'attributes' => array('id' => 'shareaholic-inactive', 'class' => array('shr-socials')))) . '</div>';

  // Hidden field.
  element_set_attributes($form_element, array('id', 'name', 'value', 'size', 'maxlength'));
  $output .= theme('hidden', array('element' => $form_element));

  return $output;
}
