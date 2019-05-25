<?php

use TransIP\Client;
use TransIP\Model\DnsEntry;
use Dotenv\Dotenv;

class Hook
{
    private $client;

    const INITIAL_SLEEP = 30;
    const LOOP_SLEEP = 10;

    public function __construct()
    {
        Dotenv::create(__DIR__)->load();

        $this->client = new Client(getenv('TRANSIP_USERNAME'), getenv('TRANSIP_PRIVATE_KEY'));
    }

    public function challenge($domain, $challenge)
    {
        list($domain, $subDomain) = $this->checkAccess($domain);
        $challengeKey = $this->getChallengeKey($subDomain);

        $records = $this->getRecords($domain);
        $records[] = new DnsEntry($challengeKey, 60, DnsEntry::TYPE_TXT, $challenge);

        $this->client->domain()->setDnsEntries($domain, $records);

        sleep(self::INITIAL_SLEEP);
        $this->sleepUntilRecordFound($domain, $subDomain, $challenge);
    }

    public function cleanup($domain, $challenge)
    {
        list($domain, $subDomain) = $this->checkAccess($domain);
        $challengeKey = $this->getChallengeKey($subDomain);

        $records = $this->getRecords($domain);
        foreach ($records as $key => $record) {
            if ($record->name === $challengeKey && $record->content === $challenge) {
                echo 'unsetting: '.print_r($record, true).PHP_EOL;
                unset($records[$key]);
            }
        }
        $records = array_values($records);
        $this->client->domain()->setDnsEntries($domain, $records);
    }

    private function sleepUntilRecordFound($domain, $subDomain, $challenge, $counter = 1)
    {
        # https://serverfault.com/a/891792/306855
        $nameserver = trim(shell_exec('dig @1.1.1.1 '.escapeshellarg($domain).' NS +short | head -n1'));
        if (empty($nameserver)) {
            die('nameserver not found:'.$nameserver);
        }
        $challengeKey = $this->getChallengeKey($subDomain);

        $records = explode(PHP_EOL, shell_exec('dig '.$challengeKey.'.'.$domain.' @'.$nameserver.' TXT +short'));

        foreach ($records as $record) {
            if ($challenge === trim($record, '"')) {
                return true;
            }
        }
        echo 'sleeping for '.self::LOOP_SLEEP.' sec, retry: '.$counter.PHP_EOL;
        sleep(self::LOOP_SLEEP);
        return $this->sleepUntilRecordFound($domain, $subDomain, $challenge, ++$counter);
    }

    private function checkAccess($domain)
    {
        $domainParts = explode('.', $domain);
        $subDomainParts = [];
        $domainNames = $this->client->domain()->getDomainNames();
        foreach ($domainParts as $index => $part) {
            $try = implode('.', $domainParts);
            if (in_array($try, $domainNames)) {
                return [implode('.', $domainParts), implode('.', $subDomainParts)];
            }
            $subDomainParts[] = array_shift($domainParts);
        }
        die('domain not found in transip');
    }

    private function getRecords($domain)
    {
        $dnsEntries = $this->client->api('domain')->getInfo($domain)->dnsEntries;
        if (empty($dnsEntries)) {
            die('dns entries was empty');
        }
        return $dnsEntries;
    }

    private function getChallengeKey($subDomain = '')
    {
        if ($subDomain !== '') {
            $subDomain = '.'.$subDomain;
        }
        return '_acme-challenge'.$subDomain;
    }
}
