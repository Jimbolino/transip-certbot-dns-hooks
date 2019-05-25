# transip-certbot-dns-hooks

Requirements
------------
* PHP with XML and SOAP extensions enabled

Installation
------------
* `git clone https://github.com/Jimbolino/transip-certbot-dns-hooks.git`
* `composer update`
* `cp .env.example .env`
* `vim .env`

Request a wildcard certificate
------------
* `./get-wildcard.sh domain.com`

Credits
------------
* Roy Bongers for the idea: https://github.com/roy-bongers/certbot-transip-dns-01-validator
* Other projects that i found were to complicated: https://github.com/hsmade/certbot-dns-transip 