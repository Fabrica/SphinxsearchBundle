About SphinxsearchBundle
========================
Sphinx search bundle for Symfony 2


Installation:
============

##Bring in the vendor libraries


This can be done in two different ways:

**First Way** : Use Composer *(recommended)*

    // composer.json
    "require": {
        "php": ">=5.3.2",
        // ...
        "delocker/sphinxsearch-bundle": "dev-master",
        // ...
    }


**Second Way** : git command


    git submodule add git://github.com/delocker/SphinxsearchBundle.git vendor/delocker/sphinxsearch-bundle


**Enable the bundle** in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Delocker\SphinxsearchBundle\SphinxsearchBundle(),
    );
}
```


Configuration:
==============


    // app/config/config.yml
    sphinxsearch:
        indexes:
            indexName:
                index_name: %sphinxsearch_index_indexName%
            indexNameTwo:
                index_name: %sphinxsearch_index_indexNameTwo%
        searchd:
            host:   %sphinxsearch_host%
            port:   %sphinxsearch_port%
            socket: %sphinxsearch_socket%
        indexer:
            bin:    %sphinxsearch_indexer_bin%
            conf:   %sphinxsearch_indexer_conf%

    **For example**
    sphinxsearch:
        indexes:
            test1:
                index_name: test1
            testrt:
                index_name: testrt
        searchd:
            host:   localhost
            port:   9312
            socket: ~
        indexer:
            bin:    /usr/local/sphinx/bin/indexer
            conf:   /usr/local/sphinx/etc/sphinx.conf



At least one index must be defined, and you may define as many as you like.

In the above sample configuration, `indexName` is used as a label for the index named `%sphinxsearch_index_indexName%` 
as defined in your `sphinxsearch.conf`).  
This allows you to avoid having to hard code raw index names inside of your code.



Usage examples:
---------------

The most basic search, using the above configuration as an example, would be:

``` php
$indexesToSearch = array(
  'Items' => array(),
  'Categories' => array(),
);
$sphinxSearch = $this->get('search.sphinxsearch.search');
$searchResults = $sphinxSearch->search('search query', $indexesToSearch);
```

This performs a search for `search query` against the indexes labeled `Items` and `Categories`.  The results of the search would be stored in `$searchResults['Items']` and `$searchResults['Categories']`.

You can also perform more advanced searches, such as:

``` php
$indexesToSearch = array(
  'Items' => array(
    'result_offset' => 0,
    'result_limit' => 25,
    'field_weights' => array(
      'Name' => 2,
      'SKU' => 3,
    ),
  ),
  'Categories' => array(
    'result_offset' => 0,
    'result_limit' => 10,
  ),
);
$sphinxSearch = $this->get('search.sphinxsearch.search');
$sphinxSearch->setMatchMode(SPH_MATCH_EXTENDED2);
$sphinxSearch->setFilter('disabled', array(1), true);
$searchResults = $sphinxSearch->search('search query', $indexesToSearch);
```

This would again search `Items` and `Categories` for `search query`, but now `Items` will return up to the first 25 matches and weight the `Name` and `SKU` fields higher than normal, and `Categories` will return up to the first 10.  Note that in order to define a `result_offset` or a `result_limit`, you must explicitly define both values.  Also, this search will use [the Extended query syntax](http://sphinxsearch.com/docs/current.html#extended-syntax), and exclude all results with a `disabled` attribute set to 1.


```
Copyright (c) 2012, Ryan Rogers
All rights reserved.
```
