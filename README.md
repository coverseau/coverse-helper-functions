# COVERSE helper functions

Some helper functions and shortcodes for use on the **CO**VERSE website. Requires [Formidable Forms](https://formidableforms.com/).

## Installation

To install this plugin, [download the latest release](https://github.com/coverseau/coverse-helper-functions/releases/latest/download/coverse-helper-functions.zip) and upload it to your Wordpress website ([instructions for uploading](https://wordpress.org/documentation/article/manage-plugins/#upload-via-wordpress-admin)).

## Usage

As a starting point, usage of Formidable Forms statistics functions can be found online at [formidableforms.com/knowledgebase/add-field-totals-and-statistics](https://formidableforms.com/knowledgebase/add-field-totals-and-statistics/).

This plugin provides additional functions:
* `fields-stats`: Combined total for two or more fields. For example, `[fields-stats ids="x,y,z"]`. Replace `x`, `y`, and `z` with the IDs of the fields you want to total.
* `subtract-fields`: Subtract one simple stat from another. The stats need to be the same type (e.g. total or count) and not have any filters or use any other parameters that would be specific to one stat but not the other. Usage: `[subtract-fields total=x removed=y type=total]`. Replace `x` with the id of the field that holds the initial total and `y` with the id of the field that holds the amount being subtracted from the total.
* `frm-percent`: Percentage of a specific field value. Usage: `[frm-percent id=x value="Option 1" without="Option 2"]`
* `frm-percent-total`: Percentage of a specific field value. Usage: `[frm-percent-total id=x value="Option 1"]`
* `frm-percent-not-total`: Percentage not of a specific field value. Usage: `[frm-percent-not-total id=x value="Option 1"]`
* `frm-age-from-years`: Subtract one simple stat from the current year to calculate _age_, and rounds to nearest integer. Usage: `[frm-age-from-years year=YYYY id=x type=average]`. Replace `YYYY` with the initial year value (or don’t include it at all, and the current year will be used) and `x` with the id of the field that holds the amount being subtracted from the year.
