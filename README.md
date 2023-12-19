# Shareaholic for Drupal Module

With the official Shareaholic for Drupal module, Drupal users now have access to the full suite of Shareaholic tools. Drupal site owners and administrators no longer need to fiddle around with HTML or JavaScript to implement Shareaholic. We’ve done all of the hard work for you - it should take less than 5 minutes to install and setup.

Note: This README is for the project while [README.txt](https://github.com/shareaholic/shareaholic_for_drupal/blob/master/README.txt) is the one that is packaged with the module.


## Drupal Project Home

https://drupal.org/project/shareaholic


## Contributing

1. Fork the [official repository](https://github.com/shareaholic/shareaholic_for_drupal/tree/master).
2. Make your changes in a topic branch.
3. Send a pull request.


### Getting Started

This project assumes you have:

* Drupal 8.x
* PHP version 5.6 or higher

Please follow the installation instructions for Drupal 8.x at the [Drupal site](https://drupal.org/documentation/install) to get started

Once you have the server setup with Drupal, clone this repository to your local workspace:

    git clone https://github.com/shareaholic/shareaholic_for_drupal.git
    git checkout master

All modules in a Drupal site are located in:

    /path/to/drupal/sites/all/modules

If you have this repository cloned into your local workspace, you can create a symbolic link for this repository to the Drupal modules location:

    ln -s /path/to/your/local/workspace/shareaholic_for_drupal/ /path/to/drupal/sites/all/modules/shareaholic_for_drupal

If you do not prefer this method, you can move this repository over to the Drupal modules path or use some other method. Otherwise, Drupal will not recognize the module.

Then go to your Drupal site signed in as admin, go to Modules and enable the Shareaholic for Drupal module. You should not receive errors and be all set to use/develop the module

## Credits

![Shareaholic](https://blog.shareaholic.com/wp-content/uploads/2013/10/new-shareaholic-logo.png)

shareaholic_for_drupal is owned and maintained by [Shareaholic, Inc](https://shareaholic.com/). The names and logos for Shareaholic are trademarks of Shareaholic, Inc.

Thank you to all [the contributors](https://github.com/shareaholic/shareaholic_for_drupal/contributors)!

This project is tested with BrowserStack.

## License

shareaholic_for_drupal is Copyright © Shareaholic Inc. It is free software, and may be redistributed under [GPL](https://github.com/shareaholic/shareaholic_for_drupal/blob/master/LICENSE)
