.. include:: ../Includes.txt


.. _installation:


Installation
============

You can install the File Canonical extension for TYPO3 CMS with or without Composer.
It's recommended to use composer!


With Composer
-------------

Just perform the following command on CLI:

::

    $ composer req t3/file-canonical

When composer is done, you need to enable the extension in the extension manager.


Without Composer
----------------

You can also fetch file_canonical from the TER and install it the old-fashioned way.


After installation (!)
----------------------

The File Canonical extension can only work, when HTTP requests to files are routed through TYPO3. To achieve this
you can simply adjust the root **.htaccess** file, right after the ``TYPO3_CONTEXT`` has been set
(before other rewrite conditions take effect):

::

    # Rule for EXT:file_canonical
    RewriteCond %{REQUEST_URI} ^/fileadmin
    RewriteCond %{REQUEST_FILENAME} \.(pdf|docx|xlsx|pptx)$
    RewriteRule ^.*$ %{ENV:CWD}index.php [QSA,L]


Now, all requests which point to /fileadmin and request a PDF or common office file will be processed by the
FileCanonicalMiddleware provided by this extension.

You can adjust this configuration to your needs.
