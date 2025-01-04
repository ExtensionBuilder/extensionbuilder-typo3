<?php

declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Tools;

class Uri
{

    // https://de.wikipedia.org/wiki/Uniform_Resource_Identifier

    protected string $scheme = '';
    protected string $authority = '';
    protected string $host = '';
    protected int $port = 0;
    protected string $path = '';
    protected string $query = '';
    protected string $fragment = '';

    protected string $hostIpV4 = '';
    protected string $hostIpV6 = '';

    protected bool $hasIp = false;
    protected bool $hasIpV4 = false;
    protected bool $hasIpV6 = false;

    public bool $isOnline = false;

    protected array $icmpPing = [];
    protected bool $socketOpen = false;

    protected int $count = 1;
    protected int $timeout = 5;

    public function __construct(
        protected readonly string $uri,
    ){
        // Get scheme
        if ($positionA = stripos($uri, ':')) {
            $this->scheme = substr($uri, 0, $positionA);
        }

        if ($startOfAuthority = strpos($uri, '//')) {
            // Scheme with Authority
            $authority = substr($uri, $startOfAuthority + 2);
            if (!($endOfAuthority = strpos($authority, '/'))) {
                if (!($endOfAuthority = strpos($authority, '?'))) {
                    if (!($endOfAuthority = strpos($authority, '#'))) {
                        $endOfAuthority = strlen($authority);
                    }
                }	
            }

            $authority = substr($authority, 0, $endOfAuthority);
            $this->authority = $authority;

            // Set port
            $positionA = strripos($authority, ':');
            $positionB = strripos($authority, ']');
            if ($positionA > $positionB) {
                $this->port = (int)substr($authority, $positionA + 1);
                $authority = substr($authority, 0, $positionA);
            } else {
                // determine port using the scheme
                switch ($this->scheme) {
                    case 'http':
                        $this->port = 80;
                        break;
                    case 'https':
                        $this->port = 443;
                        break;
                }
			}

            // Set host, clean IPv6 []
            if (strpos($authority, ']')) {
                $authority = substr($authority, 1);
                $authority = substr($authority, 0, strlen($authority) - 1);
            }
            $this->host = $authority;

            
            if (filter_var($authority, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) {
                $this->hostIpV4 = $authority;
            } elseif (filter_var($authority, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) {
                $this->hostIpV6 = $authority;
            } else {

                // https://www.php.net/manual/de/function.dns-get-record.php

                $dnsGetA = dns_get_record($this->host, DNS_A);
                $dnsGetAAAA = dns_get_record($this->host, DNS_AAAA);
                $this->hostIpV4 = $dnsGetA[0]['ip'] ?? '';
                $this->hostIpV6 = $dnsGetAAAA[0]['ipv6']?? '';
             }

             if ($this->hostIpV4){
                 $this->hasIp = true;
                 $this->hasIpV4 = true;
		     }

             if ($this->hostIpV6){
                 $this->hasIp = true;
                 $this->hasIpV6 = true;
     		 }

            $pqf = ' ' . substr($uri,$startOfAuthority+2+$endOfAuthority);
            $startOfPath = strpos($pqf, '/');
            $startOfQuery = strpos($pqf, '?');
            $startOfFragment = strpos($pqf, '#');
            if ($startOfFragment) { 
                $this->fragment = substr($pqf, $startOfFragment + 1);
                $pqf = substr($pqf, 0 ,$startOfFragment);
            }
            if ($startOfQuery) { 
                $this->query = substr($pqf, $startOfQuery + 1);
                $pqf = substr($pqf, 0 ,$startOfQuery);
            }
            if ($startOfPath) { 
                $this->path = substr($pqf, $startOfPath + 1);
                $pqf = substr($pqf, 0 ,$startOfPath);
            }
			
     		$this->isOnline = $this->icmpPing();

            $this->isOnline = $this->socketOpen();

		} else {
// ToDo Scheme without Authority
		}
	}

    public function icmpPing(): bool
    {
        $pingResult = [];
        $pingResult['online'] = false;

		exec(sprintf('ping -c ' . $this->count . ' -W ' . $this->timeout . ' %s', escapeshellarg($this->host)), $result, $resultStatus);
        $count = count($result);

        $explode = explode(', ',$result[$count-2]);
        $pingResult['transmitted'] = (int)substr($explode[0], 0, strpos($explode[0], ' packets transmitted'));
        $pingResult['received'] = (int)substr($explode[1], 0, strpos($explode[1], ' received'));
        $pingResult['loss'] = (int)substr($explode[2], 0, strpos($explode[2], '% packet loss'));

        if ($resultStatus === 0) {
            $pingResult['online'] = true;

			$time = substr($result[$count-1], strlen('rtt min/avg/max/mdev = '));
			$time = substr($time, 0, strpos($time,' ms'));
            $explode = explode('/', $time);

			$pingResult['min'] = (float)$explode[0];
			$pingResult['avg'] = (float)$explode[1];
			$pingResult['max'] = (float)$explode[2];
			$pingResult['mdev'] = (float)$explode[3];
		}

		$this->icmpPing = $pingResult;
		
        return $pingResult['online'];
	}

    public function socketOpen(): bool {
        $socketResult = [];

        $this->socketOpen = false;
        if ($this->port) {
            $fsock = fsockopen($this->host, $this->port);

            $this->socketOpen = true;

            fclose($fsock);
		}

        return $this->socketOpen;
	}

}