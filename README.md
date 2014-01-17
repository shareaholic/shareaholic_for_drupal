# Shareaholic for Drupal

This repository is for Shareaholic's Drupal module

This README is for internal/development purposes while the other README.txt is for release/public purposes

## Getting Started

At the time of writing this (2013-01-17) the repo assumes you have:
* PHP version 5.3.x or higher
* Drupal 7.x


Please follow the installation instructions for Drupal 7.x at the [drupal site](https://drupal.org/documentation/install) to get started

Once you have the server setup with drupal, clone this repository to your local workspace:

    git clone https://github.com/shareaholic/shareaholic_for_drupal.git

At the time of writing this (2013-01-17) the new drupal code is in a branch called master-new. Switch over to that branch:

    git checkout master-new

All modules in a Drupal site are located in:

    /path/to/drupal/sites/all/modules

If you have this repository cloned into your local workspace, you can create a symbolic link for this repository to the drupal modules location:

    ln -s /path/to/your/local/workspace/shareaholic_for_drupal/ /path/to/drupal/sites/all/modules/shareaholic_for_drupal

If you do not prefer this method, you can move this repository over to the drupal modules path or some other method. Otherwise, Drupal will not recognize the module.

Then go to your Drupal site signed in as admin, go to Modules and enable the Shareaholic for Drupal plugin. You should not receive errors and be all set to use/develop the plugin

## Environment Variables

TODO: figure out how to configure between spreadaholic, stageaholic, and shareaholic

## Testing

There are a couple of testing frameworks that can be used with Drupal module development but for now we will simply use the built in testing module provided by Drupal core.

You would simply need to enable the module by going to "Modules" and looking for "Testing" module under Drupal core.
If you get a memory limit error, increase your memory limit in your php.ini file and restart the server

To run the tests, enable the Shareaholic for Drupal module,  go to Configuration->Development->Testing and run some or all the tests for Shareaholic for Drupal

## Deploy

TODO: define rake task that will remove dev files and package the rest for release

## Documentation

TODO: link to wiki





