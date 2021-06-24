.. include:: ../Includes.txt


.. _usage:


Usage
=====

To enable canonical link for a file:

1. Edit the file's metadata, in TYPO3 backend

   .. figure:: ../Welcome/Images/ExtendedFileMetadata.png
     :alt: Extended sys_file_metadata TCA
     :width: 50%

2. Set a page or record, on which this file is located at

3. After saving metadata, the canonical link get parsed and stored to database


Now, when requesting the file in Frontend, will add additional "Link" HTTP header:

.. figure:: ../Welcome/Images/AdditionalHttpResponseHeaderLink.png
  :alt: Additional HTTP response header "link", shown in browser's dev tools
  :width: 50%


Auto Canonical Link
-------------------

When the this option is enabled, the file_canonical extension creates the canonical link automatically from
HTTP referer of file requests. You can see the generated link in info module and file meta data.
