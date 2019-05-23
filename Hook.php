<?php

use TransIP\Client;
use TransIP\Model\DnsEntry;

class Hook
{
    private $client;

    public function __construct()
    {
        if (!isset($_SERVER['CERTBOT_DOMAIN'], $_SERVER['CERTBOT_VALIDATION'])) {
            //die('Missing required environment variables: CERTBOT_DOMAIN, CERTBOT_VALIDATION');
        }
        Dotenv\Dotenv::create(__DIR__)->load();

        $this->client = new Client(getenv('TRANSIP_USERNAME'), getenv('TRANSIP_PRIVATE_KEY'));
    }

    public function challenge($domain, $challenge)
    {
        $this->checkAccess($domain);
        $challengeKey = $this->getChallengeKey($domain);

        $records = $this->getRecords($domain);
        $records[] = new DnsEntry($challengeKey, 60, DnsEntry::TYPE_TXT, $challenge);

        $this->client->domain()->setDnsEntries($domain, $records);

        $this->sleepUntilRecordFound($domain, $challenge);
    }

    public function cleanup($domain, $challenge)
    {
        $this->checkAccess($domain);
        $challenge_key = $this->getChallengeKey($domain);

        $records = $this->getRecords($domain);
        foreach ($records as $key => $record) {
            if ($record->name === $challenge_key && $record->content === $challenge) {
                echo 'unsetting: '.print_r($record, true).PHP_EOL;
                unset($records[$key]);
            }
        }
        $this->client->domain()->setDnsEntries($domain, $records);
    }

    private function sleepUntilRecordFound($domain, $challenge, $counter = 1)
    {
        $nameserver = trim(shell_exec('dig @1.1.1.1 '.escapeshellarg($domain).' NS +short | head -n1'));
        if (empty($nameserver)) {
            die('nameserver not found:'.$nameserver);
        }
        $challengeKey = $this->getChallengeKey($domain);

        $record = trim(shell_exec('dig '.$challengeKey.'.'.$domain.' @'.$nameserver.' TXT +short'), PHP_EOL.'"');
        if ($record === $challenge) {
            return true;
        }
        echo 'sleeping for 5 sec, retry: '.$counter.PHP_EOL;
        sleep(5);
        return $this->sleepUntilRecordFound($domain, $challenge, ++$counter);
    }

    private function checkAccess($domain)
    {
        $domainNames = $this->client->domain()->getDomainNames();
        if (!in_array($domain, $domainNames)) {
            die('domain not found in transip');
        }
    }

    private function getRecords($domain)
    {
        $dnsEntries = $this->client->api('domain')->getInfo($domain)->dnsEntries;
        if (empty($dnsEntries)) {
            die('dns entries was empty');
        }
        return $dnsEntries;
    }

    private function getChallengeKey($domain)
    {
        return '_acme-challenge'; // TODO add subdomain support
    }
}