The SexyBookmarks for Drupal module is a port of the WordPress plug-in by the
same name.

The sole aim was to stray away from the "in the box" thinking behind most social
bookmarking plugins, and add a little flair that would entice your readers,
rather than deterring them with microscopic icons that get lost in pages heavy
laden with content.

SexyBookmarks for Drupal was ported and is maintained by Stuart Clark
(deciphered).
- http://stuar.tc/lark

SexyBookmarks is owned and operated by Shareaholic.
- http://sexybookmarks.shareaholic.com/


Features
--------------------------------------------------------------------------------

* Support for more than 80 social bookmarking services.
* Multiple configuration profiles - Features exportable.
* Multiple display options:
  * Node item/Psuedo field for Nodes.
  * Field/Views formatters:
    * Link module.
    * Node Reference module.
  * Views module content field.
  * Blocks:
    * Current URL.
    * Site URL.
    * plus an example Boxes block for extended support.
  * Drupal API theme() function.
* Support for:
  * Page Title module.
  * Shorten URLs module.


Required Modules
--------------------------------------------------------------------------------

* Chaos Tool suite - http://drupal.org/project/ctools


Recommended Modules
--------------------------------------------------------------------------------

* Shorten URLs - http://drupal.org/project/shorten


Configuration
--------------------------------------------------------------------------------

* Setting up profiles:

  Profiles can be managed at:
    Administration > Configuration > Content authoring > SexyBookmarks
    http://[www.yoursite.com/path/to/drupal]/admin/config/content/sexybookmarks


* Displaying via Nodes (pseudo field):

  The profile for each Content type can be set at:
    Administration > Structure > Content types > [content type] > Edit
    http://[www.yoursite.com/path/to/drupal]/admin/structure/types/manage/[content-type]

  The position of the SexyBookmarks widget can be set at:
    Administration > Structure > Content types > [content type] > Manage display > [view mode]
    http://[www.yoursite.com/path/to/drupal]/admin/structure/types/manage/[content-type]/display/[view-mode]


Help
--------------------------------------------------------------------------------

More help can be found via the Help module:
  Administration > Help > sexybookmarks
  http://[www.yoursite.com/path/to/drupal]/admin/help/sexybookmarks
