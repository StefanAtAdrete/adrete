# Media Remote

## Overview

This module offers a simple mechanism to handle remote URL content that is
not an OEmbed implementation as Media items in Drupal.

Note: If the content you intend to use comes from a provider that implements
the OEmbed specification (i.e. it's listed in
https://oembed.com/providers.json), you don't need this module, and you should
use core Media instead. This can be done either by using a contrib module that
exposes the source plugin you need, or by writting a little bit of custom
code. More information can be found on https://www.drupal.org/node/2966029 .

Providers that don't follow OEmbed usually have a particular way of
transforming a URL value into an embed code snippet. This module offers:
  * The scaffolded code necessary to interact with the Media system in Drupal,
    including Media source plugin, validation, integration with Media Library,
    etc.
  * Formatters for the supported providers. The formatter is responsible for
    converting the remote URL into an embed/snippet code.

Supported providers / Formatters included:

- Apple Podcasts
- Box
- Brightcove
- Buzzsprout
- Dacast
- Deezer
- DocumentCloud
- Dropbox
- Google Drive docs (Documents, Spreadsheets and Slides)
- Libsyn
- Loom
- Microsoft Forms
- NPR videos
- Quickbase
- Stitcher
- ...

Want to suggest a new provider? Open an issue in the
[issue queue](https://www.drupal.org/project/issues/media_remote), patches
are always welcome.

## Configuration & Use

1- Install this module as usual.

2- Create a new Media Type, select "Remote Media URL" as Media source

3- Navigate to the "Manage Display" page of the media type you just created,
and select the formatter you want to use for the URL field.

4- Enjoy!


