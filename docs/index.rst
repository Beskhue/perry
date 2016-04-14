.. title:: Perry, EVE CREST client library

===================
Perry Documentation
===================

Perry is an EVE CREST client library that makes it easy to send CREST requests and use CREST responses.

.. ATTENTION::
   Perry 3.0 is currently in development and is unstable.

- Simple interface for sending CREST requests.
- Requests are sent asynchronously, meaning your code can continue while you wait for a CREST response.
- Requests can be sent concurrently, this significantly speeds up CREST crawls when you need to access more than just a few resources.
- CREST requests can automatically be pooled for concurrency, and pools can automatically be batched for intermediate processing.

.. code-block:: php
    
    $promise = Perry::fromUrl("https://public-crest.eveonline.com/");
    $promise->then(
        function($response) {
            echo $response->serverName;
        },
        function($exception) {
            echo $exception->getMessage();
        }
    );

Guide
=====

.. toctree::
   :maxdepth: 2

   overview
   quickstart
   faq

