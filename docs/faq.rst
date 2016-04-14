==========================
Frequently Asked Questions
==========================

Why does my script exit before the responses have been processed?
=================================================================

If you asynchronously send requests, it is possible your program finishes executing before all requests have been handled. 
In this case, the script exits and the connections are tossed. 
Perry provides a method to wait for all pending connections.
You can call :code:`Perry::execute();` to sychronously wait until all requests have been handled.
This includes requests that are made after :code:`Perry::execute()` has been called (i.e., in callbacks).
