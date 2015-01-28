Domain Blocker
===

Addon allows you to block certain domains in case offensive/objectionable words are used in the 
domain name.


### Requirements

* WHMCS 5.3.x
* PHP 5.3.x

### Installation

1. Unzip the archieve in modules/addons/
2. Place hook_domain_blocker.php into includes/hooks/ to activate the blocker script.


### Localization

Since WHMCS doesn't allow you properly handle error messages in case of blocking domain request, you'd have to override following variable with something like this:

```php
$_LANG['cartdomaininvalid'] = "Domain is either invalid or blocked due to 'offensive' words used in it";
```

and store it into ```lang/overrides/english.php``` or whatever language you're using.
