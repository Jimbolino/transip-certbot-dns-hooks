# transip-certbot-dns-hooks

Requirements
------------
* PHP with XML and SOAP extensions enabled

Installation
------------
* git clone
* cp .env.example .env
* composer update

Request a wildcard certificate
------------
* ./get-wildcard.sh domain.com

Credits
------------
* Roy Bongers for the idea: https://github.com/roy-bongers/certbot-transip-dns-01-validator
* Other projects that i found were to complicated: https://github.com/hsmade/certbot-dns-transip 