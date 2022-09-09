.. include:: ../Includes.txt


.. _welcome:


Welcome
=======

The File Canonical extension for TYPO3 provides canonical links (via HTTP headers) for files (e.g. in ``fileadmin/``).
This allows you to define a page, related to the requested file, which might be imported for SEO.


Features
--------

- Extending meta data of files in backend to allow editors to set canonical link for any file, manually
- Canonical link for files can get generated automatically by HTTP referer (configurable)
- Overview of files with canonical link set, as new info module
- Managing canonical link HTTP header when output file (.htaccess adjustments required)


Screenshots
-----------

Screenshot 1: Extended sys_file_metadata TCA
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. figure:: Images/ExtendedFileMetadata.png
  :alt: Extended sys_file_metadata TCA
  :width: 85%


Screenshot 2: Additional HTTP response header "link"
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. figure:: Images/AdditionalHttpResponseHeaderLink.png
  :alt: Additional HTTP response header "link", shown in browser's dev tools
  :width: 50%


Screenshot 3: Info module overview
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. figure:: Images/InfoModule.png
  :alt: Info module overview
  :width: 75%
