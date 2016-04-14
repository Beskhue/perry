==========
Quickstart
==========

This page provides a quick introduction to Perry. 
There are some examples to get you on your way.
For instructions on installing Perry, head to the :ref:`installation` page.

Perry Returns Promises, Not Responses
=====================================
Because Perry is an asynchronous CREST library, its CREST request methods do not return CREST responses.
Instead, Perry returns promises for responses.
Promises provide two methods of getting the actual data you want, :code:`wait` and :code:`then`. 

Synchronous Wait
----------------
Using :code:`wait`, the code stops executing and waits until the response has been received (or until the request fails).
This is a lot like a simple sychronous request.
The :code:`wait` method returns the CREST response.
       
.. code-block:: php
    
    $promise = Perry::fromUrl("https://public-crest.eveonline.com/");
    try {
        $response = $promise->wait();
        echo $response->serverName;
    } catch(Exception $e) {
        echo "Something went wrong: " . $e->getMessage();
    }
    
    
Asynchronous Then
-----------------
Using :code:`then`, you register callbacks that should be called once the response has been received. 
In the meantime, your code can continue executing. 

.. code-block:: php
    
    $promise = Perry::fromUrl("https://public-crest.eveonline.com/");
    $promise->then(
        function($response) {
            echo $response->serverName;
        },
        function($exception) {
            echo "Something went wrong: " . $exception->getMessage();
        }
    );

Full Killmail Example:
======================

The below code shows a fully working usage example of Perry. The output should be (trimmed): 

    Cyleth of Finnish Space Jaegers lost a Nyx to Marek Payne of Reikoku, and Malakai Hiero of Reikoku, ..., and Degotoga of Hoover Inc.

.. code-block:: php

    <?php
        // URL for this example
        $url = "https://public-crest.eveonline.com/killmails/53285628/daba588a7b8b9b6879c9be8b24972143e23c6679/";
        
        require_once 'vendor/autoload.php';

        // Import the Perry class, alternatively you can use the fully qualified name in your code.
        use Perry\Perry;
        
        /** 
         * @var \GuzzleHttp\Promise\PromisorInterface A promise that resolves into an object of type
         *                                            \Perry\Representation\Eve\v1\Killmail 
         */
        $killmailPromise = Perry::fromUrl($url);

        // We have now created the request. Perry will asynchronously query CREST. Thus, at this point 
        // the data will not be ready yet. As such, instead of receiving a killmail, we are given a
        // promise for the killmail. Once Perry has received a response, the promise is fulfilled.
        // Instead of using the killmail here, we register functions that should be executed once a
        // promise has been fulfilled. We can also register a function that should be executed on 
        // failure.
        $killmailPromise->then(
            function($killmail) {
                // This function is called when a promise has been fulfilled successfully.
                if (isset($killmail->victim->character)) {
                    $killstring = sprintf(
                        '%s of %s lost a %s to ',
                        $killmail->victim->character->name,     // The victim is a player character so 
                                                                // we can get its name...
                        $killmail->victim->corporation->name,   // ... and the name of the corporation it 
                                                                // is a member of...
                        $killmail->victim->shipType->name       // ... and the ship that was lost.
                    );
                } else {
                    $killstring = sprintf(
                        '%s lost by %s to ',
                        $killmail->victim->shipType->name,
                        $killmail->victim->corporation->name
                    );
                }
                
                // $killmail->attackers is an array of KillmailAttacker objects.
                $attackers = array();
                foreach ($killmail->attackers as $attacker) {
                    // Again the attacker might not be a player character
                    if (isset($attacker->character)) {
                        $attackers[] = sprintf(
                            '%s of %s',
                            $attacker->character->name,
                            $attacker->corporation->name
                        );
                    } else {
                        $attackers[] = $attacker->corporation->name;
                    }
                    
                }

                $killstring .= join(', and ', $attackers);

                echo $killstring;
            },
            function($exception) {
                // This function is called when a promise has failed.
                echo "Could not get the killmail! Reason: " . $exception->getMessage();
            }
        );
        
        // Without waiting for the request we can continue with other processing here. 
        
        // At this point we want to exit the script, but there might still be outstanding
        // connections that we want to handle. If the script exits now, those connections
        // will be tossed. To wait for outstanding connections, we call Perry::execute().
        Perry::execute();
       
Chaining Requests
=================

References in responses to other CREST resources can easily be traversed. 
The references are invokable and return a promise for that resource.
This makes chaining of requests a breeze.

.. code-block:: php
    
    $promise = Perry::fromUrl("https://public-crest.eveonline.com/");
    $promise->then(
        function($root) {
            $root->regions()->then(
                function($regions) {
                    printf("There are %d regions.", count($regions->items));
                }
            );
        }
    );

Request Pools
=============
    
Sometimes you want to process many requests.
Perry can asynchronously process an array of requests while keeping the number of concurrent connections below the CREST limit.
Two means of processing these requests are provided.

Single Pool
-----------

You can put all requests together into a single request pool.
Perry will process these requests and feed the data to the callbacks.

.. code-block:: php
    
    $requestPool = Perry::fromUrls(
        $urls, 
        function($response, $index) {
            // Process a response
        },
        function($reason, $index) {
            // Process CREST request failure
        }
    );
    
    $promise = $requestPool->promise();
    
    // While the pool is processed, the code can continue doing other things.
    // By using $promise->then(..) we can be notified when the pool is finished,
    // or we can wait for the pool to be finished:
    $promise->wait();
    
Batched Request Pools
----------------------

If you have a large number of requests, it might be desirable to perform intermediate processing on groups of requests. 
This means that we cannot pool all requests together.
Perry provides the means for creating pool batches.

.. code-block:: php
    
    $requestPoolGenerator = Perry::fromUrlsBatched(
        $urls, 
        function($response, $index) {
            // Process a response
        },
        function($reason, $index) {
            // Process CREST request failure
        },
        500 // Number of requests per pool
    );
    
    // $requestPoolGenerator is a generator of requestPools,
    // so we can iterate over it to get each pool.
    foreach($requestPoolGenerator AS $requestPool)
    {
        $promise = $requestPool->promise();
        $promise->wait();
    }
    
