**Rapid Engine**

PHP Micro Framework being used for a small social network project.  

Goals are:

 - As unobtrusive as possible, stay out of the developer's way and don't dictate program flow.  
 - As little 'boiler plate' code as possible inside the application.
 - All code, no XML or command line tools.  
 
 
 
 ### Routing
 
 ```php
 //implemented
 $RapidEngine->DefineAction( '/path', 'SomeClass');
 $RapidEngine->DefineAction( '/path', 'SomeClass->SomeMethod');
 
 $RapidEngine->DefineAction( '@404', 'SomeClass->NotFoundMethod');  //404 handler
 
 
 
 
 //to be implemented
 $RapidEngine->DefineAction( '/path', 'SomeFunction()');
 $RapidEngine->DefineAction( '/path', function() { ...some code... });
  
 ```
