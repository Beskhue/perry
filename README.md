# UNSTABLE
This library has recently been deprecated by the previous maintainer. The intention with this fork is to bring the library up to date.

# Perry
A PHP Library for accessing EVE Online's CREST API.

## WARNING
this is a prototype / work in progress.
As CCP has not released much of the CREST API yet its use is extremely limited,
also this library is not to be considered complete or stable, most likely
backward compatibility will break during further development.
Do not use this if you don't know what you are doing.

## Documentation and Help
- [Documentation](https://perry.readthedocs.org/en/3.0.0-dev/).
- Forum thread for help to come at the first release of Perry version 3.

## Status on the Completeness:
Implemented:
- https://forums.dust514.com/default.aspx?g=posts&t=103783 (districts have alot of references which dont resolve yet, also for some of those references i made guessing on what exactly they might refer to, so even when those are published there might be some extra work needed)
- https://forums.eveonline.com/default.aspx?g=posts&t=257854
- https://forums.eveonline.com/default.aspx?g=posts&m=3393341#post3393341 (Realtime Tournament Stuff)
- Killmail API
- https://forums.eveonline.com/default.aspx?g=posts&m=4303155 (Alliances, Incursions)
- https://neweden-dev.com/CREST_Market_History (Market History)

Removed:
- https://wiki.eveonline.com/en/wiki/CREST_Documentation  (while those are documented for ages, this endpoint has never been published. There is even a chance it has changed within Crest. Classes for it will come back once it is public.

Also you might find some files to access Thora, a Proxy for the old API,
which is mostly not working yet, so don't use it.

Also have a look at the bottom of this README, it contains a list of all known issues.

## USAGE
### Cache
Perry comes with build in caching of the requested CREST Pages. Perry is compliant with PSR-6 (at the currents draft
state). Since PSR-6 has not been finalized / released yet, at the moment it also contains the Interfaces PSR-6 is
defining. Once PSR-6 is available, those will be removed from the Lib.

By default Perry has the Cache DISABLED, meaning if you want any sort of caching, you have to enable it.
To enable Caching in Perry you simply give the Setup Singleton an instance of a PSR-6 Compliant Cache.

With Perry you get an extremly simple file cache, which takes a path in the constructor, that it then will
fill with cache files. If you use it keep in mind that those are not deleted automatically.

The TTL for the cache is by default 5 minutes, see the example below for how to change it.

```php
<?php
// require composers autoload.php
require_once 'vendor/autoload.php';

// import the Setup class, alternatively you can always use the full qualified name)
use Perry\Setup;

// get the Instance of Setup and hand an instance of the PoolInterface implementation
// of the file cache
Setup::getInstance()->cacheImplementation = new FilePool("/path/to/cache/folder");

// change ttl to 10 minutes
Setup::$cacheTTL = 600;


```

### Examples
here are a few examples, based on composer having been used to install perry

#### Killmail
```php
<?php
// lets set an url here for this example
$url = "http://public-crest.eveonline.com/killmails/34940735/32a1ed47430a4bf247d0544b399014067a734994/";

// require composers autoload.php
require_once 'vendor/autoload.php';

// import the Perry class, alternatively you can always use the full qualified name)
use Perry\Perry;


// since we have a use import on Perry\Perry, we can just use the classname here, otherwise
// it would be $killmail = \Perry\Perry::fromUrl($url);
/** @var \Perry\Representation\Eve\v1\Killmail */
$killmail = Perry::fromUrl($url);

// now there should be either an exception throw (in RL you want to catch those) or
// $killmail will contain a killmail. You can now access the values of the document
// quite easy.

// check if the victim has a character (not the cases for poses for example)
if (isset($killmail->victim->character)) {
    $killstring = sprintf(
        '%s of %s lost a %s to ',
        $killmail->victim->character->name,     // since we do have a character we can use its name
        $killmail->victim->corporation->name,   // victims allways have a corporation
        $killmail->victim->shipType->name       // the shiptype is what was actually lost
    );
} else {
    $killstring = sprintf(
        '%s lost by %s to ',
        $killmail->victim->shipType->name,
        $killmail->victim->corporation->name
    );
}

// attackers is a list of KillmailAttacker Objects.
$attackers = array();
foreach ($killmail->attackers as $attacker) {
    // like the victim there might not be a character with the attacker (sentry guns?)
    $attackers[] = isset($attacker->character) ? $attacker->character->name : $attacker->corporation->name;
}

$killstring .= join(',', $attackers);

echo $killstring;


// for more examples on what data is available in killmails, look at a killmail json string. If in doubt, there are some
// in tests/mock/kill*.json
// the references (character for example) which would be called like $killmail->victim->character(), do not work,
// since CCP has not opened those endpoints yet. :(
// except: the alliance endpoint work (at the moment on SISI only, but they will go live soon)
```


#### District
```php
<?php
// declare namespace of your script (optional, but recommended)
namespace MyScript;

// lets set an url here for this example
$url = "http://public-crest.eveonline.com/districts/";

// require composers autoload.php
require_once 'vendor/autoload.php';

// import the Perry class, alternatively you can always use the full qualified name
use Perry\Perry;

// we get the DistrictCollection Object, which will cause Perry to make a request to CCP's CREST API, and
// populate the DistrictCollection (and the Districts it holds)
/** @var \Perry\Representation\Eve\v1\DistrictCollection */
$districtCollection = Perry::fromUrl($url);


// districtCollection has a member called "items" which contains a list of districts
// we iterate over those items, and print a short info for every district.
// owner,system and infrastructure are references, which means they refer to further
// api representations, sadly, at the moment they refer to parts of the CREST API that
// CCP has not made public yet.
// If those reprenstations where public you could access them by doing $district->owner(), which would return a
// Corporation Representation. Again, this this is not working yet, hence we only use the name of the
// reference in this example.

foreach ($districtCollection->items as $district) {
    printf(
        "District: %s\n Owner: %s\n System: %s\n Clone Capacity: %s\n cloneRate: %s\n Infrastructure: %s\n\n",
        $district->name,
        $district->owner->name,
        $district->system->name,
        $district->cloneCapacity,
        $district->cloneRate,
        $district->infrastructure->name
    );
}
```

## Known Issues
There are a hand full of known issues. If you want to help with fixing them, pull requests are welcome.

- From Version 1.0.0 on the original conveniance methods like ```\Perry\Representation\Eve\v1\DistrictCollection::getInstance();``` do not work anymore, this is on purpose
- CREST dictionaries feature keys like "32x32", PHP will do a parse error on $object->32x32. You can either access those members by $object->{'32x32'}; or by using them as an array instead $object['32x32']. The later should be the preffered variant.
- A lot of endpoints that are referenced to within CREST are not public available. There is nothing that can be done about that except if CCP opens those.
- The Indentation within the representation classes is messed up. That's due to the classes being generated, and might get fixed in a future release
- Perry currently does not support write access to any endpoint (POST), which should not be a problem since CCP has not published a writable interface for public usage yet.
- The cache that comes with Perry is extremely rudimentary.
- The unit tests are not complete.
- Perry comes with classes in the Psr namespace, this is because Perry is implementing a Psr that is not in effect yet.
