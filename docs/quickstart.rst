==========
Quickstart
==========

This page provides a quick introduction to Perry. 
There are some examples to get you on your way.
For instructions on installing Perry, head to the :ref:`installation` page.

Chaining requests:
==================

.. code-block:: php
    
    $promise = Perry::fromUrl("https://public-crest.eveonline.com/");
    $promise->then(
        function($rootResponse) {
            $response->alliances()->then(
                function($alliances) {
                    // Process alliances
                }
            );
        }
    );

Batched request pools:
======================

.. code-block:: php
    
    $requestPoolGenerator = Perry::fromUrlsBatched(
        $urls, 
        function($response, $index) {
            // Process a response
        },
        function($reason, $index) {
            // Process CREST request failure
        },
        500 // 500 requests per pool
    );
    
    foreach($requestPoolGenerator AS $requestPool)
    {
        $promise = $requestPool->promise();
        $promise->wait();
    }
    